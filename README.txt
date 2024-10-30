=== Consignment Store For WooCommerce ===
Contributors: charcope
Donate link: https://charlenesweb.ca/donate/
Tags: consignment store, consignment for WooCommerce
Requires at least: 6.5.5
Requires PHP: 7.4
Tested up to: 6.6
Stable tag: 1.7.9
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily allow sellers to submit items for your review to your consignment store. Once approved, items are added to your WooCommerce store.

== Description ==

CWS Consignment Store for WooCommerce lets the general public upload their items for consideration to your online and physical consignment store.
You will be notified that an item has been submitted. You can review the item and either approve or reject.
If you approve, the item will be added to WooCommerce and is immediately available in your store. You may change the "split" on the revenue from the item. The default is 50-50. 
If you reject, the seller will be notified by email.
You can also use the plugin to put your existing inventory online. Use the easy form, snap a few pictures, and it will automatically be added to your WooCommerce online store without going through the approval step. 

Uninstalling the plugin will remove all associated tables and data.

Pre-requisites:
WooCommerce

= Live Demo =
<a href="https://charlenesweb.ca/cws-demos/">**Seller's Form to Add Item and the Admin area**</a><br>

= Doc =
<a href="https://charlenesweb.ca/cws-documentation/">Documentation</a>

= Features =
* Image resized on user's device before upload
* Images deleted from Media Library if item is rejected
* Help seller set a price by displaying lowest, highest and average prices for items in your store, by category.
* Track payouts to the seller once an item sells.

== Installation ==

The easiest way to install CWS Consignment Store is via your WordPress Dashboard. Go to the "Plugins" screen, click "Add New", and search for "CWS Consignment" in the WordPress Plugin Directory. Then, click "Install Now" and wait a moment. Finally, click "Activate" and start using the plugin!

Manual installation works just as for other WordPress plugins:

1. [Download](https://downloads.wordpress.org/plugin/cws-consignment.latest-stable.zip) and extract the ZIP file.
1. Move the folder "cws-consignment" to the "wp-content/plugins/" directory of your WordPress installation, e.g. via FTP.
1. Activate the plugin "CWS Consignment Store" on the "Plugins" screen of your WordPress Dashboard.
1. Create a page for potential Sellers to submit their items and add the shortcode [additemform]
1. Review the submitted items in the WordPress admin area. 

== Frequently Asked Questions ==

= What size are the images uplon upload? =

Images are resized to a maximum of 544px height or width. 

= How do I add a SKU to the item? =

If you are logged in to your site, go to the page where you have included the [additemform] shortcode and you will see SKU as the first input field. 

= Do SKU's have to be unique? =

SKU's do need to be unique. Since this is for a consignment shop it is assumed each item is unique and requires its own sku. 

= I get a 403 when I try to select an image on the Add Item form. =

I have seen this on Microsoft Edge when there is a security plugin such as WordFence activated. You need to allow basedir to be posted. In WordFence, go to All Options and scroll down to Allolisted URLs. Add a new URL that is the page where your form is (/addanitem/ for example). Select Param Type POST Body, and enter basedir in the Param Name. Click ADD. Add another rule now for the ajax file by entering URL /wp-content/plugins/cws-consignment/public/class-cws-consignment-public.php. And again select Param Type POST Body, and enter basedir in the Param Name. Click ADD. And Save Changes.


== Screenshots ==

1. Add Item Form
2. Review Submitted Items
3. Manage Payouts
4. Settings
5. Documentation

== Changelog ==
= 1.7.9 =
* Go through WP 6.6 compatibility requirements. No changes required.

= 1.7.8 =
* Always show store policy if it exists

= 1.7.7 =
* Change class name from hidden to cwshidden

= 1.7.6 =
* Update version tag

= 1.7.5 =
* Fix for multisite

= 1.7.4 =
* Change current URL

= 1.7.3 =
* Initialize variable

= 1.7.2 =
* Fix for PHP 8

= 1.7.1 =
* Update version number

= 1.7 =
* Feature add: added more Store Split options

= 1.6 =
* Feature add: added 0/100 as an option for the split (100% to seller)

= 1.5.2 =
* Remove debug message

= 1.5.1 =
* Spelling error

= 1.5 =
* Small formatting change

= 1.4 =
* Remove admin-ajax references since causing 403 errors

= 1.3 =
* Validate works with WordPress 5.9

= 1.2 =
* File verification at upload and attach

= 1.1 =
* Add more file verification

= 1.0 =
* First release

== Upgrade Notice ==
= 1.7.9 =
* Tag update. Update version when convenient.

= 1.7.8 =
* Always show store policy if it exists. Update when convenient.

= 1.7.7 =
* Minor change to seller form. Update when convenient.

= 1.7.6 =
* Update version tag

= 1.7.5 =
* Fix for multisite

= 1.7.4 =
* Change current URL

= 1.7.3 =
* Initialize variable

= 1.7.2 =
* Fix for PHP 8

= 1.7.1 =
* Update version number

= 1.7 =
* Feature add: added more Store Split options

= 1.6 =
Feature add. Upgrade at your convenience. 

= 1.5.2 =
Remove a debug message. Upgrade at your convenience. 

= 1.5.1 =
Spelling error. Upgrade at your convenience. 

= 1.5 =
Small formatting change. Upgrade immediately. 

= 1.4 =
This version removes admin-ajax since was causing 403 errors with caching in plugins and in server settings. Upgrade immediately. 

= 1.3 =
Reviewed WordPress 5.9 changes. No changes required to plugin. Upgrade at your convenience.

= 1.2 =
This version fixes a security related bug.  Upgrade immediately.

= 1.1 =
This version fixes a security related bug.  Upgrade immediately.

= 1.0 =
Initial release
