<?php 
/*
 * Plugin Name:       assistant-redaction
 * Description:       Permet la génération d'article grâce à un LLM avec un brief donné
 * Version:           0.1
 * Author:            Lucas BLANCHARD-TOULET
 * Author URI:        https://lucasblanchardtoulet.fr
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! defined( 'ASSISTANT_REDACTION_OLLAMA_URL') ) {
    define( 'ASSISTANT_REDACTION_OLLAMA_URL', 'http://localhost:11434');
}
if ( ! defined( 'ASSISTANT_REDACTION_MODEL') ) {
    define( 'ASSISTANT_REDACTION_MODEL', 'gemma3:4b');
}
if ( ! defined( 'ASSISTANT_REDACTION_TIMEOUT') ) {
    define( 'ASSISTANT_REDACTION_TIMEOUT', '120');
}

