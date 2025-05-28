<?php 
/*
 * Plugin Name:       WordPress To HTML
 * Plugin URI:        https://mediusware.com/
 * Description:       Export WordPress Post and Page as HTML Static Page
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.2
 * Author:            Mediusware LTD.
 * Author URI:        https://mediusware.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://mediusware.com/
 * Text Domain:       wp-to-static
 * Domain Path:       /languages
 * Access Url:        https://makewpstatic.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


require_once __DIR__ . '/autoloader.php';

if( ! defined('MW_STATIC_VERSION') ) define( 'MW_STATIC_VERSION', '1.0.0' );

if( ! defined('MW_STATIC_DIR_PATH') ) define( 'MW_STATIC_DIR_PATH', plugin_dir_path(__FILE__) );

if( ! defined('MW_STATIC_PATH_URL') ) define( 'MW_STATIC_PATH_URL', plugin_dir_url(__FILE__) );

if( !defined('MW_STATIC_UPLOAD_DIR') ) define( 'MW_STATIC_UPLOAD_DIR', WP_CONTENT_DIR . '/uploads/mw-static' );

use MW_STATIC\Inc\Services\Repo\Plugin_Uri;
use MW_STATIC\Inc\Static_Init;

function mw_static_plugin_init() {
    require_once __DIR__ . '/inc/static-init.php';
    new Static_Init();
}
add_action( 'init', 'mw_static_plugin_init' );

register_activation_hook( __FILE__, [ 'MW_STATIC\Inc\Static_Init', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'MW_STATIC\Inc\Static_Init', 'deactivate' ] );
register_uninstall_hook( __FILE__, [ 'MW_STATIC\Inc\Static_Init', 'uninstall' ] );
