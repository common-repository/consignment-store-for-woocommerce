<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://charlenesweb.ca
 * @since      1.0.0
 *
 * @package    CWS_Consignment
 * @subpackage CWS_Consignment/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    CWS_Consignment
 * @subpackage CWS_Consignment/includes
 * @author     Charlene Copeland <charlene@charlenesweb.ca>
 */
class cws_consignment_i18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_cws_consignment_textdomain() {

		load_plugin_textdomain(
			'cws-consignment',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
