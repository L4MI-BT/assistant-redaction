<?php

class ASSREDAC_Ollama_Client {

    /**
	 * Envoie une conversation à Ollama et renvoie sa réponse décodée.
	 *
	 * @param array $messages Les messages (system, user).
	 * @param array $format   Le schéma JSON attendu en sortie.
	 * @return array|WP_Error La réponse d'Ollama, ou une erreur.
	 */
    public function chat( $messages, $format ){
        $url = ASSREDAC_OLLAMA_URL . '/api/chat';

        $body = array(
            'model' => ASSREDAC_MODEL,
            'messages' => $messages,
            'format' => $format,
            'stream' => false,
        );

        $args = array(
            'headers' => array('Content-Type' => "application/json"),
            'body' => wp_json_encode($body),
            'timeout' => ASSREDAC_TIMEOUT, 
        );
        $response = wp_remote_post( $url, $args ); 

        if (is_wp_error($response)){
            return new WP_Error( 'assredac_unreachable', 'Ollama est injoignable : ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        if ( 200 !== $code ) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            $details = isset( $data['error']) ? $data['error'] : 'erreur inconnue';

            error_log( print_r( $response, true ) );
            return new WP_Error('assredac_http_error', 'Ollama a renvoyé une erreur (' . $code . ') : ' . $details);
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ( !is_array($data)){
            return new WP_Error('assredac_invalid_json', "La réponse d'Ollama est illisible ");
        }

        return $data; 
    }
}   