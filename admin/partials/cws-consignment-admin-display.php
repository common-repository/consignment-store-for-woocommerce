<?php

/**
 * Provide an admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://charlenesweb.ca
 * @since      1.0.0
 *
 * @package    cws_consignment
 * @subpackage cws_consignment/admin/partials
 */
///////////////////////////////////////
// SUBMITTED ITEMS display functions
///////////////////////////////////////
function cwscsShowSubmittedPage($menu_slug, $results) {
	$ctr = 0;	
	echo '<div class="cwscs_admin">';	
	if (is_array($results) || is_object($results)) {
		echo '<hr /><p>This table shows the '.count($results).' items that have been submitted and are not yet approved. </p><table class="cwscs_admin_table">
		<thead><tr><th width="15%">Date Added</th><th width="20%">Seller</th><th width="50%">Item</th><th align="center" width="15%">See Item</th></tr></thead><tbody>';
		foreach ($results as $i => $row) {
			$ctr++;
			echo '
			<tr>
				<td>'.$ctr.'. '.esc_html($row->date_added).'</td>
				<td>'.esc_html($row->seller_name).', '.esc_html($row->email).', '.esc_html($row->phone).'<br />Split: '.esc_html($row->store_split).'</td>
				<td>';
				cwscsShowItemDeets($row, false, true);
				echo '</td>
				<td align="center">
					<form action="/wp-admin/admin.php?page='.esc_html($menu_slug).'" method="post">
						<input type="hidden" value="'.esc_html($row->ID).'" name="item_id">
						<button type="submit" class="single_add_to_cart_button button">Approve / Reject</button>
					</form>	
				</td>
			</tr>';
		}
		echo '</tbody><tfoot><th colspan=4>Total Items to Review: '.esc_html($ctr).'</th></tr></tfoot></table>';
	}
}

// They selected an item to approve or reject
function showApproveRejectForm($menu_slug, $row) {
	$splits = cwscsGetAllSplits();
	echo '<h3>'.esc_html($row->item_title).'</h3>';
	cwscsShowItemDeets($row, true, true); // show deets and all images
	$_POST['item_id'] = intval($_POST['item_id']);
	echo
	'<form action="/wp-admin/admin.php?page='.esc_html($menu_slug).'" method="post" class="cwsreview_item">
		<input type="hidden" name="item_id" value="'.esc_html($_POST['item_id']).'" />
		<input type="hidden" name="item_image1" value="'.esc_html($row->item_image1).'" />
		<input type="hidden" name="item_image2" value="'.esc_html($row->item_image2).'" />
		<input type="hidden" name="item_image3" value="'.esc_html($row->item_image3).'" />
		<input type="hidden" name="item_image4" value="'.esc_html($row->item_image4).'" />
		<h3>Your Review</h3>';
		// show store split
		
		echo '
		<p id="p-store_split" class="cwshidden hideifrejected">
			<label for "store_split">Review Store Split</label>
			<select id="store_split" name="store_split">';
			foreach ($splits as $i => $s) {
				echo '<option value='.esc_html($i);
				if ($row->store_split == $i)
					echo ' selected="selected" ';
				echo '>'.esc_html($s).'</option>';
			}
			echo '
			</select>
		</p>
		<p id="p-sku" class="cwshidden hideifrejected">
			<label for "sku">Enter SKU</label>
			<input type="text" id="sku" name="sku" maxlength=8 value="';
			if ($row->sku != "")
				echo esc_html($row->sku);
			echo '" /> 
		</p>
	
		<p id="p-approved">';
			$approved = array(1=>"Approve", 2=>"Reject");
			foreach ($approved as $i =>$s) {
				echo '
				<label class="radio" for="approved_'.esc_html($i).'">
					<input type="radio" name="approved" required id="approved_'.esc_html($i).'" class="cwscheckapproved" value="'.esc_html($i).'" ';
					if ($i == $row->approved)
						echo ' checked="checked" ';
					echo '/> '.esc_html($s).'
				</label>&nbsp;&nbsp;';
			} // END loop on approved options
		echo '</p>';
		if ($row->approved == 1)
			$cwshidden = "";
		else
			$cwshidden = "cwshidden ";
		$sale = esc_html($row->item_sale);
		$sale = $sale * 1;
		echo '
		<div id="approved-email" class="'.esc_html($cwshidden).'email-msg">
			<p id="p-approved_sendemail">
				<label for "approved_sendemail">Send An Email Upon Approval?</label>';
				echo '
				<label class="radio" for="approved_sendemail_yes">
					<input type="radio" name="approved_sendemail" id="approved_sendemail_yes" onclick="showHideApproved(1)" value="Yes" checked="checked" /> Yes 
				</label>&nbsp;&nbsp;
				<label class="radio" for="approved_sendemail_no">
					<input type="radio" name="approved_sendemail" id="approved_sendemail_no" onclick="showHideApproved(2)" value="No" /> No 
				</label>&nbsp;&nbsp;
			</p>
			<div id="approved-email-content">
				<label>Email To</label>
				<input type="email" name="approved-email" value="'.esc_html($row->email).'" />
				<label>Body of Email</label>
				<textarea name="approved-body">We have approved your item: '.esc_html($row->item_title)."\r\n\r\nThe sale price will be $".number_format($sale, 2)."\r\n\r\nWe will split any proceeds 50 / 50. \r\n\r\nIf the item does not sell within 6 months we will donate it. \r\n\r\nYou may drop it off during store hours. \r\n\r\nBe sure to see this item and other items in the store on our website.\r\n\r\nThanks, Store Manager</textarea>
			</div>".'
		</div>';
		if ($row->approved == 2)
			$cwshidden = "";
		else
			$cwshidden = "cwshidden ";
		echo '	
		<div id="rejected-email" class="'.esc_html($cwshidden).'email-msg">
			<p id="p-rejected_sendemail">
				<label for "rejected_sendemail">Send An Email Upon Rejection?</label>';
				echo '
				<label class="radio" for="rejected_sendemail_yes">
					<input type="radio" name="rejected_sendemail" required id="rejected_sendemail_yes" onclick="showHideRejected(1)" value="Yes" checked="checked" /> Yes 
				</label>&nbsp;&nbsp;
				<label class="radio" for="rejected_sendemail_no">
					<input type="radio" name="rejected_sendemail" required id="rejected_sendemail_no" onclick="showHideRejected(2)" value="No" /> No 
				</label>&nbsp;&nbsp;
			</p>
			<div id="rejected-email-content">
				<label>Email To</label>
				<input type="email" name="rejected-email" value="'.esc_html($row->email).'" />
				<label>Body of Email</label>
				<textarea name="rejected-body">I am sorry but I cannot accept your item, '.esc_html($row->item_title).", for sale at the Consignment Store. \r\n\r\nThanks, Store Manager</textarea>".'
			</div>
		</div>	
		<p id="p-reviewer_comments" class="cwshidden hideifrejected">
			<label for "reviewer_comments">Want to add any comments? <span>These will be saved in the database for internal purposes only. They will not be shown to the Seller. </span></label>
			<textarea id="reviewer_comments" name="reviewer_comments">';
			if ($row->reviewer_comments != "")
				echo esc_textarea($row->reviewer_comments);
			echo '</textarea>
		</p>
		<p><button type="submit" class="single_add_to_cart_button button">Save Item</button></p>
	</form>';
}

///////////////////////////////////////
// PAYMENTS display functions
///////////////////////////////////////
function cwscsShowPayoutsPage($menu_slug, $search_sku, $search_kw, $show, $results) {
	$ctr = 0;
	// Show search and filter form
	showFilterPayouts($menu_slug, $search_sku, $search_kw, $show);
	
	// loop through results and see if sold		
	if (is_array($results) || is_object($results)) {
		echo 'Showing '.count($results).' Sold Items in inventory. </p>
		<table class="cwscs_admin_table">
			<thead>
				<tr>
					<th>Date Added</th>
					<th>Seller</th>
					<th>Item</th>
					<th>Status</th>
					<th align="center">Action</th>
				</tr>
			</thead>
			<tbody>';
		foreach ($results as $i => $row) {	
			$ctr++;
			echo 
			'<tr>
				<td>'.esc_html($ctr).'. '.esc_html($row->date_added).'</td>
				<td>'.esc_html($row->seller_name).', '.esc_html($row->email).', '.esc_html($row->phone).'<br />Split: '.esc_html($row->store_split).'</td>
				<td>';
				cwscsShowItemDeets($row, false, true);
				echo '</td>
				<td>';
				// store status
				if (isset($row->woo['woo_stock']) && $row->woo['woo_stock'] == "instock")
					echo 'In Store & In Stock';
				elseif (isset($row->woo['woo_stock']) && $row->woo['woo_stock'] == "outofstock") {
					echo 'Sold ';
					if (isset($row->woo['woo_sales'])) { // qty sold
						$price = esc_html($row->woo['woo_price']);
						echo 'for $'.number_format($price,2);
					}
				} else
					echo 'Approved';
				echo '</td>
				<td align="center">
					<form action="/wp-admin/admin.php?page='.esc_html($menu_slug).'" method="post">
						<input type="hidden" value="'.esc_html($row->ID).'" name="item_id">
						<input type="hidden" value="'.esc_html($row->woo['woo_price']).'" name="sell_price">
						<button type="submit" class="single_add_to_cart_button button">Manage Payment</button>
					</form>		
				</td>
			</tr>';
		} // END loop on results
		echo '
		</tbody><tfoot>
			<tr>
				<th colspan=6>Total Items: '.esc_html($ctr).'</th>
			</tr>
		</tfoot></table>';
	} // END there are results
	elseif (is_string($results) && $results != "") {
		echo esc_html($results);
	} else
		echo '<p>No results found. </p>';
}
// Display search and filter form for Payouts
function showFilterPayouts($menu_slug, $search_sku, $search_kw, $show="unpaid") {
	$types = array("unpaid", "paid", "all");
	echo '
	<form action="/wp-admin/admin.php?page='.esc_html($menu_slug).'" method="post" class="cwscsradio_group">
		<label><strong>Show:</strong> </label>';
	foreach ($types as $i => $t) {
		echo '
		<label for="'.esc_html($t).'">
			<input type="radio" name="payment_type" id="'.esc_html($t).'" value="'.esc_html($t).'"';
		if (isset($show) && $show == $t) {
			echo ' checked="checked" ';
		}
		echo '/> '.esc_html(ucfirst($t)).'</label>&nbsp;&nbsp;';
	} // END loop on types
	echo '<br />
		<label for="store_tag"><strong>Search on SKU:</strong> </label>
		<input type="text" name="search_sku" id="search_sku" style="width:150px" value="'.esc_html($search_sku).'" \>&nbsp;&nbsp;
		<label for="search_kw">OR <strong>Search on keyword(s):</strong> </label>
		<input type="text" name="search_kw" id="search_kw" style="width:300px" value="'.esc_html($search_kw).'" \>&nbsp;&nbsp;
		<input type="submit" name="view_lessons" value="Go >" class="single_add_to_cart_button button" />
	</form>';
}
function cwscsShowPaymentForm($menu_slug, $item) {
	$splits = cwscsGetAllSplits();
	echo '
	<form action="/wp-admin/admin.php?page='.esc_html($menu_slug).'" method="post" class="cwscsradio_group">
		<h2>Add Payment</h2>
		<input type="hidden" name="item_id" value="'.esc_html($_POST['item_id']).'" />';
		
		if (isset($_POST['sell_price']) && $_POST['sell_price'] > 0) {
			$sell_price = sanitize_text_field($_POST['sell_price']);
			echo '
			<p>
				<label for "sell_price">Sold for: $'.esc_html(number_format($sell_price,2)).'</label>
			</p>';
		}
		// show store split
		echo '
		<p id="p-store_split">
			<label for "store_split">Store Split: ';
			foreach ($splits as $i => $s) {
				if ($item->store_split == $i)
					echo esc_html($s);
			}
			echo '</label>
		</p>
		<p id="p-payment" >
			<label for "payment">Do you want to record a payment to the Seller? </label>
			<input type="text" id="payment" name="paidpayment" maxlength=8 value="';
			if (isset($item->paid) && $item->paid > 0)
				echo esc_html($item->paid);
			echo '" /> 
		</p>
		<p><button type="submit" class="single_add_to_cart_button button">Save Payment</button></p>
	</form>';
}
////////////////////////////////////////////////////////////////////////////////////////////
// Settings page
////////////////////////////////////////////////////////////////////////////////////////////
function cwscsShowSettingsMenu($menu_slug, $msg) {
	$tabs = array("General", "Categories", "Store Policy", "Store Splits", "Google reCAPTCHA v2", "Google reCAPTCHA v3", "Emails");
	$icons = array("dashicons-admin-tools", "dashicons-category", "dashicons-edit", "dashicons-chart-pie", "dashicons-admin-generic", "dashicons-admin-generic", "dashicons-email-alt");
	$content = cwscsGetSettingsContent();
	
	echo '
    <div class="cwscs_tab">';
	foreach ($tabs as $i => $tab) {
		if ($i == 0 || $i == 5) { // hide General and recaptcha v3
			echo '
    	    <button class="cwscs_tablinks cwshidden" id="btntab_'.esc_html($i).'"><span class="dashicons '.esc_html($icons[$i]).'"></span>'.esc_html($tab).'</button>';
		} else {
			echo '
    	    <button class="cwscs_tablinks" id="btntab_'.esc_html($i).'"><span class="dashicons '.esc_html($icons[$i]).'"></span>'.esc_html($tab).'</button>';
		}
	}
	echo '
    </div>';
    // Put the title on each page
	foreach ($tabs as $i => $tab) {
		echo '
		<div id="contenttab_'.esc_html($i).'" class="cwscs_tabcontent cwshidden">
			<h3>'.esc_html($tab).'</h3>'.
			$content[$i].'
		</div>';
	} // END loop on content
	// Save button
	echo '
	<div class="clear"></div>
	<div id="cwscs_msg">'.$msg.'</div>
	<div class="button-wrap">
	<form action="/wp-admin/admin.php?page='.esc_html($menu_slug).'" method="post" id="cwscs_settings_form">
		<input type="hidden" name="cwscs_key" id="cwscs_key" value="categories" />
		<input type="hidden" name="cwscs_value" id="cwscs_value" value="" />
		<input type="hidden" name="cwscs_method" id="cwscs_method" value="savecategories" />
		<button type="button" class="button button-primary btn-save-settings" id="btnsave_1">Save Categories</button>
	</div>';
	// initialize
	?>
    <script>
		// initialize on categories
		jQuery('#btntab_1').addClass("active");
		jQuery('#contenttab_1').removeClass("cwshidden");
	</script>
	<?php
}
// Get content for settings tabs
// Fetch getcontent for settings tabs
function cwscsGetSettingsContent() {
	$content = array();
	// General
	$content[] = "<p>Content for general</p>";
	
	// Categories -- display checkboxes
	$myPicks = cwscsGetSettingByKeyReturnArray("categories");
	$allPicks = cwscsGetCategories();
	$content[] = cwscsShowCategories($myPicks, $allPicks);
	
	// Store Policy
	$storepolicy = cwscsGetSettingByKey("store-policy");
	$content[] = cwscsShowStorePolicy($storepolicy);
	
	// Store Splits
	$mySplits = cwscsGetSettingByKeyReturnArray("store-splits");
	$allSplits = cwscsGetAllSplits();
	$content[] = cwscsShowStoreSplits($mySplits, $allSplits);
	
	// recaptcha v2
	$myRecaptcha = cwscsGetSettingByKeyReturnArray("recaptcha-v2");
	$content[] = cwscsShowRecaptcha($myRecaptcha, "recaptcha-v2");
	
	// recaptcha v3
	//$myRecaptcha = cwscsGetSettingByKeyReturnArray("recaptcha-v3"); no v3 for now
	//$content[] = cwscsShowRecaptcha($myRecaptcha, "recaptcha-v3");
	$content[] = "";
	
	// Emails
	$emails = cwscsGetSettingByKeyReturnArray("emails");
	$content[] = cwscsShowEmails($emails);
	return $content;
}

// Display the item. If show_more is true, show all images
function cwscsShowItemDeets($row, $show_more=false, $show_pics=true) {
	if ($show_more)
		$w = 200; // bigger images
	else
		$w = 100;
	if ($show_pics) {
		if ($row->item_image1 > 0) {
			$attachment_id = $row->item_image1;
			$image_thumbnail = wp_get_attachment_image_src($attachment_id, 'single-thumbnail');
			if ( $image_thumbnail ) {
				echo '
				<img class="alignnone size-single-thumbnail" src="'.esc_html($image_thumbnail[0]).'" alt="item_image1" width="'.esc_html($w).'" ><br />';
			} // END show image
		}
		if ($show_more) {
			if ($row->item_image2 > 0) {
				$attachment_id = $row->item_image2;
				$image_full = wp_get_attachment_image_src($attachment_id, 'full');
				if ( $image_full ) {
					echo '
					<img class="alignnone size-single-thumbnail" src="'.esc_html($image_full[0]).'" alt="item_image 2" width="'.esc_html($w).'" ><br />';
				} // END show image
			}
			if ($row->item_image3 > 0) {
				$attachment_id = $row->item_image3;
				$image_full = wp_get_attachment_image_src($attachment_id, 'full');
				if ( $image_full ) {
					echo '
					<img class="alignnone size-single-thumbnail" src="'.esc_html($image_full[0]).'" alt="item_image3" width="'.esc_html($w).'" ><br />';
				} // END show image
			}
			if ($row->item_image4 > 0) {
				$attachment_id = $row->item_image4;
				$image_full = wp_get_attachment_image_src($attachment_id, 'full');
				if ( $image_full ) {
					echo '
					<img class="alignnone size-single-thumbnail" src="'.esc_html($image_full[0]).'" alt="item_image 4" width="'.esc_html($w).'" ><br />';
				} // END show image
			}
		} // END show more
	} // END show pics
	echo '<br />
	<strong>'.esc_html($row->item_title).'</strong><br />';
	if ($row->item_desc != "")
		echo  '<strong>Description: </strong>'.esc_html($row->item_desc).'. ';
	if ($row->item_retail > 0) {
		$price = esc_html($row->item_retail);
		echo  '<strong>Retail Price: </strong>$'.number_format($price,2).'. ';
	}
	if ($row->item_sale > 0) {
		$price = esc_html($row->item_sale);
		echo  '<strong>Store Price: </strong>$'.number_format($price,2).'. ';
	}
	if ($row->item_size != "")
		echo  '<strong>Size: </strong>'.esc_html($row->item_size).'. ';
	if ($row->item_colour != "")
		echo  '<strong>Colour: </strong>'.esc_html($row->item_colour).'. ';
	if ($row->item_state != "")
		echo  '<strong>State of Item: </strong>'.esc_html($row->item_state).'. ';
	if ($row->sku != "")
		echo  '<strong>SKU: </strong>'.esc_html($row->sku).'. ';
	return;	
}

// Display categories for update
function cwscsShowCategories($myPicks, $allPicks) {
	$ct = '<p>Click which categories to include in the Add Item to Consignment Store Form on the website. </p>';
	foreach ($allPicks as $i => $obj) {
		$ct .= '
		<label class="radio" for="'.$obj->term_id.'">
			<input type="checkbox" name="'.$obj->term_id.'" id="'.$obj->term_id.'" value="'.$obj->term_id.'" ';
			if (count($myPicks) == 0 || in_array($obj->term_id, $myPicks))
				$ct .= 'checked="checked" ';
			$ct .= '/> '.$obj->name.'
		</label><br />';
	}
	return $ct;
}
// Display the store policy for update
function cwscsShowStorePolicy($storepolicy) {
	$status = 0;
	$show = 0;
	$text = "";
	if ($storepolicy['status'] == 1 && $storepolicy['data'] != "") {
		$status = 1;
		$data_str = $storepolicy['data'];
		$data = explode('::',  $data_str);
		foreach ($data as $i => $d) {
			if ($i == 0) {
				$show = trim($d);
			} elseif ($i == 1) {
				$text = trim($d);
			}
		}
	}

	$ct = "<p>You may choose to show your store policy on the Add Item to Consignment Store Form on your website. If you do, the seller will have to accept the store policy. </p>";

	if ($text == "") {
		$text = 
		"Use this form to submit your items to the consignment store. If they are in good shape, clean and generally ready to sell then we will approve the item for the store, and split the proceeds of any sale 50/50.\r\n\r\nIf we do accept your item for the store, we will email you to let you know, and to determine a time for you to drop your item off.\r\n\r\nIf after 6 months in the store, the item has not sold, we may donate the item or let you know to come pick it up.";
	}
	
	$ct .= '
	<p>
		<label class="radio" for="storepolicyshow">
			<input type="checkbox" name="storepolicyshow" id="storepolicyshow" value="1" ';
			if ($show == 1)
				$ct .= 'checked="checked" ';
			$ct .= '/> Show Store Policy on Add Item Form
		</label>
	</p>
	<p>
		<label for="storepolicytext">Store Policy<br />
			<textarea name="storepolicytext" id="storepolicytext">'.esc_textarea($text).'</textarea>
		</label>
	</p>';
	return $ct;
}
// Display the store splits for update
function cwscsShowStoreSplits($mySplits, $allSplits) {
	$ct = '<p>Click which splits to include in the Add Item to Consignment Store Form on the website. The store splits are only available to the Administrators to set. </p>';
	
	foreach ($allSplits as $i => $split) {
		$ct .= '
		<label class="radio" for="'.$i.'">
			<input type="checkbox" name="'.$i.'" id="'.$i.'" value="'.$i.'" ';
			if (count($mySplits) == 0 || in_array($i, $mySplits))
				$ct .= 'checked="checked" ';
			$ct .= '/> '.$split.'
		</label><br />';
	}
	return $ct;
}

// Display the recaptcha options
function cwscsShowRecaptcha($myRecaptcha, $version) {
	// any current values?
	$site_key = "";
	$secret = "";
	if ($version == "recaptcha-v2")
		$ext = 'v2';
	else
		$ext = 'v3';
	if (is_array($myRecaptcha)) {
		if (isset($myRecaptcha[0]) && $myRecaptcha[0] != "")
			$site_key = $myRecaptcha[0];
		if (isset($myRecaptcha[1]) && $myRecaptcha[1] != "")
			$secret = $myRecaptcha[1];
	}
	$ct = '
	<input type="hidden" name="version" id="cwscs_version'.$ext.'" value="'.$version.'" />
	<p>
		<label for="cwscs_site_key'.$ext.'">Site Key</label><br />
		<input type="text" name="cwscs_site_key" id="cwscs_site_key'.$ext.'" value="'.$site_key.'" style="width:350px"/>
	</p>
	<p>
		<label for="cwscs_secret'.$ext.'">Secret</label><br />
		<input type="text" name="cwscs_secret" id="cwscs_secret'.$ext.'" value="'.$secret.'" style="width:350px"/>
	</p>';
	return $ct;
}
// Show the email settings form
function cwscsShowEmails($emails) {
	// any current values?
	$cwscs_from_email = "";
	$cwscs_to_email = "";
	if (isset($emails) && is_array($emails) && count($emails) > 0) {
		$cwscs_from_email = $emails[0];
		if (count($emails) >1)
			$cwscs_to_email = $emails[1];
	}
	$ct = '
	<p>
		<label for="cwscs_from_email">Send from</label>
		<br />
		<input type="email" name="cwscs_from_email" id="cwscs_from_email" value="'.$cwscs_from_email.'" style="width:350px" aria-describedby="descriptionFromEmail"/><br />
		<span id="descriptionFromEmail" class="small">Send from email adress (Example: Name &lt;name@domain.com&gt;). Leave blank to use default address.</span>
	</p>
	<p>
		<label for="cwscs_to_email">Send to</label><br />
		<input type="email" name="cwscs_to_email" id="cwscs_to_email" value="'.$cwscs_to_email.'" style="width:350px" aria-describedby="descriptionToEmail" /><br />
		<span id="descriptionToEmail" class="small">Send to email adress (Example: Name &lt;name@domain.com&gt;). To notify of an item submitted to the consignment store. If blank, a notification email will not be sent.</span>
	</p>
	<div class="clear"></div>';
	return $ct;
}
///////////////////////////////////////////////////////////
// Documentation page
//////////////////////////////////////////////////////////
function cwscsShowDocsPage() {
	?>
	<h1>Documentation</h1>
	<div class="twothirds">
		<h2>On the Website</h2>
		<p>Visitors to your website, as well as your staff, can submit items to your consignment store using the <strong>Add Item</strong> form. </p>
		<ol>
		<li>Create a page</li>
		<li>Add shortcode <pre>[additemform]</pre></li>
		<li>That is it!</li>
		</ol>
		<p>If a visitor submits an item, it is not added to the store right away. You will receive a notification email to review the item. You can review it under Submitted Items. Set Emails in Settings.</p>
		<p>If an administrator submits an item, it is automatically added to the store. They will be required to enter a unique sku.</p>
		<h2>Backend</h2>
		<h3>Submitted Items</h3>
		<p>Displays items in the inventory that have been submitted and not approved yet.</p>
		<p>Click Approve / Reject to show form</p>
		<p>If you click Approve:</p>
		<ol>
			<li>Select the store split - default is 50% to store and 50% to seller</li>
			<li>Enter a unique SKU - it cannot be used in the store</li>
			<li>Select whether to send an email to the seller. You have the option to modify the body of the email. </li>
			<li>Optionally, enter comments. These will be saved in the Inventory table, but not shown in the store. </li>
			<li>On save, creates a WooCommerce product with inventory of 1. </li>
		</ol>	
		<p>If you click Reject:</p>
		<ol>
			<li>Select whether to send an email to the seller. You have the option to modify the body of the email. </li>
			<li>On save, deletes the item from the Inventory table and deletes associated images. </li>
		</ol>
		<h3>Manage Payouts</h3>
		<p>You may record your payouts to sellers using this feature. </p>
		<p>Displays approved items that have sold in the online store. </p>
		<p>Click Manage Payment</p>
		<ol>
			<li>Enter the amount of payment to the Seller</li>
			<li>Click Save Payment</li>
			<li>Saves payment in the Inventory table</li>
		</ol>
	</div>
	<div class="onethird greybox">
		<img src="<?php echo plugin_dir_url( __FILE__ ) .'cwscs-consignment.jpg'; ?>" alt="flowchart" />
	</div>
	<div class="clear"></div>';
	<h3>Settings</h3>
	<p>You can modify 5 settings.</p>
	<ol>
		<li>Categories</li>
		<li>Store Policy</li>
		<li>Store Splits</li>
		<li>Google reCAPTCHA v2</li>
		<li>Emails</li>
	</ol>	
	<h4>Categories</h4>
	<p>Displays a list of all categories in your WooCommerce store.</p>
	<p>Check categories to display on the Add Item form.</p>
	<p>By default, all categories are displayed. </p>
	<h4>Store Policy</h4>
	<p>You may choose to show the store policy on your Add Item form.</p>
	<p>You can change the default store policy.</p>
	<p>By default, the store policy is shown. </p>
	<h4>Store Splits</h4>
	<p>Displays a list of the available store splits.</p>
	<p>Check which splits to display on the Add Item form. These are only show to Administrators. </p>
	<p>By default, there are 2 splits available. </p>
	<h4>Google reCAPTCHA v2</h4>
	<p>Enter your site key and secret to enable the Google reCAPTCHA v2 "I am not a robot" checkbox on the Add Item form.</p>
	<h4>Emails</h4>
	<p>Enter the send from email or leave blank to use the WordPress default address.</p>
	<p>Enter the send to email. This will be used to notify you of a new item submitted from the Add Item form from a non-Administrator. </p>
	<?php
}