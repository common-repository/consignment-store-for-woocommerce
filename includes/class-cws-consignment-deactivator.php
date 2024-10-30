<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://charlenesweb.ca
 * @since      1.0.0
 *
 * @package    CWS_Consignment
 * @subpackage CWS_Consignment/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    CWS_Consignment
 * @subpackage CWS_Consignment/includes
 * @author     Charlene Copeland <charlene@charlenesweb.ca>
 */
class cws_consignment_Deactivator {

	/**
	 * CWS Consignment Store for WooCommerce.
	 *
	 * Sellers may upload their consignment items for review. The store manager can approve or reject each item. If they approve, the item is added automatically to the WooCommerce store. If they reject, the seller is notified. 
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// may be nothing to do here
	}

}
