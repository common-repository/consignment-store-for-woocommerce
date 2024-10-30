<?php

/**
 * Fired during plugin activation
 *
 * @link       https://charlenesweb.ca
 * @since      1.0.0
 *
 * @package    CWS_Consignment
 * @subpackage CWS_Consignment/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    CWS_Consignment
 * @subpackage CWS_Consignment/includes
 * @author     Charlene Copeland <charlene@charlenesweb.ca>
 */
class cws_consignment_Activator {

	/**
	 * CWS Consignment Store for WooCommerce.
	 *
	 * Sellers may upload their consignment items for review. The store manager can approve or reject each item. If they approve, the item is added automatically to the WooCommerce store. If they reject, the seller is notified. 
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Set up inventory table in website
		global $wpdb;
		$base = $wpdb->base_prefix;
		$prefix = $wpdb->prefix; // this blog id
		// inventory table
		$table_name = $prefix . "cwscs_inventory";
		$charset_collate = $wpdb->get_charset_collate();
		$query = $wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table_name));
		if ($wpdb->get_var($query) != $table_name) {
			$sql = "CREATE TABLE $table_name (
				`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`item_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
				`item_cat` int(3) DEFAULT '0',
				`item_desc` text,
				`item_tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`item_retail` int(4) NOT NULL DEFAULT '0' COMMENT 'retail price',
				`item_sale` int(4) NOT NULL DEFAULT '0' COMMENT 'price in store',
				`item_size` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`item_colour` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`item_state` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`item_image1` int(7) DEFAULT '0',
				`item_image2` int(7) DEFAULT '0',
				`item_image3` int(7) DEFAULT '0',
				`item_image4` int(7) DEFAULT '0',
				`seller_name` varchar(150) DEFAULT NULL,
				`phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`policy_accepted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=accepted',
				`dropoff` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`sku` varchar(255) NOT NULL DEFAULT '0',
				`store_split` tinyint(2) NOT NULL DEFAULT '50' COMMENT 'Split to store',
				`reviewer_comments` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`approved` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if approved',
				`date_added` date NOT NULL,
				`date_sold` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`sold_price` decimal(8,2) NOT NULL DEFAULT '0.00',
				`picked_up` tinyint(1) NOT NULL DEFAULT '0',
				`paid` decimal(8,2) NOT NULL DEFAULT '0.00',
				`update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	
			  	PRIMARY KEY  (ID)
			) $charset_collate;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
		// Settings table
		$table_name = $prefix . "cwscs_settings";
		$charset_collate = $wpdb->get_charset_collate();
		$query = $wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table_name));
		if ($wpdb->get_var($query) != $table_name) {
			$sql = "CREATE TABLE $table_name (
				ID int(11) NOT NULL AUTO_INCREMENT,
				cwscs_key varchar(45) DEFAULT NULL,
				cwscs_value text,
				cwscs_type varchar(45) DEFAULT NULL,
				`update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  	PRIMARY KEY  (ID)
			) $charset_collate;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
		// error log table
		$table_name = $prefix . "cwscs_errorlog";
		$charset_collate = $wpdb->get_charset_collate();
		$query = $wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table_name));
		if ($wpdb->get_var($query) != $table_name) {
			$sql = "CREATE TABLE $table_name (
				`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`file` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`fcn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`url` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`msg` text,
				`update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  	PRIMARY KEY  (ID)
			) $charset_collate;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
	} // END activate
}
