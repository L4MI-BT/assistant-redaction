<?php

class ASSREDAC_Article_Generator {

	private const SYSTEM_PROMPT = "Tu es rédacteur web pour une rédaction. À partir du brief fourni, tu rédiges directement un article complet d'environ 300 mots, sans jamais demander de précisions.\n"
		. "Tu réponds uniquement avec un objet JSON contenant deux champs :\n"
		. "- « titre » : le titre de l'article, en texte simple ;\n"
		. "- « contenu » : le corps de l'article en HTML, avec uniquement des balises <p> pour les paragraphes et <h2> pour les intertitres.\n"
		. "Règles :\n"
		. "- Écris dans la langue indiquée dans le brief et respecte le ton demandé.\n"
		. "- Utilise le mot-clé deux ou trois fois, de façon naturelle.\n"
		. "- Reste sur des informations générales et reconnues : ne cite aucune étude, statistique ni source.\n"
		. "- Le texte ne contient ni émoji ni mise en forme autre que les balises <p> et <h2>.";

	private const FORMAT = array(
		'type'       => 'object',
		'properties' => array(
			'titre'   => array( 'type' => 'string' ),
			'contenu' => array( 'type' => 'string' ),
		),
		'required'   => array( 'titre', 'contenu' ),
	); 
	
	private const MIN_WORDS = 100;
	private const ALLOWED_TAGS = array(
		'p'  => array(),  //Clé = tags autorisé, valeurs = listes des attribue autorisé. Tab vide = aucun atrribut
		'h2' => array(),
	);
	private $client; // L'instance d'ASSREDAC_Ollama_Client


	public function __construct( $client ) {
        $this->client = $client;
    }

	/**
	 * Génère un article à partir d'un brief.
	 *
	 * @param array $brief Le brief : sujet, mot_cle, ton, langue, consigne (facultative).
	 * @return array|WP_Error L'article (titre, contenu), ou une erreur.
	 */
	public function generate( $brief ) {

		$message = $this->build_messages($brief);

		$response = $this->client->chat($message, self::FORMAT);
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$article = $this->parse_response($response);
		if ( is_wp_error( $article ) ) {
			return $article;
		}

		$article = $this->clean_article($article);

		if ( '' === $article['titre'] ) {
			return new WP_Error( 'assredac_empty_title', "Le titre généré est vide. Réessayez." );
		}

		$nb_mots = $this->count_words($article['contenu']);

		if (self::MIN_WORDS > $nb_mots){
			return new WP_Error( 'assredac_tooshort', 'La réponse du modèle est trop courte (' . $nb_mots . ' mots). Réessayez.' );
		}
		return $article;

	}   


	// Construction du prompt
	private function build_messages( $brief ) {

        $lignes = array(
            'Sujet : ' . $brief['sujet'],
            'Mot-clé : ' . $brief['mot_cle'],
            'Ton : ' . $brief['ton'],
            'Langue : ' . $brief['langue'],
        );
        
        if ( ! empty( $brief['consigne'] ) ) {
			$lignes[] = 'Consigne : ' . $brief['consigne'];
		}

        return array(
			array( 'role' => 'system', 'content' => self::SYSTEM_PROMPT ),
			array( 'role' => 'user', 'content' => implode( "\n", $lignes ) ),
		);
    }

	// récupère et vérifie le message retourner par ollama
	private function parse_response( $response ) {

        // 1. Le modèle doit avoir terminé normalement (et pas avoir été coupé).
		if ( !isset( $response['done_reason'] ) || 'stop' !== $response['done_reason'] ) {
			return new WP_Error( 'assredac_truncated', 'La réponse du modèle est incomplète. Réessayez.' );
		}

        //Vérifie que le texte généré existe
        if ( !isset( $response['message']['content'])) {
			return new WP_Error( 'assredac_noexisting', 'La réponse du modèle est incomplète. Réessayez.' );
        }

        //Vérifie que c'est bien du JSON retourner
        $article = json_decode($response['message']['content'], true);
        if ( !is_array($article)){
            return new WP_Error('assredac_invalid_json', "L'article généré n'est pas au format attendu. Réessayez. ");
        }

        //Vérifie que les champs titre et contenu sont existant et non vide
		foreach ( array( 'titre', 'contenu' ) as $champ ) {
			if ( empty( $article[ $champ ] ) || !is_string( $article[ $champ ] ) || '' === trim( $article[ $champ ] ) ) {
				return new WP_Error( 'assredac_invalid_article', "L'article généré est incomplet (champ " . $champ . ' manquant). Réessayez.' );
			}
		}

        return array(
			'titre'   => $article['titre'],
			'contenu' => $article['contenu'],
		);
    }     

	public function clean_article( $article ) {
		$titre = sanitize_text_field($article['titre']);

		$contenu = wp_kses($article['contenu'], self::ALLOWED_TAGS);

		$contenu = force_balance_tags($contenu);

		return array(
			'titre' => $titre,
			'contenu' => $contenu,
		);
	}   

	private function count_words( $html ) {
		$texte = wp_strip_all_tags($html);  //Retire les balise html

		$mots = preg_split('/\s+/', trim($texte), -1, PREG_SPLIT_NO_EMPTY);

		return count($mots);

	}            
	            
}