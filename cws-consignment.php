<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://charlenesweb.ca
 * @since             1.0.0
 * @package           CWS_Consignment
 *
 * @wordpress-plugin
 * Plugin Name:       Consignment Store for WooCommerce
 * Plugin URI:        https://charlenesweb.ca/
 * Description:       Consignment Store for WooCommerce
 * Version:           1.7.9
 * Author:            Charlene's Web Services
 * Author URI:        https://charlenesweb.ca/plugins
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cws-consignment
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * First release 1.0.0 and then using SemVer - https://semver.org X.Y.Z (Major.Minor.Patch)
 */
define( 'CWS_CONSIGNMENT_VERSION', '1.6' );
define('CWSCS_SRC_DIR', dirname(__FILE__) );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cws-consignment-activator.php
 */
function activate_cws_consignment() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cws-consignment-activator.php';
	cws_consignment_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cws-consignment-deactivator.php
 */
function deactivate_cws_consignment() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cws-consignment-deactivator.php';
	cws_consignment_Deactivator::deactivate();
}
register_activation_hook( __FILE__, 'activate_cws_consignment' );
register_deactivation_hook( __FILE__, 'deactivate_cws_consignment' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cws-consignment.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cws_consignment() {
	$plugin = new CWS_Consignment();
	$plugin->run();
}
run_cws_consignment();
