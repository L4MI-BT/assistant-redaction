<?php 
/*
 * Plugin Name:       assistant-redaction
 * Description:       Permet la génération d'article grâce à un LLM avec un brief donné
 * Version:           0.1
 * Author:            Lucas BLANCHARD-TOULET
 * Author URI:        https://lucasblanchardtoulet.fr
 */

require_once plugin_dir_path( __FILE__ ) . 'includes/class-assredac-ollama-client.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-assredac-article-generator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-assredac-admin-page.php';


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! defined( 'ASSREDAC_OLLAMA_URL') ) {
    define( 'ASSREDAC_OLLAMA_URL', 'http://localhost:11434');
}
if ( ! defined( 'ASSREDAC_MODEL') ) {
    define( 'ASSREDAC_MODEL', 'gemma3:4b');
}
if ( ! defined( 'ASSREDAC_TIMEOUT') ) {
    define( 'ASSREDAC_TIMEOUT', '120');
}

// Assemblage : la page reçoit le générateur, qui reçoit le client.
$assredac_client    = new ASSREDAC_Ollama_Client();
$assredac_generator = new ASSREDAC_Article_Generator( $assredac_client );
$assredac_admin     = new ASSREDAC_Admin_Page( $assredac_generator );

add_action( 'admin_menu', array( $assredac_admin, 'register_menu' ) );