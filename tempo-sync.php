<?php
/**
 * Plugin Name: Tempo Sync
 * Description: Tempo Sync.
 * Version: 0.9
 * Author: Support
 * Author URI: https://prosvit.design
 * Text Domain: tempo
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
if ( ! defined( 'P_VERSION' ) ) {
	define( 'P_VERSION', '0.4' );
}

if ( ! defined( 'P_PATH' ) ) {
	define( 'P_PATH', dirname( __FILE__ ) . '/' );
}

if ( ! defined( 'P_URL_FOLDER' ) ) {
	define( 'P_URL_FOLDER', plugin_dir_url( __FILE__ ) );
}

// Include the main class.
register_activation_hook(__FILE__, 'p_activate');

register_deactivation_hook( __FILE__, 'p_deactivate' );

include P_PATH . 'autoloader.php';
include P_PATH . 'includes/functions/functions.php';

p_startup();

function p_activate()
{
	\system\Install::install_depencies();
}

function p_deactivate()
{
    
}
