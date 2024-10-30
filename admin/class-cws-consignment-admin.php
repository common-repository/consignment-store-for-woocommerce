<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://charlenesweb.ca
 * @since      1.0.0
 *
 * @package    cws_consignment
 * @subpackage cws_consignment/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    cws_consignment
 * @subpackage cws_consignment/admin
 * @author     Charlene Copeland <charlene@charlenesweb.ca>
 */
class cws_consignment_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cws_consignment    The ID of this plugin.
	 */
	private $cws_consignment;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $cws_consignment       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $cws_consignment, $version ) {

		$this->plugin_name = $cws_consignment;
		$this->version = $version;
		add_action( 'init', array( $this, 'init_ajax' ), 20 );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in cws_consignment_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The cws_consignment_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cws-consignment-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cws-consignment-admin.js', array( 'jquery' ), $this->version, false );
		
		// for ajax functions
		wp_localize_script(
			$this->plugin_name,
			'my_ajax_obj',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'cwscs_doajax' ),
			)
		);
	}
	public function init_ajax() {
		add_shortcode( 'additemform', array($this, 'additemform_func') );
	}
	// Log errors
	public function cwscsLogError($system, $file, $fcn, $url, $msg) {
		global $wpdb;
		$prefix = $wpdb->prefix; 
		
		$query = $wpdb->insert( 
			$prefix.'cwscs_errorlog', 
				array( 
					'system' => sanitize_text_field($system), 
					'file' => sanitize_text_field($file),
					'fcn' => sanitize_text_field($fcn),
					'url' => esc_url_raw($url),
					'msg' => sanitize_text_field($msg)
				), 
				array('%s', '%s', '%s', '%s', '%s') 
		);
		$wpdb->print_error();
		$result = $wpdb->insert_id;
		return $result;
	} // END cwscsLogError
	/**
     * create menu structure
     */
    public function add_menu_pages() {
        // top_level_menu
        add_menu_page(
            'CWS Consignment Store',
            'CWS Consignment Store',
            'edit_posts',
            'cws_cons_top_level',
            null,
            'dashicons-money-alt',
            '10.842015'
        );

        // Rename first
		$parent_slug = 'cws_cons_top_level';
        $page_app_suffix = add_submenu_page(
            $parent_slug,
            __('Submitted Items', 'cws-consignment'),
            __('Submitted Items', 'cws-consignment'),
            'edit_posts',
            'cws_cons_top_level',
            array($this, 'top_level_consignment')
        );
        // payouts
        $page_worker_suffix = add_submenu_page(
            'cws_cons_top_level',
            __('Payments', 'cws-consignment'),
            '' . __('Manage Payouts', 'cws-consignment'),
            'manage_options',
            'cws_cons_payments',
            array($this, 'cwscspayments_page')
        );

        // settings
        $page_settings_suffix = add_submenu_page(
            'cws_cons_top_level',
            __('Settings', 'cws-consignment'),
            '' . __('Settings', 'cws-consignment'),
            'manage_options',
            'cwscs_app_settings',
            array($this, 'cwscstop_settings_menu')
        );

        // documentation
        $page_docs_suffix = add_submenu_page(
            'cws_cons_top_level',
            __('Documentation', 'cws-consignment'),
            '' . __('Documentation', 'cws-consignment'),
            'manage_options',
            'cws_cons_docs',
            array($this, 'cwscsdocs_page')
        );
	}
	/**
     * Content of appointments admin page
     */
    public function top_level_consignment() {
		if ( is_user_logged_in() ) {
			echo '<h1>CWS Consignment Store</h1>
			<h2>Review Submitted Items</h2>';
			
			// initialize vars
			$menu_slug = "cws_cons_top_level";

			// get roles
			global $current_user;
			wp_get_current_user();
			$roles = $current_user->roles;
			
			if (in_array("administrator", $roles)) {
				// Display functions
				require_once plugin_dir_path( __FILE__ ) . 'partials/cws-consignment-admin-display.php';

				// Item selected?
				if (isset($_POST['item_id'])) {
					// was it an approve/reject?
					$_POST['item_id'] = sanitize_text_field($_POST['item_id']);
					if (isset($_POST['approved'])) {
						if ($_POST['approved'] == 1) { // approved
							if ($_POST['sku'] == "") {
								echo '<p class="failmsg">You must enter a unique SKU.</p>';
							} else {
								// update item in inventory, save to WC, email the sender
								cwscsApproveItem(); 
							}
						} elseif ($_POST['approved'] == 2) { // rejected
							// delete item from inventory, delete all images,  email the sender
							cwscsRejectItem(); 
						} 
						$results = cwscsGetInventory(0); // get all submitted, not approved items
					} // END approved / rejected
					else {
						$found = false;
						$results = cwscsGetInventory(0); // get all submitted, not approved items
						$clean_item_id = sanitize_text_field($_POST['item_id']);
						if (is_array($results) || is_object($results)) { 
							// show item details, all images and the approve/reject form
							foreach ($results as $i => $row) {
								if ($row->ID == intval($clean_item_id)) {
									showApproveRejectForm($menu_slug, $row);
									$found = true;
								}
							}
							if (!$found)
								echo '<p class="failmsg">Could not find match for '.esc_html($clean_item_id).'</p>';
						} else {
							echo '<p>Error fetching inventory.</p>';
						}
					}
				} else
					$results = cwscsGetInventory(0); // get all submitted, not approved items
					
				cwscsShowSubmittedPage($menu_slug, $results); // will display form
			} else {
				echo '<p class="failmsg">You are not authorized to be here. </p>';
			}
		} // END is logged in
		else
			echo '<p class="failmsg">You are not authorized to be here. </p>';
    }

	public function cwscspayments_page() {
		if ( is_user_logged_in() ) {
			echo '<h1>Payments to Sellers</h1>
			<p>Below is the list of items that were submitted to the consignment store and have now been sold. Record payments to the sellers here.</p>';
			// initialize vars
			$menu_slug = "cws_cons_payments";
		
			// Display functions
			require_once plugin_dir_path( __FILE__ ) . 'partials/cws-consignment-admin-display.php';
			
			// handle submission from the filter form
			if (isset($_POST['search_sku']))
				$search_sku = sanitize_text_field($_POST['search_sku']);
			else
				$search_sku = "";
			if (isset($_POST['search_kw']))
				$search_kw = sanitize_text_field($_POST['search_kw']);
			else
				$search_kw = "";
			if (isset($_POST['payment_type'])) {
				$show = sanitize_text_field($_POST['payment_type']); // radio button
			} else
				$show = "unpaid"; // default
			// Did they click to Manage Payment or save payment?
			if (isset($_POST['item_id'])) {
				// was it to save a payment?
				if (isset($_POST['paidpayment'])) {
					 cwscsSavePayment();
				} else { 
					// fetch the item
					$item = cwscsGetInventoryByID(sanitize_text_field($_POST['item_id']));
					cwscsShowPaymentForm($menu_slug, $item);
					// show the form to save a payment, show any payment so far
				} // END show form
			} // END form submitted
				
			// Get the data
			$results = cwscsGetInventorySold($show, $search_sku, $search_kw);	
			// Show the data
			cwscsShowPayoutsPage($menu_slug, $search_sku, $search_kw, $show, $results);
		} // END is logged in
		else
			echo '<p class="failmsg">You are not authorized to be here. </p>';
	}
	// SETTINGS!
	public function cwscstop_settings_menu() {
		if ( is_user_logged_in() ) {
			echo '<h1>Settings</h1>';

			// initialize vars
			$menu_slug = "cwscs_app_settings";
			$msg = "";
			// Form submitted?
			if (isset($_POST['cwscs_key']) && $_POST['cwscs_key'] != "") {
				$cwscs_key = sanitize_text_field($_POST['cwscs_key']);
				if (isset($_POST['cwscs_value'])) // may be blank
					$cwscs_value = sanitize_text_field($_POST['cwscs_value']);
				else
					$cwscs_value = "";
				if (isset($_POST['method'])) // may be blank
					$cwscs_method = sanitize_text_field($_POST['cwscs_method']);
				else {
					$cwscs_method = "";
				}
				$results = cwscsSaveSetting($cwscs_key, $cwscs_value); // sets status, msg
				if ($results['status'] == 1) {
					$msg = '<p class="successmsg">Changes have been saved.</p>';
				} else {
					$msg = '<p class="failmsg">Could not update. Please refresh and try again.</p>';
				}
			}

			// Display functions
			require_once plugin_dir_path( __FILE__ ) . 'partials/cws-consignment-admin-display.php';
			cwscsShowSettingsMenu($menu_slug, $msg);
		} else
			echo '<p class="failmsg">You are not authorized to be here. </p>';
	}
	public function cwscsreports_page() {
		echo '<h1>Reports</h1>';
	}
	public function cwscsdocs_page() {
		// Display functions
		require_once plugin_dir_path( __FILE__ ) . 'partials/cws-consignment-admin-display.php';
		cwscsShowDocsPage();
	}
}
//////////////////////////////////
// Inventory Table FUNCTIONS
//////////////////////////////////

// Retrieve inventory of a certain approved
function cwscsGetInventory($approved) {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$wpdb->hide_errors();
	$ok = true;
	
	$results = array();
	if (isset($approved) && ($approved == 0 || $approved == 1 || $approved == 2)) { // not approved
		$results = $wpdb->get_results( 'SELECT * FROM '.$prefix.'cwscs_inventory WHERE approved='.$approved.' ORDER BY date_added' ); 
	} else {
		$results = $wpdb->get_results( 'SELECT * FROM '.$prefix.'cwscs_inventory ORDER BY date_added' ); 
	}
	
	if (!is_object($results) && !is_array($results)) {
		$tmp = 'Failed to find inventory. Error is '.$wpdb->last_error.'. ';
		$results = '<p class="failmsg">'.$tmp.'. </p>';
		$url = get_site_url();
		$test = cwscsLogError("admin", "class-cws-consignment-admin-php", "cwscsGetInventory", $url, $tmp);
		$ok = false;
	}
	// if getting approved then check woo for SKU and add woo description
	if ($ok && ($approved == 1 || $approved == -1)) { // approved or all
		foreach ($results as $i => $row) {
			if ($row->approved == 1 && $row->sku != "") {
				$woo = cwscsGetWooBySkuAdmin($row->sku); // store details
				$results[$i]->status = $woo['status'];
				if (isset($woo['status']) && $woo['status'] == 0) {
					$results[$i]->msg = $woo['msg'];
				} else {
					$results[$i]->woo = $woo; // stock and sales
				}
			} // END is approved and has SKU
		} // END loop on results
	}
	return $results;
}
// get a specific item from inventory
function cwscsGetInventoryByID($id) {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$wpdb->hide_errors();
	
	$results = 1;
	if (isset($id) && $id > 0) {
		$id = sanitize_text_field($id);
		$results = $wpdb->get_results( 'SELECT * FROM '.$prefix.'cwscs_inventory WHERE ID='.$id ); 
	}
	if (!is_object($results) && !is_array($results)) {
		$tmp = 'Could not find the item in inventory. ';
		$item = '<p class="failmsg">'.$tmp.'.</p>';
		$url = get_site_url();
		$test = cwscsLogError("admin", "class-cws-consignment-admin-php", "cwscsGetInventoryByID", $url, $tmp.'ID: '.$id);
	} else {
		$item = $results[0];
	}
	return $item;
}

function cwscsGetInventoryBySKU($sku) {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$wpdb->hide_errors();
	$results = 1;
	if (isset($sku) && $sku != "") {
		$sku = sanitize_text_field($sku);
		$results = $wpdb->get_results( 'SELECT * FROM '.$prefix.'cwscs_inventory WHERE sku='.$sku); 
	}
	
	if (!is_object($results) && !is_array($results)) {
		$tmp = 'Failed to find item in inventory for store tag '.$sku.'. Error is '.$wpdb->last_error.'. ';
		$item = '<p class="failmsg">'.$tmp.'. Error emailed to Charlene. </p>';
		$url = get_site_url();
		$test = cwscsLogError("admin", "class-cws-consignment-admin-php", "cwscsGetInventoryBySKU", $url, $tmp.'SKU: '.$sku);
	} else {
		$item = $results; // only 1
	}
	return $item;
}

// get inventory items that match the keyword in either title or desc
function cwscsGetInventoryByKw($search_kw) {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$wpdb->hide_errors();
	$results = array();
	if ($search_kw != "") {
		$search_kw = sanitize_text_field($search_kw);
		$search_kw = str_replace(' ', '%', $search_kw);
		$search_kw = '%'.$search_kw.'%';
	}
	$results = $wpdb->get_results( 'SELECT * FROM '.$prefix.'cwscs_inventory WHERE item_title LIKE "'.$search_kw.'" OR item_desc LIKE "'.$search_kw.'" OR item_size LIKE "'.$search_kw.'" OR item_colour LIKE "'.$search_kw.'" OR item_state LIKE "'.$search_kw.'" ORDER BY date_added' ); 
	
	if (!is_object($results) && !is_array($results)) {
		$tmp = 'Failed to find inventory. Error is '.$wpdb->last_error.'. ';
		$results = '<p class="failmsg">'.$tmp.'. </p>';
		$url = get_site_url();
		$test = cwscsLogError("admin", "class-cws-consignment-admin-php", "cwscsGetInventoryBySKU", $url, $tmp.'SKU: '.$sku);
	}
	return $results;
}
// Search inventory table for approved items. Check in woocommerce if they have been sold. 
function cwscsGetInventorySold($show="unpaid", $search_sku="", $search_kw="") {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$wpdb->hide_errors();
	$results = array();
	// figure out the where statement
	$where = "WHERE approved=1";
	$conn = " AND ";
	if ($show == "unpaid") {
		$where .= $conn.'paid=0';
		$conn = ' AND ';
	} elseif ($show == "paid") {
		$where .= $conn.'paid>0';
		$conn = ' AND ';
	}
	if (isset($search_sku) && $search_sku > 0) {
		$search_sku = sanitize_text_field($search_sku);
		$where .= $conn.' sku='.$search_sku;
		$conn = ' AND ';
	} elseif (isset($search_kw) && $search_kw != "") {
		$search_kw = sanitize_text_field($search_kw);
		$search_kw = str_replace(' ', '%', $search_kw);
		$search_kw = '%'.$search_kw.'%';
		$where .= $conn.' (item_title LIKE "'.$search_kw.'" OR item_desc LIKE "'.$search_kw.'" OR item_size LIKE "'.$search_kw.'" OR item_colour LIKE "'.$search_kw.'" OR item_state LIKE "'.$search_kw.'")';
	}

	$items = $wpdb->get_results( 'SELECT * FROM '.$prefix.'cwscs_inventory '.$where.' ORDER BY sku' ); 
	
	if (!is_object($items) && !is_array($items)) {
		$tmp = 'Failed to find inventory. Error is '.$wpdb->last_error.'. Search criteria are '.$show.' store tag: '.$search_sku.', keywords: '.$search_kw.'. WHERE is '.$where;
		$results = array();
		$url = get_site_url();
		$test = cwscsLogError("admin", "class-cws-consignment-admin-php", "cwscsGetInventorySold", $url, $tmp);
	} elseif (count($items) == 0)
		$results = array();
	else {
		// get woocommerce details
		$ctr_r = 0;
		foreach ($items as $i => $row) {
			$woo = cwscsGetWooBySkuAdmin($row->sku); // store details
			if (isset($woo['woo_sales']) && $woo['woo_sales'] > 0) { // sold so include it
				$results[] = $row;
				$results[$ctr_r]->woo = $woo; // stock and sale
				$ctr_r++;
			} // END item was sold
		} // END loop
	}
	
	return $results;
}

// Administrator approved a submitted item
function cwscsApproveItem() {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$wpdb->hide_errors();
	$ok = true;
	$url = get_site_url();
	$_POST['item_id'] = intval($_POST['item_id']);
	if (!isset($_POST) || !isset($_POST['item_id']) || $_POST['item_id'] <= 0) {
		echo '<p class="failmsg">There was an error approving this item. Please refresh and try again. </p>';
		$ok = false;
	} elseif ($_POST['sku'] == "") {
		echo '<p class="failmsg">You must enter a unique SKU.</p>';
		$ok = false;
	} else {
		// check that this sku is not already in WC
		$woo = cwscsGetWooBySkuAdmin(sanitize_text_field($_POST['sku']));
		if (isset($woo['status']) && $woo['status'] == 1) {
			echo '<p class="failmsg">That sku already exists in the store. Please enter a different one.</p>';
			$ok = false;
		}
	}
	
	if ($ok) {
		// APPROVED. Update inventory item as approved, with comments
		$result = $wpdb->update ( $prefix.'cwscs_inventory',
			array(  
				'approved' => 1, 
				'sku' => sanitize_text_field($_POST['sku']),
				'reviewer_comments' => sanitize_text_field($_POST['reviewer_comments'])
			), 
			array(
				'ID' => sanitize_text_field($_POST['item_id'])
			), 
			array('%d', '%s', '%s') ,
			array( '%d' ) 
		);
		if (!$result) {
			$tmp = '<p class="failmsg">Could not save item as approved:  '.sanitize_text_field($_POST['item_id']).' from '.sanitize_text_field($_POST['seller_name']).', '.sanitize_email($_POST['email']).'. Error is '.$wpdb->last_error.'. </p>';
			$ok = false;
		} // END bad result from update inventory
		if ($ok) {
			$action = "insert";
			// INSERT - updated inventory successfully. Now add to woocommerce
			$post_id = cwscsAddItemToWCadmin($_POST, "publish"); // try in includes
			if (!$post_id) {
				$tmp = '<p class="failmsg">Could not save item to store. Error is '.$wpdb->last_error.'. </p>';
				$ok = false;
			}
		} // msg is blank
	}// sku and item_id
	if ($ok) {
		echo '<p class="successmsg">Item approved and saved to store successfully. </p>';	
		// send email to Seller if checked
		if (isset($_POST['approved_sendemail']) && $_POST['approved_sendemail'] == "Yes" && isset($_POST['approved-email']) && $_POST['approved-email'] != "" && isset($_POST['approved-body']) && $_POST['approved-body'] != "") {
			$emails = cwscsGetMyEmails();
			if (isset($emails) && isset($emails[0]) && $emails[0] != "") {
				$to = sanitize_email($_POST['approved-email']);
				$from = sanitize_email($emails[0]);
				$body = sanitize_textarea_field($_POST['approved-body']);
				$headers="From: ".$from."\r\n";
				$subject = get_option('siteurl').' has accepted your item!';
				$test = wp_mail($to, $subject, $body, $headers);
				if ($test)
					echo '<p class="successmsg">An email sent. </p>';
				else
					echo '<p class="failmsg">Could not send email. </p>';
			}
		}
	} // END no errors after add to woo, update to inventory
}

// Administrator rejected a submitted item
function cwscsRejectItem() {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$wpdb->hide_errors();
	$ok = true;
	$url = get_site_url();
	$headers="From: no-reply@".$url."\r\n";
	$_POST['item_id'] = intval($_POST['item_id']);
	if (!isset($_POST) || !isset($_POST['item_id']) || $_POST['item_id'] <= 0) {
		echo '<p class="failmsg">There was an error rejecting this item. Please refresh and try again. </p>';
		$ok = false;
	} else {
		$res = $wpdb->delete( $prefix.'cwscs_inventory', array( 'ID' => sanitize_text_field($_POST['item_id'])));
		if ($res == 1) { // deleted
			// Remove images
			for ($i=1; $i<=4; $i++) {
				$_POST['item_image'.$i] = sanitize_text_field($_POST['item_image'.$i]);
				if (isset($_POST['item_image'.$i]) && $_POST['item_image'.$i] > 0) {
					$isImageDeleted = wp_delete_attachment(sanitize_text_field($_POST['item_image'.$i]), false ); // send to trash
					if (!$isImageDeleted) {
						echo 'Could not delete image. ';
						$ok = false;
					}
				}
			}
			if ($ok)
				echo '<p class="successmsg">Successfully deleted item from submitted items. </p>';	
		} else { // error
			echo '<p class="failmsg">Could not delete item from inventory. </p>';
			$test = cwscsLogError("admin", "class-cws-consignment-admin-php", "cwscsRejectItem", $url, "Could not delete inventory ".sanitize_text_field($_POST['id']).'. Error: '.$wpdb->last_error);
			$ok = false;
		}
	}

	// send email to Seller if selected
	if (isset($_POST['rejected_sendemail']) && $_POST['rejected_sendemail'] == "Yes" && isset($_POST['rejected-email']) && $_POST['rejected-email'] != "" && isset($_POST['rejected-body']) && $_POST['rejected-body'] != "") {
		$emails = cwscsGetMyEmails();
		if (isset($emails) && isset($emails[0]) && $emails[0] != "") {
			$to = sanitize_email($_POST['rejected-email']);
			$from = sanitize_email($emails[0]);
			$body = sanitize_textarea_field($_POST['rejected-body']);
			$headers="From: ".$from."\r\n";
			$subject = 'Update from '.get_option('siteurl');
			$test = wp_mail($to, $subject, $body, $headers);
			if ($test)
				echo '<p class="successmsg">An email was sent. </p>';
			else {
				echo '<p class="failmsg">Could not send email. </p>';
				$ok = false;
			}
		}
	}
	if ($ok)
		echo '<p class="successmsg">The item has been saved to the database as REJECTED. </p>';
}

// Administrator added a payment
function cwscsSavePayment() {
	global $wpdb;
	$prefix = $wpdb->prefix;
	$wpdb->hide_errors();
	$ok = true;
	$url = get_site_url();
	$_POST['item_id'] = intval($_POST['item_id']);
	if (!isset($_POST) || !isset($_POST['item_id']) || $_POST['item_id'] <= 0) {
		echo '<p class="failmsg">There was an error rejecting this item. Please refresh and try again. </p>';
		$ok = false;
	} elseif (!isset($_POST['paidpayment']) || $_POST['paidpayment'] < 0) {
		echo '<p class="failmsg">Please enter a valid payment. </p>';
		$ok = false;
	} else {
		$table_name = $prefix.'cwscs_inventory'; //custom table name
        $id = sanitize_text_field($_POST['item_id']);
		$paid = sanitize_text_field($_POST['paidpayment']) * 1;
	    $result = $wpdb->query( $wpdb->prepare("UPDATE $table_name SET paid = ".$paid." WHERE ID =".$id));
		if ($wpdb->last_error) {
			echo '<p class="failmsg">Could not save payment for item. Error is '.esc_html($wpdb->last_error).'. </p>';
			$ok = false;
		} elseif (!$result) { // ok but no update
			echo '<p class="warnmsg">Nothing was updated.</p>';
			$ok = false;
		}
	}// sku and item_id
	if ($ok)
		echo '<p class="successmsg">Payment has been saved successfully. </p>';	
}

//////////////////////////////////
// Settings Table FUNCTIONS
//////////////////////////////////
function cwscsGetSettingByKey($key) {
	global $wpdb;
	$prefix = $wpdb->prefix;
	$table = "cwscs_settings";
	$wpdb->hide_errors();

	if ($key == "") {
		$results = array("status"=>0, "msg"=>'Please enter a valid key for the settings table.');
	} else {
		$key = sanitize_text_field($key);
		$values = $wpdb->get_results( 'SELECT cwscs_value FROM '.$prefix.$table.' WHERE cwscs_key="'.$key.'"'); 
		
		if (!is_object($values) && !is_array($values)) {
			$tmp = 'Failed to find setting for '.$key.'. Error is '.$wpdb->last_error.'. ';
			$results = array("status"=>0, "msg"=>$tmp);
			$url = get_site_url();
			$test = cwscsLogError("admin", "class-cws-consignment-admin-php", "cwscsGetSettingByKey", $url, $tmp);
		} elseif (count($values) == 0) {
			$results = array("status"=>0, "data"=>"", "msg"=>"");
		} else { // there is a value
			$val = $values[0]->cwscs_value;
			$results = array("status"=>1, "data"=>$val, "msg"=>"");
		}
	}
	return $results;
}

// like above except returns the data split into an array
function cwscsGetSettingByKeyReturnArray($key) {
	$results = cwscsGetSettingByKey($key);
	$data = array();
	if ($results['status'] == 1 && $results['data'] != "") {
		$data_str = $results['data'];
		$data = explode('::',  $data_str);
		foreach ($data as $i => $d)
			$data[$i] = trim($d);
	}
	return $data;
}
// Administrator saved a setting
function cwscsSaveSetting($cwscs_key, $cwscs_value) {
	global $wpdb;
	$prefix = $wpdb->prefix;
	$table = "cwscs_settings";
	$wpdb->hide_errors();
	$msg = "";
	$status = 1;
	
	$url = get_site_url();
	
	if (!isset($cwscs_key) || $cwscs_key == "") {
		$msg .= 'There was a problem saving the settings because the key was not set. Please refresh and try again.';
	} elseif (!isset($cwscs_value)) {
		$msg .= 'Please enter a valid setting.';
	} else {
		// Update or insert?
		$values = cwscsGetSettingByKey($cwscs_key);
		if ($values['status'] == 0) 
			$action = "insert";
		else
			$action = "update";
		if ($action == "update") {
			// if value is blank then delete
			$result = $wpdb->update ( $prefix.$table,
				array('cwscs_value' => sanitize_text_field($cwscs_value)), array('cwscs_key' => sanitize_text_field($cwscs_key)), array('%s'));
			if ($wpdb->last_error) {
				$msg = 'Could not save setting for '.sanitize_text_field($cwscs_key).', value: '. sanitize_text_field($cwscs_value).'. Error is '.$wpdb->last_error.'. ';
			} // END bad result from update inventory
		} else { // insert
			$query = $wpdb->insert( 
				$prefix.$table, 
					array( 
						'cwscs_key' => sanitize_text_field($cwscs_key), 
						'cwscs_value' => sanitize_text_field($cwscs_value)
					), 
					array( 
						'%s', '%s'
					) 
			);
			$wpdb->print_error();
			if ($wpdb->insert_id < 0) {
				$msg = 'Could not add setting for '.sanitize_text_field($cwscs_key).', value '.sanitize_text_field($cwscs_key).'. Error is '.$wpdb->last_error.'. ';
				
			} else {
				$result = $wpdb->insert_id;
			}
		}
	} // there is a key and a value
	if ($msg == "") {
		$msg = 'Settings have been updated.';
		$results = array("status"=>1, "msg"=>$msg);
	} else { // log the error
		$system = "admin";
		$fcn = "cwscsSaveSetting";
		$file = "class-cws-consignment-admin.php";
		$url = get_site_url();
		//cwscsLogError($system, $file, $fcn, $url, $tmp);
		$results = array("status"=>0, "msg"=>$msg);
	}
	return $results;
}

// get all categories and then check saved settings to determine which should be checked
function cwscsGetCategoriesChecked() {
	$cats = cwscsGetCategories();
	$myvals = cwscsGetSettingByKey("categories");
	$data = array();
	if (is_array($cats)) {
		$mycats_array = array();
		if ($myvals['data'] != "") { // some saved categories
			$mycats = $myvals['data'];
			if ($mycats != "") {
				$mycats_array = explode("::", $mycats);
				foreach ($mycats_array as $i => $v)	
					$mycats_array[$i] = trim($v);
			}
		}
		$ctr_d = 0;
		foreach ($cats as $i => $cat) {
			$data[$cat->term_id]['name'] = $cat->name;
			// should it be checked?
			if (count($mycats_array) == 0 || in_array($cat->term_id, $mycats_array))
				$data[$cat->term_id]['checked'] = true;
			else
				$data[$cat->term_id]['checked'] = false;
		}
	}
	return $data;
}

// Get the store policy and save to data
function cwscsGetStorePolicy() {
	$myPicks = cwscsGetSettingByKeyReturnArray("store-policy");
	if (!isset($myPicks[0]) || $myPicks[0] == 1) {
		$results[0] = 1;
	} else
		$results[0] = 0;
	// now the text
	if (isset($myPicks[1]) && $myPicks[1] != "") {
		$results[1] = $myPicks[1];
	} else
		$results[1] = "Use this form to submit your items to the consignment store. If they are in good shape, clean and generally ready to sell then we will approve the item for the store, and split the proceeds of any sale 50/50.\r\n\r\nIf we do accept your item for the store, we will email you to let you know, and to determine a time for you to drop your item off.\r\n\r\nIf after 6 months in the store, the item has not sold, we may donate the item or let you know to come pick it up.";
	return $results;
}

// get all splits and then check saved settings to determine which should be checked
function cwscsGetStoreSplitsChecked() {
	$mySplits = cwscsGetSettingByKeyReturnArray("store-splits");
	$allSplits = cwscsGetAllSplits();
	$data = array();	
	if (is_array($allSplits)) {
		foreach ($allSplits as $i => $split) {
			$data[$i]['name'] = $split;
			// should it be checked?
			if (count($mySplits) == 0 || in_array($i, $mySplits)) {
				$data[$i]['checked'] = true;
			} else {
				$data[$i]['checked'] = false;
			}
		}
	}
	return $data;
}

// get the recaptcha site key and secret -- called from ajax fcn to save settings
function cwscsGetRecaptchas($version) {
	$myData = cwscsGetSettingByKeyReturnArray($version);
	$data = array('version' => $version);
	if (is_array($myData)) {
		if (isset($myData[0]))
			$data['site_key'] = $myData[0];
		else
			$data['site_key'] = "";
		if (isset($myData[1]))
			$data['secret'] = $myData[1];
		else
			$data['secret'] = "";
	}
	return $data;
}

// get the email settings
function cwscsGetEmailSettings() {
	$myData = cwscsGetSettingByKeyReturnArray("emails");
	$data = array(0 => "", 1=>"");
	if (is_array($myData)) {
		if (isset($myData[0]))
			$data[0] = $myData[0];
		else
			$data[0] = "";
		if (isset($myData[1]))
			$data[1] = $myData[1];
		else
			$data[1] = "";
	}
	return $data;
}
//////////////////////////////////
// Woo FUNCTIONS
//////////////////////////////////

// fetch all woo data based on SKU
function cwscsGetWooBySkuAdmin($sku) {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$results = array();
	$wpdb->hide_errors();
	// get post id
	$sku = sanitize_text_field($sku);
	$pms = $wpdb->get_results( 'SELECT post_id FROM '.$prefix.'postmeta WHERE meta_key="_sku" AND meta_value="'.$sku.'"' ); 
	$post_id = 0;
	if (is_object($pms) || is_array($pms)) {
		foreach ($pms as $i => $pm) {
			$post_id = $pm->post_id;
		}
	} else {
		$results['status'] = 0;
		$results['msg'] = 'No item in store for sku '.esc_html($sku).' Error is '.$wpdb->last_error;
	}
	if (isset($post_id) && $post_id > 0) { // keep searching for info
		$pms = $wpdb->get_results( 'SELECT meta_key, meta_value FROM '.$prefix.'postmeta WHERE post_id='.$post_id.' AND meta_key IN ("_stock_status", "total_sales","_price", "_regular_price")' ); 
		if (is_object($pms) || is_array($pms)) {
			// fetch data
			$results = array("status"=>1, "msg"=>"", "post_id"=>$post_id);
			foreach ($pms as $i => $pm) {
				// organize woo data
				if ($pm->meta_key == "_price")
					$results['woo_price'] = $pm->meta_value;
				elseif ($pm->meta_key == "_stock_status")
					$results['woo_stock'] = $pm->meta_value;
				elseif ($pm->meta_key == "total_sales")
					$results['woo_sales'] = $pm->meta_value;	
				elseif ($pm->meta_key == "_regular_price")
					$results['woo_regprice'] = $pm->meta_value;
			}
		} else {
			$results['status'] = 0;
			$results['msg'] = 'No details in store for sku. Error is '.$wpdb->last_error;
		}
	}
	return $results;
}

// WooCommerce - add product
function cwscsAddItemToWCadmin($post, $status) {
	// get the item from the inventory table and use that info to add to WC
	$item = cwscsGetInventoryByID(sanitize_text_field($post['item_id']));
	if (isset($item->item_desc)) {
		$desc = sanitize_text_field($item->item_desc);
		if (isset($item->item_size) && $item->item_size != "") {
			if (stristr("ize", $item->item_size))
				$desc .= "\r\n".sanitize_text_field($item->item_size);
			else	
				$desc .= "\r\nSize: ".sanitize_text_field($item->item_size);
		}
		if (isset($item->item_colour) && $item->item_colour != "")
			$desc .= "\r\n".sanitize_text_field($item->item_colour);	
		if (isset($item->item_state) && $item->item_state != "")
			$desc .= "\r\nState of item: ".sanitize_text_field($item->item_state);
		$options = array(
			'post_title' => sanitize_text_field($item->item_title),
			"post_type" => "product", 
			"post_status" => $status, 
			'post_content' => $desc,
			'post_excerpt' => $desc
		);
		$post_id = wp_insert_post($options);
	}
	if (isset($post_id) && $post_id > 0) { // inserted
		// set product is simple/variable/grouped
		wp_set_object_terms( $post_id, 'simple', 'product_type' );
		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', 'instock');
		update_post_meta( $post_id, '_tax_status', 'taxable');
		update_post_meta( $post_id, 'total_sales', '0' );
		update_post_meta( $post_id, '_downloadable', 'no' );
		update_post_meta( $post_id, '_virtual', 'no' );
		update_post_meta( $post_id, '_regular_price', $item->item_retail);
		update_post_meta( $post_id, '_sale_price', $item->item_sale);
		update_post_meta( $post_id, '_purchase_note', '' );
		update_post_meta( $post_id, '_featured', 'no' );
		update_post_meta( $post_id, '_weight', '' );
		update_post_meta( $post_id, '_length', '' );
		update_post_meta( $post_id, '_width', '' );
		update_post_meta( $post_id, '_height', '' );
		update_post_meta( $post_id, '_sku', sanitize_text_field($post['sku']));
		update_post_meta( $post_id, '_product_attributes', array() );
		update_post_meta( $post_id, '_sale_price_dates_from', '' );
		update_post_meta( $post_id, '_sale_price_dates_to', '' );
		update_post_meta( $post_id, '_price', $item->item_sale );
		update_post_meta( $post_id, '_sold_individually', '' );
		update_post_meta( $post_id, '_manage_stock', 'yes' );
		wc_update_product_stock($post_id, 1, 'set');
		update_post_meta( $post_id, '_backorders', 'no' );
		//update_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', '2' ); // shop and search results
		// update_post_meta( $post_id, '_stock', $post['qty'] );
		// add category to product
		$product = wc_get_product($post_id);
		$product->set_category_ids(array(intval($item->item_cat)));
		$product->save();
		
		// add the feature image
		$attachments = array();
		if (isset($post['item_image1']) && $post['item_image1'] > 0)
			$attachments[] = sanitize_text_field($post['item_image1']);
		if (isset($post['item_image2']) && $post['item_image2'] > 0)
			$attachments[] = sanitize_text_field($post['item_image2']);
		if (isset($post['item_image3']) && $post['item_image3'] > 0)
			$attachments[] = sanitize_text_field($post['item_image3']);
		if (isset($post['item_image4']) && $post['item_image4'] > 0)
			$attachments[] = sanitize_text_field($post['item_image4']);
		if (isset($attachments[0]) && $attachments[0] > 0)
			set_post_thumbnail( $post_id, $attachments[0] );
		// now add to the product gallery if more than 1 image
		if (count($attachments) > 1) {
			$attach_id_str = get_post_meta($post_id,'_product_image_gallery', true);
			if (!isset($attach_id_str) || $attach_id_str == "")
				$conn = "";
			else
				$conn = ",";
			foreach ($attachments as $i => $att) {
				if ($i > 0 && $att > 0) {
					$attach_id_str .= $conn.$att;
					$conn = ",";
				}
			}
			if (isset($attach_id_str) && $attach_id_str != "") {
				$meta_key = update_post_meta($post_id, '_product_image_gallery', $attach_id_str);
			}
		} // more than 1 image to add
		
	}
	return $post_id;
}
