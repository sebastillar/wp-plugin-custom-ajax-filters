<?php
/*
Plugin Name: Plaza Mayor Custom Filters
Plugin URI:  https://www.plazamayor.com.uy/
Description: Filtros avanzados y customizados para el sitio Plaza Mayor
Version:     1.0.0
Author:      Sebastillar
Author URI:  https://sebastillar.uy
License:     GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PLAZAM_PLUGIN_URL', plugin_dir_url( __FILE__ )); // Plugin directory URL
define( 'PLAZAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); // Plugin directory path
define( 'PLAZAM_PLUGIN_PATH', dirname( __FILE__ ));


//Main plugin file
require_once 'classes/class-plazam-init.php';

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function plazam_textdomain() {
    load_plugin_textdomain( 'plazam-mayor-custom-filters', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'plazam_textdomain' );

Plazam::run();    
      




