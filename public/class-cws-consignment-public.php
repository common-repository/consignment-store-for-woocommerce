<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://charlenesweb.ca
 * @since      1.0.0
 *
 * @package    CWS_Consignment
 * @subpackage CWS_Consignment/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    CWS_Consignment
 * @subpackage CWS_Consignment/public
 * @author     Charlene Copeland <charlene@charlenesweb.ca>
 */
class cws_consignment_Public {

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
	 * @param      string    $cws_consignment       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $cws_consignment, $version ) {
		$this->plugin_name = $cws_consignment;
		$this->version = $version;
		add_action( 'init', array( $this, 'init_shortcodes' ), 20 ); // run on priority 20 as Shortcodes are registered at priority 10
	}
	
	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cws-consignment-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
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

		wp_enqueue_script($this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cws-consignment-public.js', array( 'jquery' ), $this->version, false );
		
		// do we need the reCaptcha scripts? Check for keys. Do v2 then v3
		$myRecaptcha = cwscsGetSettingByKeyReturnArray("recaptcha-v2");
		if (isset($myRecaptcha) && is_array($myRecaptcha) && isset($myRecaptcha[0]) && isset($myRecaptcha[1]) && $myRecaptcha[0] != "" && $myRecaptcha[1] != "") {
			wp_register_script(
				'cwscs-google-recaptchav2',
				'https://www.google.com/recaptcha/api.js',
				array(),
				$this->version,
				true
			);
			wp_enqueue_script("cwscs-google-recaptchav2");
		} 
		/* no v3 for now
		else { // check for v3
			$myRecaptcha = cwscsGetSettingByKeyReturnArray("recaptcha-v3");
			if (isset($myRecaptcha) && is_array($myRecaptcha) && isset($myRecaptcha[0]) && isset($myRecaptcha[1]) && $myRecaptcha[0] != "" && $myRecaptcha[1] != "") {
				wp_register_script(
					'cwscs-google-recaptchav3',
					'https://www.google.com/recaptcha/api.js',
					array(),
					$this->version,
					true
				);
				wp_enqueue_script('cwscs-google-recaptchav3', "https://www.google.com/recaptcha/api.js?render=".$myRecaptcha[0]."&ver=".$this->version);
			}
		}
		*/
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
	
	
	/**
	 * Register public Shortcodes
	 *
	 * @since 1.0.0
	 */
	public function init_shortcodes() {
		add_shortcode( 'additemform', array($this, 'additemform_func') );
		add_shortcode( 'cwscs_testapi', array($this, 'cwscs_testapi_func') );
		add_action( 'wp_ajax_cwscs_ajax_add_item', array( $this, 'cwscs_ajax_add_item' ), 20 );
		add_action( 'wp_ajax_nopriv_cwscs_ajax_add_item', array( $this, 'cwscs_ajax_add_item' ), 20 );
	}
	 
	/**
	 * Handles my AJAX request.
	 */
	public function cwscs_ajax_add_item() {
		// check referrer
		check_ajax_referer( 'cwscs_doajax', 'security' );
		// get post vars
		// SWITCH ON ACTION, get cat prices if get_cat_prices
		if (!isset($_POST['thistask'])) {
			$thistask = "None";
			$results = "No task passed";
			$status = 0;
		} else
			$thistask = sanitize_text_field($_POST['thistask']); //what shall we do
			
		if ($thistask == "getcatprices") {
			$thiscat = sanitize_text_field($_POST['thiscat']); // may be blank
			$status = 1;
			$ct = "";
			// get store categories
			$cats = cwscsGetCategories();
			if (is_array($cats) || is_object($cats)) {
				$found = false;
				if (isset($thiscat) && $thiscat > 0) {
					$select_cats = array();
					foreach ($cats as $i => $cat) {
						if ($cat->term_id == $thiscat) {
							$select_cats[] = $cat;
							$found = true;
						}
					}
				}
				if (!$found)
					$select_cats = $cats;
				$results = cwscsGetPricesByCategory($select_cats);
			}
			if (!isset($results[0]['total_items'])) {
				$status = 0;
			}
			if ($status == 1) {
				// any items?
				$found = false;
				foreach($results as $i => $arr) {
					if ($arr['total_items'] > 0) {
						$found = true;
					}
				}
				if (!$found)
					$status = -1;
			}
		} // END get_cat_prices
		elseif ($thistask == "uploadimage") {
			$status = 1;
			$results = cwscs_uploadImg();
			if ($results['status'] == 0)
				$status = 0;
		} else {
			$status = 0;
			$results = "No action";
		}
		$results = array("status"=>$status, "data"=>$results);
		wp_send_json($results);
		wp_die(); // All ajax handlers die when finished
	}
	/**
	 * SHORTCODE FUNCTIONS
	 * Show the add item form for admin and sellers - [additemform]
	 * @since 1.0.0
	 */
  	public function additemform_func() {
		global $wp;
		$ct = "";
		$current_url  = home_url( $wp->request );
		$subscriber = false; $editor = false; $loggedin = false; $admin = false; $author = false;
		$msg = "";
		$warn = "";
		$upload_dir_paths = wp_upload_dir();
		$baseurl = $upload_dir_paths['baseurl'];
		$basedir = $upload_dir_paths['basedir'];
		$name = "";
		$email = "";
		// get some info if they are logged in
		if ( is_user_logged_in() ) {
			// get roles
			$loggedin = true;
			global $current_user;
			wp_get_current_user();
			$user_id = $current_user->ID;
			$roles = $current_user->roles;
			$name = $current_user->display_name;  // for the form
			$email = $current_user->user_email;
			if (in_array("administrator", $roles)) {
				$admin = true;
			} elseif (in_array("subscriber", $roles))
				$subscriber = true;
			elseif (in_array("editor", $roles))
				$editor = true;
			elseif (in_array("author", $roles))
				$author = true;
			elseif (in_array("customer", $roles))
				$customer = true;
		} // END is logged in
		
		// get recaptcha settings - do here since need if submitted
		$recaptcha = cwscsGetMyRecaptcha();
		$max_upload_size = wp_max_upload_size();
		$displayMaxSize = $max_upload_size/1000000;
		// Was additem form submitted?
		if (isset($_POST['additem'])) {
			// validate the form before doing anything
			$ok = true;
			if (isset($recaptcha) && isset($recaptcha['version']) && $recaptcha['version'] == "v2") {
				if (!isset($_POST['g-recaptcha-response'])) {
					$results = array('status'=>0, 'error'=>'Please check captcha checkbox. ');
					$ok = false;
				} else
					$secret = $recaptcha['secret'];
			} else
				$secret = ""; // not in play
			// check sku if admin
			if ($admin && (!isset($_POST['sku']) || $_POST['sku'] == "")) {
				$results = array('status'=>0, 'error'=>'Please enter a unique sku for this product. ');
				$ok = false;
			} else
				$results = cwscsValidateAddItem($secret);
				$attachments = array();	
			if ($results['status'] == 0) {
				$ct .= '<p class="failmsg">'.esc_html($results['error']).'</p>';
			} else {
				// First add to inventory table and return insert id
				if ($msg == "" && $results['status'] == 1)
					$insert_id = cwscsAddItem($_POST, $attachments);
				else {
					if ($results['error'] != "")
						$msg .= '<p class="failmsg">'.esc_html($results['error']).'</p>';
					$insert_id = -1;
				}
			
				
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				
				$allowed = array("image/jpeg", "image/png", "image/x-png", "image/pjpeg");
				$allowedExt = array("gif", "jpeg", "png", "jpg");
				for ($i=1; $i<=4; $i++) {
					$status = 1;
					$imagename = "image".$i;
					if (isset($_FILES[$imagename]) && $_FILES[$imagename]['size'] > 0 && $_FILES[$imagename]['error'] === UPLOAD_ERR_OK) {
						// first check on filetype
						$type = sanitize_text_field($_FILES[$imagename]['type']);
						$mime = wp_get_image_mime($_FILES[$imagename]["tmp_name"]);
						$fileInfo = @getimagesize($_FILES[$imagename]['tmp_name']);
						if ($_FILES[$imagename]['name'] != "" && in_array($type, $allowed) && in_array($mime, $allowed) && in_array($fileInfo['mime'], $allowed) && $fileInfo[0] > 0) {
							$size = sanitize_text_field($_FILES[$imagename]['size']);
							if ($_FILES[$imagename]['size'] > $max_upload_size) {
								$msg .= '<p class="failmsg">Image is too big! Can accept images that are bigger than '.esc_html($max_upload_size).'. This one is '.esc_html($size).' bytes.</p>';
								$status = 0;
							}
							if ($status == 1) {
								$attachment_id = media_handle_upload($imagename, 0);
								if (!isset( $attachment_id) || $attachment_id == 0) {
									$msg .= '<p class="failmsg">There was an error adding image.</p>';
								} else {
									$attachments[] = $attachment_id;
									$msg .= cwscsRemoveExtraImages($attachment_id);
								}
							}
						} else {
							$msg .= '<p class="failmsg">Image submitted was not an image file.</p>';
						}
					} // END there was an image
				} // END loop on 4 images
						
				if ($insert_id >= 0) { // fail so show msg and show form
					if (!$admin) {
						$msg .= '<p class="successmsg">Your item has been submitted. Once your item has been reviewed, we will be in touch! You can scroll down to add another item. <br />Please don&rsquo;t refresh! That will resubmit your item. ';
					}
					$msg  .= '</p>';
					if ($admin) {
						// added to pending inventory successfully. Now add to woocommerce
						$result = cwscsAddItemToWC($_POST, $attachments, "publish");
						if ($result > 0) {
							$msg .= '<p class="successmsg">Your item '.$_POST['sku'].' has been saved to the store. You can scroll down to add another item. Please don&rsquo;t refresh! That will resubmit your item.</p>';
						} else {
							$msg .= '<p class="failmsg">Your item '.$_POST['sku'].' was not saved to the store. There was an error: '.$result.'</p>';
						}
					}
				} // END was added
			
				// If item was successfully added and the user is not an administrator then send the notification email to the email in settings
				if (!$admin && $insert_id >= 0) {
					$email_settings = cwscsGetMyEmails();
					if (is_array($email_settings) && count($email_settings) == 2 && $email_settings[1] != "") {
						$from = sanitize_email($email_settings[0]);
						$to = sanitize_email($email_settings[1]);
						$item_retail = sanitize_text_field($_POST['item_retail']);
						$item_sale = sanitize_text_field($_POST['item_sale']);
						
						$subject = 'Someone has submitted an item in the store!';
						$body = "Title: ".sanitize_text_field($_POST['item_title'])."\r\n"."Description: ".sanitize_textarea_field($_POST['item_desc'])."\r\nRetail Price: $".number_format($item_retail,2)."\r\nStore Price: $".number_format($item_sale,2)."\r\nSize: ".sanitize_text_field($_POST['item_size'])."\r\nColour: ".sanitize_text_field($_POST['item_colour'])."\r\nState of Item: ".sanitize_text_field($_POST['item_state'])."\r\nPhone: ".sanitize_text_field($_POST['phone'])."\r\nEmail: ".sanitize_email($_POST['email'])."\r\nAccepted Policy? ";
						if (isset($_POST['policy_accepted']) && $_POST['policy_accepted'] == 1)
							$body .= 'Yes';
						elseif (isset($_POST['policy_accepted']) && $_POST['policy_accepted'] == 2)
							$body .= 'Not Shown';
						else
							$body .= 'No';
						$body .= "\r\n\r\nReview this and all submitted items in the CWS Consignment Store plugin \r\n";
						$headers = array();
						$headers[] = 'From: '.$from;
						$sent = wp_mail($to, $subject, $body, $headers);
					}
				} // END send email

				// Show message and button to add another item, maybe show summary of items?
				$ct .= $msg; // must be formatted as good or bad
				if ($insert_id >= 0) { // success and not staff so summary and form
					$ct .= cwscsShowItemSummary(); // TO DO
				}
			} // passed validation
		} // END form was submitted

		$cats = cwscsGetMyCategories();
		$splits = cwscsGetMySplits();
		$policy = cwscsGetMyPolicy();
		
		unset($_POST); // prevent double submission
		$ct .= '<br />
		<div class="additemform">';
		// SHOW regular additem form
		$ct .= '<br />
		<form action="'.esc_html($current_url).'" method="post" enctype="multipart/form-data" class="cwscs_form" id="cwscs_formadditem" >';
				if ($admin) {
					// enter sku if staff
					$ct .= '
					<p id="p-sku">
						<label for "sku">Enter Unique SKU for Item</label>
						<input type="text" id="sku" name="sku" maxlength=8 placeholder="" value="" required/> </p>';
				}
				$ct .= '
				<p id="p-item_title">
					<label for "item_title">Item Title</label>
					<input type="text" id="item_title" name="item_title" required autofocus />
				</p>
				<p id="p-item_cat">
					<label for "item_cat">Select a Category</label>
					<select id="item_cat" name="item_cat" required>
						<option value="">Choose &hellip;</option>';
						foreach ($cats as $i => $obj) {
							$ct .= '<option value="'.esc_html($obj->term_id).'">'.esc_html($obj->name).'</option>';
						}
						$ct .= '
					</select>
				</p>
				<p id="p-item_desc">
					<label for "item_desc">Description</label>
					<textarea id="item_desc" name="item_desc" maxlength=61000 data-minlength=10></textarea>
				</p>';
				$ct .= '<input type="hidden" id="item_tags" name="item_tags" value="" />';
				$ct .= '
				<p id="p-item_retail">
					<label for "item_retail">Retail Price <span>How much does this item sell for in the store, brand new? </span></label>
					<input type="text" id="item_retail" name="item_retail" maxlength=8 placeholder="$" required />
				</p>
				<p id="pshowcatprices" style="display:none;"><a href="javascript:void(0);" data-divid="catprices" class="toggledivbyid showcatprices"><span class="dashicons dashicons-visibility"></span> View average sale prices in the store to help you set a price.</a></p>
				<div id="catprices" class="cwshidden"></div>
				<p id="p-item_sale">
					<label for "item_sale">Sale Price 
						<span>How much should it sell for in the store? Note on average you will receive 1/2 half of this amount if the item sells. </span>
					</label>
					<input type="text" id="item_sale" name="item_sale" maxlength=8 placeholder="$" required />
				</p>
				<p id="p-item_size">
					<label for "item_size">Size if applicable</label>
					<input type="text" id="item_size" name="item_size" maxlength=255 placeholder="" />
				</p>
				<p id="p-item_colour">
					<label for "item_colour">Colour if applicable</label>
					<input type="text" id="item_colour" name="item_colour" maxlength=100 placeholder="" />
				</p>
				<p id="p-item_state">
					<label for "item_state">What is the State of the Item?</label>
					<label class="radio" for="state_new">
						<input type="radio" name="item_state" required id="state_new" value="like new" /> Like new (barely used)
					</label>&nbsp;&nbsp;
					
					<label class="radio" for="state_gentle">
						<input type="radio" name="item_state" required id="state_gentle" value="gently used" /> Gently used
					</label>&nbsp;&nbsp;
					<label class="radio" for="state_worn">
						<input type="radio" name="item_state" required id="state_worn" value="worn" /> Pretty worn
					</label>
				</p>
				<p id="p-item_images">
					<label for "item_images">Add Up To 4 Images <span>Include pictures with different angles and details. Your images should be at least 300px wide or tall, and no more than '.number_format($displayMaxSize,1).' MB in size. </span></label>
					<input type="file" id="image1" name="image1" accept="image/*" /><br />
					<input type="file" id="image2" name="image2" accept="image/*" /><br />
					<input type="file" id="image3" name="image3" accept="image/*" /><br />
					<input type="file" id="image4" name="image4" accept="image/*" /><br />
				</p>
				<p id="p-seller_name">';
					if ($admin)
						$ct .= '<label for "seller_name">What Is The Seller&rsquo;s Name?</label>';
					else
						$ct .= '<label for "seller_name">What Is Your Name?</label>';
					$ct .= '
					<input type="text" id="seller_name" name="seller_name" maxlength=150 placeholder="" value="'.$name.'" required />
				</p>
				<p id="p-phone">';
				if ($admin)
					$ct .= '<label for "phone">What Is the Seller&rsquo;s Phone Number?</label>';
				else
					$ct .= '<label for "phone">What Is Your Phone Number?</label>';
				$ct .= '
					<input type="text" id="phone" name="phone" maxlength=14 placeholder="" />
				</p>
				<p id="p-email">';
				if ($admin)
					$ct .= '<label for "email">What Is the Seller&rsquo;s Email?</label>';
				else
					$ct .= '<label for "email">What Is Your Email?</label>';
				$ct .= '
					<input type="text" id="email" name="email" maxlength=255 placeholder="" ';
					if ($email != "")
						$ct .= 'value="'.esc_html($email).'" ';
					else
						$ct .= 'value=""';
					$ct .= '" required />
				</p>';
				$split = 50; // default
				if ($admin) {
					// show store split if the user is an administrator
					
					$ct .= '
					<p id="p-store_split">
						<label for "store_split">Review Store Split</label>
						<select id="store_split" name="store_split" required>';
						foreach ($splits as $i => $s) {
							$ct .= '
							<option value='.esc_html($i);
							if ($split == $i)
								$ct .= ' selected="selected" ';
							$ct .= '>'.esc_html($s).'</option>';
						}
						$ct .= '
						</select>
					</p>';
				} else {
					$ct .= '
					<input type="hidden" id="sku" name="sku" value="" />
					<input type="hidden" id="store_split" name="store_split" value=50 />';
				}
				if ($policy[0] == 1) {
					$ct .= '
					<p><strong>Review the Store Policy on selling items in the our consignment store.</strong></p>
					<div id="policy">'.esc_html($policy[1]).'</div>
					<p id="p-policy_accepted">
						<label for "policy_accepted">Please indicate your acceptance of the store policy. </label>
						<label class="radio" for="policy_accepted">
							<input type="checkbox" name="policy_accepted" required id="policy_accepted" value=1 ';
							if ($admin) {
								$ct .= ' checked="checked" ';
							}
							$ct .= '/> I accept
						</label>
					</p>';
				} // END yes show the policy
				else {
					// save that policy was not shown
					$ct .= '<input type="hidden" name="policy_accepted" id="policy_accepted" value=2 >';
				}
				// recaptcha? save a hidden field if so to help with processing
				$isRc3 = false;
				$disabled = "";
				if (isset($recaptcha) && isset($recaptcha['version'])) {
					if ($recaptcha['version'] == "v2") {
						$ct .= '
						<input type="hidden" name="rc2" id="rc2" value="'.esc_html($recaptcha['site_key']).'" >
						<div class="clear">&nbsp;</div>
						<div class="g-recaptcha" data-sitekey="'.esc_attr($recaptcha['site_key']).'"  data-callback="cc_enableSubmitBtn">></div>
      <br/>';
	  					$disabled = 'disabled="disabled" ';
					} 
					/* no recaptcha v3 for now
					elseif ($recaptcha['version'] == "v3") {
						$ct .= '<input type="hidden" name="rc3" id="rc3" value="'.$recaptcha['site_key'].'" >';
						$isRc3 = true;
					}*/
				}
				$ct .= '
				<p id="cwscs_errormsg" class="failmsg cwshidden"></p>
				<button type="submit" name="additem" id="cc_additem" class="single_add_to_cart_button button" '.esc_html($disabled).'>Add Item</button>'; 
				if ($disabled != "")
					$ct .= '<p>The Add Item button is disabled until the form is complete and the "I am not a robot checkbox is clicked". </p>';
			$ct .= '	
			</form>		
		</div> <!-- END .additemform -->';

		return $ct;
	}
	
	public function cwscs_testapi_func() {
		global $wp;
		$ct = "";
		if (is_ssl())
			$http = 'https';
		else	
			$http = 'http';
		$current_url  = set_url_scheme($http.'://'.$_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_URL'] );
		$subscriber = false; $editor = false; $loggedin = false; $admin = false; $author = false;
		$msg = "";
		$warn = "";
		$name = "";
		$email = "";
		// get some info if they are logged in
		if ( is_user_logged_in() ) {
			// get roles
			$loggedin = true;
			global $current_user;
			wp_get_current_user();
			$user_id = $current_user->ID;
			$roles = $current_user->roles;
			$name = $current_user->display_name;  // for the form
			$email = $current_user->user_email;
			if (in_array("administrator", $roles)) {
				$admin = true;
			} elseif (in_array("subscriber", $roles))
				$subscriber = true;
			elseif (in_array("editor", $roles))
				$editor = true;
			elseif (in_array("author", $roles))
				$author = true;
			elseif (in_array("customer", $roles))
				$customer = true;
		}
		$request = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		// Set one or more request query parameters
		//$request->set_param( 'per_page', 20 );
		//$request->set_param( '_envelope', 1 );
		$response = rest_do_request( $request );
		
		$ct = '<h3>After call.</h3>';

		if ( $response->is_error() ) {
			// Convert to a WP_Error object.
			$error = $response->as_error();
			$message = $response->get_error_message();
			$error_data = $response->get_error_data();
			$status = isset( $error_data['status'] ) ? $error_data['status'] : 500;
			wp_die( printf( '<p>An error occurred: %s (%d)</p>', $message, $error_data ) );
		}
		 
		$data = $response->get_data();
		$headers = $response->get_headers();
		echo "<p>Success! Here's the data:</p>";
		var_dump( $data );

		return $ct;
	}
	
} // END class cws_consignment_Public
//////////////////////////////////////
// Add Item Form functions
/////////////////////////////////////
// get the consignment store categories from WooCommerce -- would be good to set these in the plugin but ok for now
// in terms, term_id=17, name=Boots
// term_meta where term_id=17, see thumbnail_id, product_count, order and display_type
// term_taxonomy where term_id=17, get term_taxonomy_id and taxonomy=product_cat
function cwscsGetCategories() {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$results = array();
	$wpdb->hide_errors();
	$cats = $wpdb->get_results( 'SELECT '.$prefix.'term_taxonomy.term_id, name FROM '.$prefix.'term_taxonomy, '.$prefix.'terms WHERE '.$prefix.'term_taxonomy.term_id='.$prefix.'terms.term_id AND taxonomy="product_cat" order by '.$prefix.'terms.name' ); 
	if (is_object($cats) || is_array($cats)) {
		foreach ($cats as $i => $obj) {
			$results[] = $obj;
		}
	}
	return $results;
}
// get categories from settings OR, if none set, return all categories. Returns as array of objects
function cwscsGetMyCategories() {
	$results = array();
	$myCats = cwscsGetSettingByKeyReturnArray("categories");
	$allCats = cwscsGetCategories();
	if (count($myCats) == 0) { // have not selected any cats so return all
		$results = $allCats;
	} else {
		foreach ($allCats as $i => $obj) {
			if (in_array($obj->term_id, $myCats))
				$results[] = $obj;
		}
	}
	return $results;
}
// query store -- get total_items, lowest price, highest price & average by category
function cwscsGetPricesByCategory($cats) {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$results = array();
	$wpdb->hide_errors();
	$ctr_r = 0;
	foreach ($cats as $i => $cat) {
		if (isset($cat->term_id) && $cat->term_id > 0) {
			// get all post_ids for the products in this cat from term_relationships 
			$allprods = $wpdb->get_results( 'SELECT object_id FROM '.$prefix.'term_relationships WHERE term_taxonomy_id="'.esc_html($cat->term_id).'"' ); 
			if ((is_array($allprods) || is_object($allprods)) && count($allprods) > 0) {
				$str = "(";
				$conn = '';
				$total = count($allprods);
				
				foreach ($allprods as $j => $prod) {
					$str .= $conn.$prod->object_id;
					$conn = ',';
				}
				$str .= ')';
				// get lowest price -- metavalue is character so must convert to numeric and then sort
				$values = $wpdb->get_results( 'SELECT meta_value FROM '.$prefix.'posts as a, '.$prefix.'postmeta as b WHERE a.ID=b.post_id AND a.post_type="product" AND a.post_status="publish" AND b.post_id IN '.$str.' AND b.meta_key ="_price" AND b.meta_value IS NOT NULL AND b.meta_value!="" ORDER BY b.meta_value ASC' );
				if ((is_array($values) || is_object($values)) && count($values) > 0) {
					$results[$ctr_r]['term_id'] = esc_html($cat->term_id);
					$results[$ctr_r]['name'] = esc_html($cat->name);
					$nums = array();
					$total = count($values);
					$amt = 0;
					foreach ($values as $i => $val) {
						$nums[] = $val->meta_value * 1;
						$amt += $val->meta_value * 1;
					}
					sort($nums);
					$results[$ctr_r]['lowest'] = number_format($nums[0],2);
					$len = count($nums) - 1;
					$results[$ctr_r]['highest'] = number_format($nums[$len],2);
					$results[$ctr_r]['total_items'] = $total;

					if ($amt > 0 && $total > 0)
						$results[$ctr_r]['average'] = number_format($amt/$total,2);
					else
						$results[$ctr_r]['average'] = 0;
				} // END got prices
			} // END got products for cat
			else {
				$results[$ctr_r]['total_items'] = 0;
				$results[$ctr_r]['lowest'] = 0; 
				$results[$ctr_r]['highest'] = 0; 
				$results[$ctr_r]['average'] = 0; 
			}
			$ctr_r++;
		} // END there is a cat id
	} // END loop on cats
	return $results;
}
// get aplits from settings OR, if none set, return all splits. Returns as array
function cwscsGetMySplits() {
	$results = array();
	$mySplits = cwscsGetSettingByKeyReturnArray("store-splits");
	$allSplits = cwscsGetAllSplits();
	$data = array();	
	if (is_array($allSplits)) {
		foreach ($allSplits as $i => $split) {
			// should it be included
			if (count($mySplits) == 0 || in_array($i, $mySplits)) {
				$results[$i] = $split;
			}
		}
	}
	return $results;
}

// Get the store policy settings
function cwscsGetMyPolicy() {
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
function cwscsGetMyEmails() {
	$myPicks = cwscsGetSettingByKeyReturnArray("emails");
	$results = array(0=>"", 1=>"");
	// if no "to" set then don't send emails
	if (isset($myPicks[1]) && $myPicks[1] != "") {
		// did they set a from or do we need to use the system email?
		if (isset($myPicks[0]) && $myPicks[0] != "") {
			$results[0] = $myPicks[0];
		} else {
			$results[0] = get_option("admin_email");		
		}
		if ($results[0] != "")
			$results[1] = $myPicks[1]; // to
	}
	return $results;
}
// Get recaptcha settings, if any
function cwscsGetMyRecaptcha() {
	$results = array();
	
	$myRecaptcha = cwscsGetSettingByKeyReturnArray("recaptcha-v2");
	if (isset($myRecaptcha) && is_array($myRecaptcha) && isset($myRecaptcha[0]) && isset($myRecaptcha[1]) && $myRecaptcha[0] != "" && $myRecaptcha[1] != "") {
		$results = array("version"=>"v2", "site_key"=>$myRecaptcha[0], "secret"=>$myRecaptcha[1]);
	} else { // check for v3
		$myRecaptcha = cwscsGetSettingByKeyReturnArray("recaptcha-v3");
		if (isset($myRecaptcha) && is_array($myRecaptcha) && isset($myRecaptcha[0]) && isset($myRecaptcha[1]) && $myRecaptcha[0] != "" && $myRecaptcha[1] != "") {
			$results = array("version"=>"v3", "site_key"=>$myRecaptcha[0], "secret"=>$myRecaptcha[1]);
		}
	}
	return $results;
}
// validate the additem form including recaptcha
function cwscsValidateAddItem($secret) {
	$status = 1;
	$error = "";
	
	$required = array('item_title'=>'Item Title', 'item_cat'=>'Category', 'item_retail'=>'Retail Price', 'item_sale'=>'Sale Price', 'seller_name'=>'Seller Name', 'email'=>'Email');
	foreach ($required as $key => $n) {
		if (!isset($_POST[$key]) || $_POST[$key] == "") {
			$error .= 'Please enter '.esc_html($n).'. ';
			$status = 0;
		}
	} // END loop on $required
	// check email is an email
	if (!is_email($_POST['email'])) {
		$error .= 'Enter a valid email. ';
		$status = 0;
	}
	if ($status == 1 && $secret != "") { // validate recaptcha if in play
		if(!isset($_POST['g-recaptcha-response'])){
			$error .= 'Please check the the reCaptcha checkbox. ';
			$status = 0;
		} else {
			$captcha = sanitize_text_field($_POST['g-recaptcha-response']);
			$ip = $_SERVER['REMOTE_ADDR'];
			// post request to server
			$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secret) .  '&response=' . urlencode($captcha);
			//$response = file_get_contents($url);
			$response = wp_remote_get($url);
			$status = 0; // pessimist
			if ( is_array( $response ) && !is_wp_error( $response ) ) {
				$headers = $response['headers']; // array of http header lines
				$body =  wp_remote_retrieve_body( $response ) ; // array of http header lines
				$response_code =  wp_remote_retrieve_response_code( $response );
				if ($response_code == 200) {
					$responseBody = json_decode($body, true);
					if ($responseBody['success'] === true) {
						$status = 1;
					} elseif ($responseBody['error-codes'] && $responseBody['error-codes'][0] == "timeout-or-duplicate") {
						$error = "Your form has expired. Please fill in the form below and click Add Item. ";					
					} else {
						$error = 'You did not pass the anti-spam check and we cannot accept your submission. ;'; 
					}
				} else
					$error = 'We could not contact Google to validate the form. Please try again later. ';
			} elseif (!is_wp_error( $response )) {
				$error = 'We could not contact Google to validate the form. Please try again later. ';
			} else
				$error = 'We could not contact Google to validate the form. Please try again later. ';
        }
	}
	$results = array('status'=>$status, 'error'=>$error);
	return $results;
}
// Add an item to the store - may require approval. Or if added by admin, goes directly into woocommerce
function cwscsAddItem($post, $attachments) {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$att1 = 0; $att2 = 0; $att3 = 0; $att4 = 0;
	if (is_array($attachments) && count($attachments) > 0) {
		$att1 = $attachments[0];
		if (count($attachments) > 1)
			$att2 = $attachments[1];
		if (count($attachments) > 2)
			$att3 = $attachments[2];	
		if (count($attachments) > 3)
			$att4 = $attachments[3];
	}
	if ($post['sku'] != "")
		$approved = 1;
	else
		$approved = 0;
	
	$query = $wpdb->insert( 
		$prefix.'cwscs_inventory', 
			array( 
				'item_title' => sanitize_text_field($post['item_title']), 
				'item_cat' => sanitize_text_field($post['item_cat']), 
				'item_desc' => sanitize_textarea_field($post['item_desc']), 
				'item_tags' => sanitize_text_field($post['item_tags']), 
				'item_retail' => sanitize_text_field($post['item_retail']),
				'item_sale' => sanitize_text_field($post['item_sale']),
				'item_size' => sanitize_text_field($post['item_size']), 
				'item_colour' => sanitize_text_field($post['item_colour']), 
				'item_state' => sanitize_text_field($post['item_state']), 
				'seller_name' => sanitize_text_field($post['seller_name']), 
				'phone' => sanitize_text_field($post['phone']), 
				'email' => sanitize_email($post['email']), 
				'policy_accepted' => sanitize_text_field($post['policy_accepted']),
				'sku' => sanitize_text_field($post['sku']),
				'store_split' => sanitize_text_field($post['store_split']),
				'approved' => sanitize_text_field($approved),
				'item_image1' => sanitize_text_field($att1),
				'item_image2' => sanitize_text_field($att2),
				'item_image3' => sanitize_text_field($att3),
				'item_image4' => sanitize_text_field($att4),
				'date_added'=>current_time("Y-m-d")
			), 
			array( 
				'%s', '%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s'
			) 
	);
	$wpdb->print_error();
	if ($wpdb->insert_id < 0) {
		$result = -1;
		$tmp = 'Could not add inventory item for '.sanitize_text_field($post['seller_name']).', '.sanitize_email($post['email']).'. Error is '.sanitize_text_field($wpdb->last_error).'. ';
		$system = "public";
		$fcn = "cwscsAddItem";
		$file = "class-cws-consignment-public.php";
		$url = get_site_url();
		cwscsLogErrror($system, $file, $fcn, $url, $tmp);
	} else {
		$result = $wpdb->insert_id;
	}
	return $result;
}
//////////////////////////////////
// Image functions
//////////////////////////////////
function cwscsRemoveExtraImages($attachment_id) {
	$path = get_attached_file( $attachment_id );
	$meta = wp_generate_attachment_metadata( $attachment_id, $path );
	wp_update_attachment_metadata( $attachment_id, $meta );
	return "";
}
//////////////////////////////////
// WooCommerce FUNCTIONS
//////////////////////////////////

// WooCommerce - add product
function cwscsAddItemToWC($post, $attachments, $status) {
	$msg = "";
	// first check if SKU already used
	if (isset($post['sku']) || $post['sku'] > 0) {
		$woo = cwscsGetWooBySku($post['sku']);
		if (isset($woo['status']) && $woo['status'] == 1) {
			$msg = 'That SKU has already been used. Please select another. ';
			return $msg;
		}
	}
	// ok continue	
	$desc = sanitize_textarea_field($post['item_desc']);
	if (isset($post['item_size']) && $post['item_size'] != "") {
		if (stristr("ize", $post['item_size']))
			$desc .= "\r\n".sanitize_text_field($post['item_size']);
		else	
			$desc .= "\r\nSize: ".sanitize_text_field($post['item_size']);
	}
	if (isset($post['item_colour']) && $post['item_colour'] != "")
		$desc .= "\r\n".sanitize_text_field($post['item_colour']);
	if (isset($post['item_state']) && $post['item_state'] != "")
		$desc .= "\r\nState of item: ".sanitize_text_field($post['item_state']);
	$options = array(
		'post_title' => sanitize_text_field($post['item_title']),
		"post_type" => "product", 
		"post_status" => sanitize_text_field($status),
		'post_content' => $desc, // already sanitized
		'post_excerpt' => $desc
	);
	$post_id = wp_insert_post($options);
	if ($post_id > 0) { // inserted
		// set product is simple/variable/grouped
		wp_set_object_terms( $post_id, 'simple', 'product_type' );
		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', 'instock');
		update_post_meta( $post_id, '_tax_status', 'taxable');
		update_post_meta( $post_id, 'total_sales', '0' );
		update_post_meta( $post_id, '_downloadable', 'no' );
		update_post_meta( $post_id, '_virtual', 'no' );
		update_post_meta( $post_id, '_regular_price', sanitize_text_field($post['item_retail']));
		update_post_meta( $post_id, '_sale_price', sanitize_text_field($post['item_sale']));
		update_post_meta( $post_id, '_purchase_note', '' );
		update_post_meta( $post_id, '_featured', 'no' );
		update_post_meta( $post_id, '_weight', '' );
		update_post_meta( $post_id, '_length', '' );
		update_post_meta( $post_id, '_width', '' );
		update_post_meta( $post_id, '_height', '' );
		update_post_meta( $post_id, '_sku', sanitize_text_field($post['sku']) );
		update_post_meta( $post_id, '_product_attributes', array() );
		update_post_meta( $post_id, '_sale_price_dates_from', '' );
		update_post_meta( $post_id, '_sale_price_dates_to', '' );
		update_post_meta( $post_id, '_price', sanitize_text_field($post['item_sale']));
		update_post_meta( $post_id, '_sold_individually', '' );
		update_post_meta( $post_id, '_manage_stock', 'yes' );
		wc_update_product_stock($post_id, 1, 'set');
		update_post_meta( $post_id, '_backorders', 'no' );
		//update_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', '2' ); // shop and search results
		// update_post_meta( $post_id, '_stock', sanitize_text_field($post['qty']) );
		// add the feature image
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
		// add category to product
		if (isset($post['item_cat']) && $post['item_cat'] > 0) {
			wp_set_post_terms( $post_id, array(sanitize_text_field($post['item_cat'])), 'product_cat', true );
		}
	}
	return $post_id;
}
// fetch all woo data based on SKU
function cwscsGetWooBySku($sku) {
	global $wpdb;
	$prefix = $wpdb->prefix; 
	$results = array();
	$wpdb->hide_errors();
	// get post id
	$pms = $wpdb->get_results( 'SELECT post_id FROM '.$prefix.'postmeta WHERE meta_key="_sku" AND meta_value="'.sanitize_text_field($sku).'"' ); 
	$post_id = 0;
	if (is_object($pms) || is_array($pms)) {
		foreach ($pms as $i => $pm) {
			$post_id = $pm->post_id;
		}
	} else {
		$results['status'] = 0;
		$results['msg'] = 'No item in store for sku '.esc_html($sku).' Error is '.esc_html($wpdb->last_error);
	}
	if (isset($post_id) && $post_id > 0) { // keep searching for info
		$pms = $wpdb->get_results( 'SELECT meta_key, meta_value FROM '.$prefix.'postmeta WHERE post_id='.sanitize_text_field($post_id).' AND meta_key IN ("_stock_status", "total_sales","_price", "_regular_price")' ); 
		if (is_object($pms) || is_array($pms)) {
			// fetch data
			$results = array("status"=>1, "msg"=>"", "post_id"=>$post_id);
			$results['data'] = array();
			foreach ($pms as $i => $obj) {
				$results['data'][] = $obj;
			}
		} else {
			$results['status'] = 0;
			$results['msg'] = 'No details in store for sku '.esc_html($sku).', post id: '.esc_html($post_id).'. ';
		}
	}
	return $results;
}

//////////////////////////////////
// Helper FUNCTIONS
//////////////////////////////////
// Display the item back
function cwscsShowItemSummary() {
	$item_retail = sanitize_text_field($_POST['item_retail']);
	$item_sale = sanitize_text_field($_POST['item_sale']);
	$ct = '
		<p>
		<strong>'.sanitize_text_field($_POST['item_title']).'</strong><br />
		<strong>Description: </strong>'.sanitize_textarea_field($_POST['item_desc']).'<br />
		<strong>Retail Price: </strong>$'.number_format($item_retail,2).'<br />
		<strong>Store Price: </strong>$'.number_format($item_sale,2).'<br />
		<strong>Size: </strong>'.sanitize_text_field($_POST['item_size']).'<br />
		<strong>Colour: </strong>'.sanitize_text_field($_POST['item_colour']).'<br />
		<strong>State of Item: </strong>'.sanitize_text_field($_POST['item_state']).'<br />
		<strong>Phone: </strong>'.sanitize_text_field($_POST['phone']).'<br />
		<strong>Email: </strong>'.sanitize_email($_POST['email']).'<br />
		</p>';
	return $ct;
}

// used in both admin and public -- get all possible splits 
// currently hardcoded. May put in admin later
function cwscsGetAllSplits() {
	$splits = array(50=>'50 / 50 (default)', 
					100=>"100 for store / 0 to seller (donated item)", 
					75=>"75% for store / 25% to seller",
					25=>"25% for store / 75% to seller",
					0=>"0 / 100% to Seller"
				);
	return $splits;
}
// handle the image upload from the ajax function - the form does not actually upload a file on submit. It is done when they select an image file so it can be resized.
function cwscs_uploadImg() {
	$upload_dir_paths = wp_upload_dir();
	$baseurl = $upload_dir_paths['baseurl'];
	$basedir = $upload_dir_paths['basedir'];
	$imagename = "image_data";
	$msg = "";
	$status = 1;
	$allowed = array("image/jpeg", "image/pjpeg");
	$img = "";
	if (isset($_POST['tmpfilename']) && $_POST['tmpfilename'] != "") {
		$tmpfilename = sanitize_imagename($_POST['tmpfilename']); // replaces whitespace with dashes
		
	} else
		$tmpfilename = 'newimg-'.date("Ymdhis").'.jpg';
	$max_upload_size = wp_max_upload_size();
	if ($_FILES[$imagename]['error'] === UPLOAD_ERR_OK) {
		// first check on filetype
		$type = sanitize_text_field($_FILES[$imagename]['type']);
		$mime = wp_get_image_mime($_FILES[$imagename]["tmp_name"]);
		$fileInfo = @getimagesize($_FILES[$imagename]['tmp_name']);
		if ($_FILES[$imagename]['name'] != "" && in_array($type, $allowed) && in_array($mime, $allowed) && in_array($fileInfo['mime'], $allowed) && $fileInfo[0] > 0) {
			
			$size = sanitize_text_field($_FILES[$imagename]['size']);
			if ($_FILES[$imagename]['size'] > $max_upload_size) {
				$msg .= 'Image is too big! Can accept images that are bigger than '.esc_html($max_upload_size).'. This one is '.esc_html($size).' bytes.';
				$status = 0;
			} else {
				$tmpfilename = str_replace("%20","-",$tmpfilename);
				$partimgurl = $baseurl.'/'.date("Y").'/'.date("m").'/'.$tmpfilename;
				$fullimgurl = $basedir.'/'.date("Y").'/'.date("m").'/'.$tmpfilename;
				// move the image and return the image name
				if (move_uploaded_file($_FILES[$imagename]['tmp_name'], $fullimgurl)) {
					$msg .= 'Image has been uploaded to '.esc_html($fullimgurl).'. ';
				} // END no errors in upload
				else {
					$status = 0;
					$msg .= 'Could not upload to '.esc_html($fullimgurl).'. ';
					$img = "";
				}
			} // END upload
		} // passed checks
		else {
			$status = 0;
			$msg = "Could not upload since this is not an image file. ";
		}
	} // no error
	else {
		$msg = 'Could not upload image file.';
	}
	$results = array("status"=>$status, "message"=>$msg);
	if ($status == 1) {
		$results['fullimgurl'] = $fullimgurl;
		$results['partimgurl'] = $partimgurl;
	}
	return $results;	
}
