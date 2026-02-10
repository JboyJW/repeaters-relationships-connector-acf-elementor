<?php
/**
 * Plugin Name:Repeaters & Relationships Connector with ACF for Elementor
 * Description: Allows Elementor Loop Grids to use ACF Repeaters and Relationships as a data source.
 * Version: 1.1.1
 * Author: Solid Digital
 * Author URI: https://www.soliddigital.com
 * Text Domain: repeaters-relationships-connector-acf-elementor
 * Requires Plugins: elementor
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace RepRelCon;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

\add_action( 'plugins_loaded', function() {
    $includes_path = \plugin_dir_path( __FILE__ ) . 'includes/';

    require_once $includes_path . 'register_controls.php';
    require_once $includes_path . 'register_dynamic_tag.php';
    require_once $includes_path . 'modify_query_results.php';
} );
