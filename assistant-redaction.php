<?php 
/*
 * Plugin Name:       assistant-redaction
 * Description:       Permet la génération d'article grâce à un LLM avec un brief donné
 * Version:           0.1
 * Author:            Lucas BLANCHARD-TOULET
 * Author URI:        https://lucasblanchardtoulet.fr
 */

require_once('includes/class-assredac-ollama-client.php');
require_once('includes/class-article-generator.php');

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
