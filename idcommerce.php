<?php

//error_reporting(E_ALL);

//@ini_set('display_errors', 1);

/*
Plugin Name: IgnitionDeck Commerce
URI: http://IgnitionDeck.com
Description: A powerful, yet simple, content delivery system for WordPress. Features a widgetized dashboard so you can customize your product offerings, instant checkout, credits, and more.
Version: 1.5.6
Author: Virtuous Giant
Author URI: http://VirtuousGiant.com
License: GPL2
*/

define( 'IDC_PATH', plugin_dir_path(__FILE__) );

global $memberdeck_db_version;
$memberdeck_db_version = "1.5.6";
global $old_idc_version;
$old_idc_version = get_option('memberdeck_db_version');

$active_plugins = get_option('active_plugins', true);
if (in_array('ignitiondeck/idf.php', $active_plugins)) {
	include_once plugin_dir_path(dirname(__FILE__)).'ignitiondeck/idf.php';
}
else if (is_multisite() && file_exists(plugin_dir_path(dirname(__FILE__)).'/ignitiondeck/idf.php')) {
	include_once plugin_dir_path(dirname(__FILE__)).'ignitiondeck/idf.php';
}

if (in_array('ignitiondeck-crowdfunding/ignitiondeck.php', $active_plugins)) {
	include_once plugin_dir_path(dirname(__FILE__)).'ignitiondeck-crowdfunding/ignitiondeck.php';
}
else if (is_multisite() && file_exists(plugin_dir_path(dirname(__FILE__)).'ignitiondeck-crowdfunding/ignitiondeck.php')) {
	include_once plugin_dir_path(dirname(__FILE__)).'ignitiondeck-crowdfunding/ignitiondeck.php';
}

include_once 'classes/class-id-member.php';
include_once 'classes/class-id-member-level.php';
include_once 'classes/class-id-member-download.php';
include_once 'classes/class-id-member-order.php';
include_once 'classes/class-id-member-subscription.php';
include_once 'classes/class-id-member-credit.php';
include_once 'classes/class-id-member-metaboxes.php';
include_once 'classes/class-md-keys.php';
include_once 'classes/class-md-form.php';
include_once 'classes/class-id-member-email.php';
include_once 'classes/class-id-authorize.net.php';
include_once 'idcommerce-globals.php';
include_once 'idcommerce-admin.php';
global $s3;
$s3_enabled = $s3;
if ($s3_enabled) {
	include_once IDC_PATH.'lib/aws-config.php';
}
include_once 'idcommerce-functions.php';
include_once 'idcommerce-shortcodes.php';
include_once 'idcommerce-update.php';
if (function_exists('is_id_pro') && is_id_pro()) {
	$gateways = get_option('memberdeck_gateways');
	if (isset($gateways)) {
		if (!is_array($gateways)) {
			$gateways = unserialize($gateways);
		}
		if (isset($gateways['esc']) && $gateways['esc'] == 1) {
			include_once 'idcommerce-sc.php';
		}
		else if (isset($gateways['ebm']) && $gateways['ebm'] == 1) {
			include_once 'idcommerce-bm.php';
		}
	}
	include_once 'inc/idcommerce-ide.php';
}
global $crowdfunding;
global $global_currency;
$first_data = true;
if (class_exists('IDF')) {
	$platform = idf_platform();
	if ($platform == 'idc') {
		$pwyw = true;
	}
}
include_once 'inc/idcommerce-adaptive.php';
function idc_languages() {
  	load_plugin_textdomain( 'memberdeck', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
  	load_plugin_textdomain( 'idcommerce', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
  	add_filter('idc_localization_strings', 'idc_localization_strings');
}

add_action('plugins_loaded', 'idc_languages');

function idc_init() {
  	$general = get_option('md_receipt_settings');
	if (!empty($general)) {
		if (!is_array($general)) {
			$general = unserialize($general);
		}
		if (isset($general['disable_toolbar']) && $general['disable_toolbar'] == 1) {
			if (!current_user_can('administrator') && !is_admin()) {
  				show_admin_bar(false);
			}
		}
	}
	idc_set_roles();
}
add_action('init', 'idc_init', 1);

function idc_set_roles() {
	$admin = get_role('administrator');
	$cap_array = array(
		'idc_manage_members',
		'idc_manage_products',
		'idc_manage_gateways',
		'idc_manage_orders',
		'idc_manage_email',
		'idc_manage_crowdfunding',
		'idc_manage_extensions'
	);
	foreach ($cap_array as $cap) {
		$admin->add_cap($cap);
	}
}

function idc_localization_strings() {
	$strings = array();
	$strings['virtual_currency'] = __('Virtual Currency', 'memberdeck');
	$strings['purchase_form_shortcode'] = __('Purchase form shortcode', 'memberdeck');
	$strings['continue'] = __('Continue', 'memberdeck');
	$strings['complete_checkout'] = __('Complete Checkout', 'memberdeck');
	$strings['use_idcf_settings'] = __('Use IDCF Setting', 'memberdeck');
	$strings['continue_checkout'] = __('Continue Checkout', 'memberdeck');
	$strings['choose_product'] = __('Choose Product', 'memberdeck');
	$strings['choose_download'] = __('Choose Download', 'memberdeck');
	$strings['choose_credit'] = __('Choose Credit', 'memberdeck');
	$strings['no_payment_options'] = __('No Payment Options', 'memberdeck');
	return $strings;
}

// Let's determine whether we are installing on multisite or standard WordPress
// If multisite, we need to know whether we are network activated or activated on a per-site basis

if (is_multisite()) {
	// we only run this if we're network activating
	if (is_network_admin()) {
		register_activation_hook(__FILE__,'memberdeck_blog_install');
	}
	// we are not in network admin, so we run regular activation script
	else {
		register_activation_hook(__FILE__,'memberdeck_install');
	}
}
else {
	// not multisite, standard install
	register_activation_hook(__FILE__,'memberdeck_install');
}

if (is_md_network_activated()) {
	// setup again when new blogs are added
	add_action('wpmu_new_blog', 'memberdeck_install', 1, 1);
}

function memberdeck_blog_install() {
	global $wpdb;
	$sql = 'SELECT * FROM '.$wpdb->base_prefix.'blogs';
	$res = $wpdb->get_results($sql);
	foreach ($res as $blog) {
		memberdeck_install($blog->blog_id);
	}
}

function memberdeck_install($blog_id = null) {
	global $wpdb;
	global $memberdeck_db_version;
	global $old_idc_version;

	if (!empty($old_idc_version) && $old_idc_version < '1.2.7') {
		// new for 1.2.7 in order to normalize level data
	    $members = ID_Member::get_members();
	    foreach ($members as $member) {
	    	$levels = $member->access_level;
	    	if (!empty($levels)) {
	    		$levels = unserialize($levels);
	    		foreach ($levels as $level) {
	    			$id_member = new ID_Member($member->user_id);
	    			$add_level = $id_member->set_level($level);
	    		}
	    	}
	    }
		// convert serialized gateway settings into unserialized
    	$idc_gateway_settings = get_option('memberdeck_gateways');
    	if (!empty($idc_gateway_settings)) {
    		if (!is_array($idc_gateway_settings)) {
    			$idc_gateway_settings = unserialize($idc_gateway_settings);
    		}
    		update_option('memberdeck_gateways', $idc_gateway_settings);
    	}
	}

	if (!empty($old_idc_version) && $old_idc_version <= '1.5.1') {
		$get_db = get_page_by_title('Dashboard');
		if (!empty($get_db)) {
			$dash_settings = get_option('md_dash_settings');
			if (!empty($dash_settings)) {
				if (!is_array($dash_settings)) {
					$dash_settings = unserialize($dash_settings);
				}
				$dash_settings['durl'] = $get_db->ID;
				update_option('md_dash_settings', $dash_settings);
			}
		}
	}

	if (isset($old_idc_version) && $old_idc_version <= '1.5.4') {
		$global_currency = get_option('idc_global_currency');
		// If global currency is to Use IDCF (deprecated), set it to USD and store in DB too
		if ($global_currency == "idcf") {
			// Getting IDCF default currency
			if (class_exists('ID_Project')) {
				$default_settings = ID_Project::get_project_defaults();
				if (is_object($default_settings)) {
					$global_currency = $default_settings->currency_code;
				} else {
					$global_currency = "USD";
				}
				update_option('idc_global_currency', $global_currency);
			}
		}
	}

	$prefix = md_wpdb_prefix($blog_id);

	// 
	$memberdeck_members = $prefix . "memberdeck_members";
    $sql = "CREATE TABLE " . $memberdeck_members . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					user_id MEDIUMINT(9) NOT NULL,
					access_level TEXT NOT NULL,
					add_ons VARCHAR(250) NOT NULL,
					credits MEDIUMINT(9) NOT NULL,
					r_date DATETIME,
					reg_key VARCHAR(250) NOT NULL,
					data TEXT NOT NULL,
					UNIQUE KEY id (id));";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    update_option("memberdeck_db_version", $memberdeck_db_version);

    $memberdeck_member_levels = $prefix . "memberdeck_member_levels";
    $sql = "CREATE TABLE " . $memberdeck_member_levels . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					user_id MEDIUMINT(9) NOT NULL,
					level_id MEDIUMINT(9) NOT NULL,
					UNIQUE KEY id (id));";
    dbDelta($sql);

    $memberdeck_key_assoc = $prefix . "memberdeck_key_assoc";
    $sql = "CREATE TABLE " . $memberdeck_key_assoc . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					user_id MEDIUMINT(9) NOT NULL,
					download_id MEDIUMINT(9) NOT NULL,
					assoc MEDIUMINT(9) NOT NULL,
					UNIQUE KEY id (id));";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $memberdeck_keys = $prefix . "memberdeck_keys";
    $sql = "CREATE TABLE " . $memberdeck_keys . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					license VARCHAR(250) NOT NULL,
					avail MEDIUMINT(9) NOT NULL,
					in_use MEDIUMINT(9) NOT NULL,
					UNIQUE KEY id (id));";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $memberdeck_levels = $prefix . "memberdeck_levels";
    $sql = "CREATE TABLE " . $memberdeck_levels . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					product_type VARCHAR(250) NOT NULL DEFAULT 'purchase',
					level_name VARCHAR(250) NOT NULL,
					level_price VARCHAR (250) NOT NULL,
					credit_value MEDIUMINT(9) NOT NULL,
					txn_type VARCHAR (250) NOT NULL DEFAULT 'capture',
					level_type VARCHAR(250) NOT NULL,
					recurring_type VARCHAR(250) NOT NULL DEFAULT 'NONE',
					limit_term TINYINT(1) NOT NULL,
					term_length MEDIUMINT(9) NOT NULL,
					plan VARCHAR(250),
					license_count MEDIUMINT(9),
					enable_renewals TINYINT(1) NOT NULL,
					renewal_price VARCHAR(255) NOT NULL,
					enable_multiples TINYINT(1) NOT NULL,
					UNIQUE KEY id (id));";
    dbDelta($sql);

    $memberdeck_credits = $prefix . "memberdeck_credits";
    $sql = "CREATE TABLE " . $memberdeck_credits . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					credit_name VARCHAR(250) NOT NULL,
					credit_count MEDIUMINT(9) NOT NULL,
					credit_price VARCHAR (250) NOT NULL,
					credit_level MEDIUMINT(9) NOT NULL,
					UNIQUE KEY id (id));";
    dbDelta($sql);

    $memberdeck_downloads = $prefix . "memberdeck_downloads";
    $sql = "CREATE TABLE " . $memberdeck_downloads . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					download_name VARCHAR(250) NOT NULL,
					download_levels TEXT NOT NULL,
					button_text VARCHAR (250) NOT NULL,
					download_link VARCHAR (250) NOT NULL,
					info_link VARCHAR (250) NOT NULL,
					doc_link VARCHAR (250) NOT NULL,
					image_link VARCHAR (250) NOT NULL,
					version VARCHAR(250) NOT NULL,
					position VARCHAR(250) NOT NULL,
					licensed TINYINT(1) NOT NULL,
					hidden TINYINT(1) NOT NULL,
					enable_s3 TINYINT(1) NOT NULL,
					enable_occ TINYINT(1) NOT NULL,
					occ_level MEDIUMINT(9) NOT NULL,
					id_project MEDIUMINT(9) NOT NULL,
					updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					UNIQUE KEY id (id));";
    dbDelta($sql);

    $memberdeck_orders = $prefix . "memberdeck_orders";
    $sql = "CREATE TABLE " . $memberdeck_orders . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					user_id MEDIUMINT( 9 ) NOT NULL,
					level_id MEDIUMINT( 9 ) NOT NULL,
					order_date DATETIME,
					transaction_id VARCHAR (250) NOT NULL,
					subscription_id VARCHAR (250) NOT NULL,
					subscription_number MEDIUMINT( 9 ) NOT NULL,
					e_date DATETIME,
					status VARCHAR (250) NOT NULL,
					price VARCHAR(250) NOT NULL,
					UNIQUE KEY id (id));";
    dbDelta($sql);

    $memberdeck_order_meta = $prefix . "memberdeck_order_meta";
    $sql = "CREATE TABLE " . $memberdeck_order_meta ." (
			    	id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			    	order_id MEDIUMINT(9) NOT NULL,
			    	meta_key VARCHAR(255),
			    	meta_value LONGTEXT,
			    	UNIQUE KEY id (id));";
	dbDelta($sql);

    $memberdeck_preorders = $prefix . "memberdeck_preorder_tokens";
    $sql = "CREATE TABLE " . $memberdeck_preorders . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					order_id MEDIUMINT( 9 ) NOT NULL,
					charge_token VARCHAR (250) NOT NULL,
					gateway VARCHAR (250) NOT NULL,
					UNIQUE KEY id (id));";
    dbDelta($sql);

    $memberdeck_subscriptions = $prefix . "memberdeck_subscriptions";
    $sql = "CREATE TABLE " . $memberdeck_subscriptions . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					user_id MEDIUMINT( 9 ) NOT NULL,
					level_id MEDIUMINT( 9 ) NOT NULL,
					subscription_id VARCHAR(255) NOT NULL,
					payments MEDIUMINT( 9 ) NOT NULL,
					status VARCHAR(255) NOT NULL,
					source VARCHAR(250) NOT NULL,
					UNIQUE KEY id (id));";
    dbDelta($sql);

    $mdid_assignments = $prefix . "mdid_assignments";
    $sql = "CREATE TABLE " . $mdid_assignments . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					level_id BIGINT(20) NOT NULL,
					project_id BIGINT(20) NOT NULL,
					assignment_id BIGINT(20) NOT NULL,
					UNIQUE KEY id (id));";
    dbDelta($sql);

    $project_levels = $prefix . "mdid_project_levels";
    $sql = "CREATE TABLE " . $project_levels . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					levels VARCHAR(255) NOT NULL,
					UNIQUE KEY id (id));";
    dbDelta($sql);

    $mdid_orders = $prefix . "mdid_orders";
	$sql = "CREATE TABLE " . $mdid_orders . " (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
		customer_id VARCHAR(255) NOT NULL,
		subscription_id VARCHAR(255),
		order_id BIGINT(20),
		pay_info_id BIGINT(20) NOT NULL,
		UNIQUE KEY id (id));";
	dbDelta($sql);

	$md_sc_params = $prefix . "memberdeck_sc_params";
	$sql = "CREATE TABLE " . $md_sc_params . " (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
		user_id MEDIUMINT(9) NOT NULL,
		access_token VARCHAR(255) NOT NULL,
		refresh_token VARCHAR(255) NOT NULL,
		stripe_publishable_key VARCHAR(255) NOT NULL,
		stripe_user_id VARCHAR(255) NOT NULL,
		UNIQUE KEY id (id));";
	dbDelta($sql);

	$bm_payouts = $prefix . "bm_payouts";
	$sql = "CREATE TABLE " . $bm_payouts . " (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
		order_id MEDIUMINT(9) NOT NULL,
		payout_status VARCHAR(255),
		UNIQUE KEY id (id));";
	dbDelta($sql);

    do_action('idc_after_install');
}

add_action('idc_after_install', 'idc_set_defaults');

function idc_set_defaults() {
	global $crowdfunding;
	global $memberdeck_db_version;
	global $old_idc_version;

	$registration_email_default = 
		'<h3>{{COMPANY_NAME}} Payment Receipt</h3>
		Hello {{NAME}},

		Thank you for your purchase of {{PRODUCT_NAME}}.

		Your order is almost ready to go. We just need you to click the link below to complete your registration:

		{{REG_LINK}}

		Thank you for your support!

		The {{COMPANY_NAME}} team.
		---------------------------------
		{{COMPANY_NAME}}
		{{COMPANY_EMAIL}}';
	$welcome_email_default = 
		'Hello {{NAME}},

		Your registration for {{SITE_NAME}} was successful.

		If you have already created a password, you can login at any time using the information below. Otherwise, please check your inbox for a second email with instructions for creating your password.
		<div style="border: 1px solid #333333; width: 500px;">
		<table border="0" width="500" cellspacing="0" cellpadding="5">
		<tbody>
		<tr style="color: white;" bgcolor="#333333">
		<td width="200"><span style="color: #ffffff;">Username</span></td>
		<td width="200"><span style="color: #ffffff;">Login URL</span></td>
		</tr>
		<tr>
		<td width="200">{{EMAIL}}</td>
		<td width="200">{{DURL}}</td>
		</tr>
		</tbody>
		</table>
		</div>
		The {{COMPANY_NAME}} team.
		---------------------------------
		{{COMPANY_NAME}}
		{{COMPANY_EMAIL}}';
	$purchase_receipt_default =
		'<h3>{{COMPANY_NAME}} Payment Receipt</h3>
		Hello {{NAME}},

		You have successfully made a payment of {{AMOUNT}}.

		This transaction should appear on your credit card statement as {{COMPANY_NAME}}.
		<div>
		<div>
		<table border="" width="600" cellspacing="0" cellpadding="5">
		<tbody>
		<tr style="color: white;" bgcolor="#333333">
		<td width=""><span style="color: #ffffff;">DATE</span></td>
		<td width=""><span style="color: #ffffff;">PRODUCT</span></td>
		<td width=""><span style="color: #ffffff;">AMOUNT</span></td>
		<td width=""><span style="color: #ffffff;">ORDER ID</span></td>
		</tr>
		<tr>
		<td width="">{{DATE}}</td>
		<td width="">{{PRODUCT_NAME}}</td>
		<td width="">{{AMOUNT}}</td>
		<td width="">{{TXN_ID}}</td>
		</tr>
		</tbody>
		</table>
		</div>
		Thank you for your support!
		The {{COMPANY_NAME}} team

		</div>
		---------------------------------
		{{COMPANY_NAME}}
		{{COMPANY_EMAIL}}';
	$preorder_receipt_default =
		'<h3>{{COMPANY_NAME}} Payment Receipt</h3>
		Hello {{NAME}},

		This is a confirmation of your pre-order of {{PRODUCT_NAME}} for {{AMOUNT}}.

		If this is a crowdfunding project, and funding is successful, your credit card will be charged on {{END_DATE}}.

		This transaction will appear on your credit card statement as {{COMPANY_NAME}}.
		<div>
		<div>
		<table border="" width="500" cellspacing="0" cellpadding="5">
		<tbody>
		<tr style="color: white" bgcolor="#333333">
		<td width=""><span style="color: #ffffff">DATE</span></td>
		<td width=""><span style="color: #ffffff">PRODUCT</span></td>
		<td width=""><span style="color: #ffffff">AMOUNT</span></td>
		</tr>
		<tr>
		<td width="">{{DATE}}</td>
		<td width="">{{PRODUCT_NAME}}</td>
		<td width="">{{AMOUNT}}</td>
		</tr>
		</tbody>
		</table>
		</div>
		Thank you for your support!
		The {{COMPANY_NAME}} team

		</div>
		---------------------------------
		{{COMPANY_NAME}}
		{{COMPANY_EMAIL}}';
	if (function_exists('is_id_pro') && is_id_pro()) {
		$success_notification = 
			'<h3>{{PROJECT_NAME}} Has Been Successfully Funded!</h3>
			Congratulations! Thanks to the help of backers like you, {{PROJECT_NAME}} has successfully reached its funding goal.

			Your credit card will be charged for the amount of {{AMOUNT}} on {{END_DATE}}, and a receipt will be issued at that time. Please ensure that you have the necessary funds available.

			Thanks for your support!';
		$success_notification_admin =
		'<h3>Project Success Notification</h3>
		One of your projects, {{PROJECT_NAME}}, has successfully reached its funding goal. This project is set to end on {{END_DATE}}, at which point you may process credit cards and export order information.';
		$update_notification =
		'<h3>{{PROJECT_NAME}} Update</h3>
		<strong>{{UPDATE_TITLE}}</strong>

		{{UPDATE_CONTENT}}';
	}
	update_option('registration_email_default', $registration_email_default);
	update_option('welcome_email_default', $welcome_email_default);
	update_option('purchase_receipt_default', $purchase_receipt_default);
	update_option('preorder_receipt_default', $preorder_receipt_default);
	if (function_exists('is_id_licensed') && is_id_licensed()) {
		$option = get_option('idc_global_currency');
		if (empty($option)) {
			update_option('idc_global_currency', 'idcf');
		}
	}
	if (function_exists('is_id_pro') && is_id_pro()) {
		update_option('success_notification_default', $success_notification);
		update_option('success_notification_admin_default', $success_notification_admin);
		update_option('update_notification_default', $update_notification);
		$fund_type = get_option('idc_cf_fund_type');
		if (empty($fund_type)) {
			update_option('idc_cf_fund_type', 'both');
		}
	}
	/* Install Default Pages */
	$reg = array(
    	'menu_order' => 100,
    	'comment_status' => 'closed',
    	'ping_status' => 'closed',
    	'post_name' => 'membership-registration',
    	'post_status' => 'publish',
    	'post_title' => 'Membership Registration',
    	'post_type' => 'page');

    $db = array(
    	'menu_order' => 100,
    	'comment_status' => 'closed',
    	'ping_status' => 'closed',
    	'post_name' => 'dashboard',
    	'post_status' => 'publish',
    	'post_title' => 'Dashboard',
    	'post_type' => 'page',
    	'post_content' => '[memberdeck_dashboard]');

    $theme = wp_get_theme();

   	if ($theme->name == '500 Framework' || $theme->parent_theme == '500 Framework') {
   		$db['page_template'] = 'page-fullwidth.php';
   	}

    $get_reg = get_page_by_title('Membership Registration');
    $get_db = get_page_by_title('Dashboard');

    if (empty($get_reg)) {
    	$reg_page = wp_insert_post($reg);
	    if (isset($wp_error)) {
	    	echo $wp_error;
	    }
    }
    if (empty($get_db)) {
    	$d_page = wp_insert_post($db);
	    if (isset($wp_error)) {
	    	echo $wp_error;
	    }
    }
    /* Install Default Options */
	$dash_settings = get_option('md_dash_settings');
	if (empty($dash_settings)) {
		$dash_settings = array(
			'durl' => (isset($d_page) ? $d_page : $get_db->ID),
			'alayout' => 'md-featured',
			'aname' => '',
			'blayout' => 'md-featured',
			'bname' => '',
			'clayout' => 'md-featured',
			'cname' => '',
			'layout' => 1,
			'powered_by' => 1,
			'aff_link' => '',
		);
		update_option('md_dash_settings', $dash_settings);
	}
	else {
		if ($old_idc_version <= '1.5.5') {
			if (!is_array($dash_settings)) {
				$dash_settings = unserialize($dash_settings);
			}
			if (isset($get_db->ID)) {
				$dash_settings['durl'] = $get_db->ID;
				update_option('md_dash_settings', $dash_settings);
			}
		}
	}
}

// prepare deletion hooks
if (is_md_network_activated()) {
	add_action('delete_blog', 'memberdeck_uninstall', 1, 1);
	register_uninstall_hook(__FILE__,'md_remove_all_traces');
}
else {
	register_uninstall_hook(__FILE__, 'memberdeck_uninstall');
}

function md_remove_all_traces() {
	global $wpdb;
	$sql = 'SELECT * FROM '.$wpdb->base_prefix.'blogs';
	$res = $wpdb->get_results($sql);
	foreach ($res as $blog) {
		memberdeck_uninstall($blog->blog_id);
	}
}

function memberdeck_uninstall($blog_id = null) {
	global $wpdb;
	// once again, check for type of install and get proper prefixes
	$prefix = md_wpdb_prefix($blog_id);

	$sql = 'DROP TABLE IF EXISTS '.$prefix.'memberdeck_members, '.$prefix.'memberdeck_levels, '.$prefix
	.'memberdeck_credits, '.$prefix.'memberdeck_downloads, '.$prefix.'memberdeck_orders, '.$prefix.'memberdeck_preorder_tokens, '.$prefix
	.'mdid_assignments, '.$prefix.'mdid_project_levels, '.$prefix.'mdid_orders, '.$prefix.'memberdeck_keys, '.$prefix.'memberdeck_key_assoc, '.$prefix
	.'memberdeck_sc_params';
	$option = get_option('testme');
	update_option('testme', $option.', '.$sql);
	$res = $wpdb->query($sql);
	delete_option('memberdeck_gateways');
	delete_option('md_dash_settings');
	delete_option('md_receipt_settings');
	$email_defaults = array('registration_email_default', 'welcome_email_default', 'purchase_receipt_default', 'preorder_receipt_default');
	foreach ($email_defaults as $default) {
		delete_option($default);
	}
}

global $crowdfunding;

function memberdeck_styles() {
	global $permalink_structure;
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	else {
		$prefix = '?';
	}
	wp_register_script('idcommerce-js', plugins_url('js/idcommerce.js', __FILE__));
	wp_register_style('idcommerce', plugins_url('css/style.css', __FILE__));
	wp_register_style('font-awesome', "//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css");
	wp_enqueue_script('jquery');
	wp_enqueue_script('idcommerce-js');
	wp_enqueue_style('font-awesome');
	wp_register_script('stripe', 'https://js.stripe.com/v1/');
	wp_register_script('balanced', 'https://js.balancedpayments.com/v1/balanced.js');
	$ajaxurl = site_url('/wp-admin/admin-ajax.php');
	$pluginsurl = plugins_url('', __FILE__);
	$siteurl = site_url('/');
	$durl = md_get_durl();
	$settings = get_option('memberdeck_gateways');
	$gen_settings = get_option('md_receipt_settings');
	$test = '0';
	if (!empty($settings)) {
		//$settings = unserialize($settings);
		if (is_array($settings)) {
			if (isset($settings['test'])) {
				$test = (string)$settings['test'];
			}
			else {
				$test = '0';
			}
			if (isset($settings['es'])) {
				$es = $settings['es'];
			}
			else {
				$es = '0';
			}
			if (isset($settings['esc'])) {
				$esc = $settings['esc'];
			}
			else {
				$esc = '0';
			}
			if (isset($settings['epp'])) {
				$epp = $settings['epp'];
			}
			else {
				$epp = '0';
			}
			if (isset($settings['eb'])) {
				$eb = $settings['eb'];
			}
			else {
				$eb = '0';
			}
			if (isset($settings['manual_checkout'])) {
				$mc = (string) $settings['manual_checkout'];
			}
			else {
				$mc = '0';
			}
			if (isset($settings['ecb'])) {
				$ecb = $settings['ecb'];
			}
			else {
				$ecb = '0';
			}
			if (isset($settings['eauthnet'])) {
				$eauthnet = $settings['eauthnet'];
			}
			else {
				$eauthnet = '0';
			}
			if (isset($settings['eppadap'])) {
				$eppadap = $settings['eppadap'];
			}
			else {
				$eppadap = '0';
			}
			global $post;
			if (isset($post)) {
				if (strpos($post->post_content, 'memberdeck_checkout') || isset($_GET['mdid_checkout']) || isset($_GET['idc_renew'])) {
					if ($es == '1') {
						wp_enqueue_script('stripe');
					}
					if ($eb == '1') {
						wp_enqueue_script('balanced');
					}
				}
			}
			wp_localize_script('idcommerce-js', 'memberdeck_mc', $mc);
			if ($es == '1') {
				wp_localize_script( 'idcommerce-js', 'memberdeck_es', '1');
				// wp_localize_script( 'idcommerce-js', 'memberdeck_epwyw_stripe', $settings['epwyw_stripe']);
				$pk = $settings['pk'];
				$tpk = $settings['tpk'];
				if ($test == '1') {
					wp_localize_script( 'idcommerce-js', 'memberdeck_pk', $tpk);
				}
				else {
					wp_localize_script( 'idcommerce-js', 'memberdeck_pk', $pk);
				}
			}
			else {
				wp_localize_script( 'idcommerce-js', 'memberdeck_es', '0');
			}
			if ($esc == '1') {
				wp_register_style('sc_buttons', plugins_url('/lib/connect-buttons.css', __FILE__));
				wp_enqueue_style('sc_buttons');
			}
			if ($epp == '1') {
				wp_localize_script( 'idcommerce-js', 'memberdeck_epp', '1');
				$pp_email = $settings['pp_email'];
				$test_email = $settings['test_email'];
				$return_url = $settings['paypal_redirect'];
				if ($test == '1') {
					wp_localize_script('idcommerce-js', 'memberdeck_pp', $test_email);
					wp_localize_script('idcommerce-js', 'memberdeck_paypal', 'https://www.sandbox.paypal.com/cgi-bin/webscr');
				}
				else {
					wp_localize_script('idcommerce-js', 'memberdeck_pp', $pp_email);
					wp_localize_script('idcommerce-js', 'memberdeck_paypal', 'https://www.paypal.com/cgi-bin/webscr');
				}
				wp_localize_script('idcommerce-js', 'memberdeck_returnurl', $return_url);
			}
			else {
				wp_localize_script( 'idcommerce-js', 'memberdeck_epp', '0');
			}
			if ($eppadap == 1) {
				wp_localize_script('idcommerce-js', 'memberdeck_eppadap', '1');
				if ($test == '1') {
					wp_localize_script('idcommerce-js', 'memberdeck_paypal_adaptive', 'https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay');
					wp_localize_script('idcommerce-js', 'memberdeck_paypal_adaptive_preapproval', 'https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/preapproval');
				}
				else {
					wp_localize_script('idcommerce-js', 'memberdeck_paypal_adaptive', 'https://www.paypal.com/webapps/adaptivepayment/flow/pay');
					wp_localize_script('idcommerce-js', 'memberdeck_paypal_adaptive_preapproval', 'https://www.paypal.com/webapps/adaptivepayment/flow/preapproval');
				}
			}
			else {
				wp_localize_script('idcommerce-js', 'memberdeck_eppadap', '0');
			}
			if ($eb == '1') {
				wp_localize_script( 'idcommerce-js', 'memberdeck_eb', '1');
			}
			else {
				wp_localize_script( 'idcommerce-js', 'memberdeck_eb', '0');
			}
			if ($ecb == '1') {
				wp_localize_script( 'idcommerce-js', 'memberdeck_ecb', '1');
			}
			else {
				wp_localize_script( 'idcommerce-js', 'memberdeck_ecb', '0');
			}
			if ($eauthnet == '1') {
				wp_localize_script( 'idcommerce-js', 'memberdeck_eauthnet', '1');
			}
			else {
				wp_localize_script( 'idcommerce-js', 'memberdeck_eauthnet', '0');
			}
			wp_localize_script('idcommerce-js', 'memberdeck_testmode', $test);
		}
	}
	else {
		wp_localize_script( 'idcommerce-js', 'memberdeck_epp', '0');
		wp_localize_script( 'idcommerce-js', 'memberdeck_es', '0');
		wp_localize_script( 'idcommerce-js', 'memberdeck_eb', '0');
		wp_localize_script( 'idcommerce-js', 'memberdeck_mc', '0');
		wp_localize_script( 'idcommerce-js', 'memberdeck_ecb', '0');
		wp_localize_script( 'idcommerce-js', 'memberdeck_eauthnet', '0');
		wp_localize_script( 'idcommerce-js', 'memberdeck_eppadap', '0');
	}
	wp_localize_script( 'idcommerce-js', 'memberdeck_ajaxurl', $ajaxurl );
	wp_localize_script( 'idcommerce-js', 'memberdeck_siteurl', $siteurl );
	wp_localize_script( 'idcommerce-js', 'memberdeck_pluginsurl', $pluginsurl );
	wp_localize_script( 'idcommerce-js', 'memberdeck_durl', $durl);
	wp_localize_script( 'idcommerce-js', 'idc_localization_strings', apply_filters('idc_localization_strings', ''));
	wp_localize_script( 'idcommerce-js' , 'permalink_prefix', $prefix);
	wp_enqueue_style('idcommerce');
};

add_action('wp_enqueue_scripts', 'memberdeck_styles');

function memberdeck_webhook_listener() {
	global $crowdfunding;
	global $global_currency;
	global $old_db_version;
	if (isset($_POST)) {
		//$log = fopen('idmlog.txt', 'a+');
		ini_set('post_max_size', '12M');
		if (isset($_GET['memberdeck_notify']) && $_GET['memberdeck_notify'] == 'pp') {
			global $wpdb;
			// need to generate a secure key
			// need to redirect them tto registration url with that key
			//$key = md5(strtotime('now'), 

			$vars = array();

			$payment_complete = false;
			$status = null;
			
			foreach($_POST as $key=>$val) {
	           	$data = array($key => $val);

	            $vars[$key] = $val;
	            //fwrite($log, $key.' = '.$val."\n");
				if ($key == "payment_status" && strtoupper($val) == "COMPLETED") {
	                $payment_complete = true;
	                //fwrite($log, 'complete'."\n");
	            }
	            else if ($key = 'txn_type' && strtoupper($val) == 'SUBSCR_CANCEL') {
	            	$subscription_cancel = true;
	            }

	            else if ($key = 'txn_type' && strtoupper($val) == 'NEW_CASE') {
	            	if (strtoupper($vars['case_type']) == 'COMPLAINT') {
	            		$dispute = true;
	            	}
	            }
	        }
	        if ($payment_complete) {
	        	// lets get our vars
	            $fname = $vars['first_name'];
	            $lname = $vars['last_name'];
	            $price = $vars['mc_gross'];
	            $payer_email = $vars['payer_email'];
	            $email = $_GET['email'];
	            $product_id = $vars['item_number'];
	            $ipn_id = $vars['ipn_track_id'];
	            $txn_id = $vars['txn_id'];
	       		$txn_check = ID_Member_Order::check_order_exists($txn_id);
	       		if (empty($txn_check)) {
		            $level = ID_Member_Level::get_level($product_id);
		            if ($level->limit_term == '1') {
						$term_length = $level->term_length;
					}
		            if (isset($vars['txn_type']) && $vars['txn_type'] == 'subscr_payment') {
		            	$recurring = true;
		            	$sub_id = $vars['subscr_id'];
		            	//fwrite($log, 'sub id: '.$sub_id."\n");
		            }
		            else {
		            	$recurring = false;
		            }

		            $access_levels = array(absint($product_id));
		            //fwrite($log, 'id: '.$product_id."\n");
		            //fwrite($log, $email."\n");
		            // now we need to see if this user exists in our db
		            $check_user = ID_Member::check_user($email);
		            //fwrite($log, serialize($check_user)."\n");
		            if (!empty($check_user)) {
		        		//fwrite($log, 'user exists'."\n");
		            	// now we know this user exists we need to see if he is a current ID_Member
		            	$user_id = $check_user->ID;
		            	$match_user = ID_Member::match_user($user_id);
		            	if (!isset($match_user)) {
		            		//fwrite($log, 'first purchase'."\n");
		            		// not a member, this is their first purchase
		            		if ($recurring == true) {
		            			$recurring_type = $level->recurring_type;
		            			if ($recurring_type == 'weekly') {
		            				// weekly
		            				$exp = strtotime('+1 week');
		            			}
		            			else if ($recurring_type == 'monthly') {
		            				// monthly
		            				$exp = strtotime('+1 month');
		            			}
		            			else {
		            				// annually
		            				$exp = strtotime('+1 years');
									
		            			}
		            			$e_date = date('Y-m-d h:i:s', $exp);
		            			$data = array('ipn_id' => $ipn_id, 'sub_id' => $sub_id);
		            		}
		            		else if ($level->e_date == 'lifetime') {
		            			$e_date = null;
		            		}
		            		else {
		            			$exp = strtotime('+1 years');
								$e_date = date('Y-m-d h:i:s', $exp);
		            			$data = array('ipn_id' => $ipn_id);
		            		}
		            		

		            		$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $data);
							$new = ID_Member::add_user($user);
							$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price);
							$new_order = $order->add_order();
		            	}

		            	else {
		            		//fwrite($log, 'more than one purchase'."\n");
		            		// is a member, we need to push new data to their info table
		            		if (isset($match_user->access_level)) {
		            			$levels = unserialize($match_user->access_level);
		            			foreach ($levels as $key['val']) {
									$access_levels[] = absint($key['val']);
								}
		            		}

		            		if (isset($match_user->data)) {
		            			$data = unserialize($match_user->data);
		            			if (!is_array($data)) {
		            				$data = array($data);
		            			}
		            			if ($recurring == true) {
		            				$recurring_type = $level->recurring_type;
			            			if ($recurring_type == 'weekly') {
			            				// weekly
			            				$exp = strtotime('+1 week');
			            			}
			            			else if ($recurring_type == 'monthly') {
			            				// monthly
			            				$exp = strtotime('+1 month');
			            			}
			            			else {
			            				// annually
			            				$exp = strtotime('+1 years');
										
			            			}
			            			$e_date = date('Y-m-d h:i:s', $exp);
		            				$data[] = array('ipn_id' => $ipn_id, 'sub_id' => $sub_id);
		            			}
		            			else if ($level->level_type == 'lifetime') {
		            				$e_date = null;
		            			}
		            			else {
		            				$exp = strtotime('+1 years');
									$e_date = date('Y-m-d h:i:s', $exp);
		            				$data[] = array('ipn_id' => $ipn_id);
		            			}
		            		}
		            		else {
		            			if ($recurring == true) {
		            				$recurring_type = $level->recurring_type;
			            			if ($recurring_type == 'weekly') {
			            				// weekly
			            				$exp = strtotime('+1 week');
			            			}
			            			else if ($recurring_type == 'monthly') {
			            				// monthly
			            				$exp = strtotime('+1 month');
			            			}
			            			else {
			            				// annually
			            				$exp = strtotime('+1 years');
										
			            			}
			            			$e_date = date('Y-m-d h:i:s', $exp);
		            				$data[] = array('ipn_id' => $ipn_id, 'sub_id' => $sub_id);
		            			}
		            			else if ($level->e_date == 'lifetime') {
		            				$e_date = null;
		            			}
		            			else {
		            				$exp = strtotime('+1 years');
									$e_date = date('Y-m-d h:i:s', $exp);
		            				$data[] = array('ipn_id' => $ipn_id);
		            			}
		            		}

							$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $data);
							$new = ID_Member::update_user($user);
							//fwrite($log, $user_id);
							$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price);
							$new_order = $order->add_order();
		            	}
		            }
		            else {
		            	//fwrite($log, 'new user: '."\n");
		            	// user does not exist, we must create them
		            	// gen random pw they can change later
		            	$pw = idmember_pw_gen();
		            	// gen our user input
		            	$userdata = array('user_pass' => $pw,
		            		'first_name' => $fname,
		            		'last_name' => $lname,
		            		'user_login' => $email,
		            		'user_email' => $email,
		            		'display_name' => $fname);
		            	//fwrite($log, serialize($userdata));
		            	// insert user into WP db and return user id
		            	$user_id = wp_insert_user($userdata);
		            	//fwrite($log, $user_id."\n");
		            	// now add user to our member table
		            	if ($recurring == true) {
		            		$recurring_type = $level->recurring_type;
		            		//fwrite($log, 'recurring type: '.$recurring_type."\n");
	            			if ($recurring_type == 'weekly') {
	            				// weekly
	            				$exp = strtotime('+1 week');
	            			}
	            			else if ($recurring_type == 'monthly') {
	            				// monthly
	            				$exp = strtotime('+1 month');
	            			}
	            			else {
	            				// annually
	            				$exp = strtotime('+1 years');
								
	            			}
	            			$e_date = date('Y-m-d h:i:s', $exp);
							$data = array('ipn_id' => $ipn_id, 'sub_id' => $sub_id);
		            	}
		            	else if ($level->e_date == 'lifetime') {
	            			$e_date = null;
	            		}
		            	else {
		            		$exp = strtotime('+1 years');
							$e_date = date('Y-m-d h:i:s', $exp);
		            		$data = array('ipn_id' => $ipn_id);
		            	}
		            	//fwrite($log, 'exp: '.$exp."\n");
		            	$reg_key = md5($email.time());
		            	$user = array('user_id' => $user_id, 'level' => $access_levels, 'reg_key' => $reg_key, 'data' => $data);
						$new = ID_Member::add_paypal_user($user);
						//fwrite($log, $new."\n");
						$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price);
						$new_order = $order->add_order();
						//fwrite($log, 'order added: '.$new_order."\n");
						do_action('idmember_registration_email', $user_id, $reg_key, $new_order);
		            }
		            // we need to pass any extra post fields set during checkout
		            if (isset($_GET)) {
		            	$fields = $_GET;
		            }
		            else {
		            	$fields = array();
		            }
		            if (empty($reg_key)) {
		            	$reg_key = '';
		            }
		            //
		            if ($crowdfunding) {
		            	if (isset($fields['memberdeck_notify']) && $fields['memberdeck_notify'] == 'pp') {
							if (isset($fields['mdid_checkout'])) {
								$mdid_checkout = $fields['mdid_checkout'];
							}
							if (isset($fields['project_id'])) {
								$project_id = $fields['project_id'];
							}
							if (isset($fields['project_level'])) {
								$proj_level = $fields['project_level'];
							}
							$order = new ID_Member_Order($new_order);
							$order_info = $order->get_order();
							$created_at = $order_info->order_date;
							$pay_id = mdid_insert_payinfo($fname, $lname, $email, $project_id, $txn_id, $proj_level, $price, $status, $created_at);
							if (isset($pay_id)) {
								if ($recurring) {
									$start = strtotime("now");
									$mdid_id = mdid_insert_order('', $pay_id, $start, $txn_id);
								}
								else {
									$mdid_id = mdid_insert_order('', $pay_id, $new_order, null);
								}
								do_action('id_payment_success', $pay_id);
							}
						}
					}	
		            //
		            do_action('memberdeck_payment_success', $user_id, $new_order, $reg_key, $fields, 'paypal');
		            if ($recurring) {
		           		do_action('memberdeck_recurring_success', 'paypal', $user_id, $new_order, (isset($term_length) ? $term_length : null));
		           	}
		            //fwrite($log, 'user added');
		        }
	        }
	        else if (isset($subscription_cancel) && $subscription_cancel == true) {
	        	$sub_id = $vars['subscr_id'];
	        	//fwrite($log, 'subscription cancelled with id: '.$sub_id."\n");
	        	$order = new ID_Member_Order(null, null, null, null, null, $sub_id);
	        	$sub_data = $order->get_subscription($sub_id);
	        	if (!empty($sub_data)) {
	        		//fwrite($log, $sub_data->user_id."\n");
	        		$sub_id = $sub_data->subscription_id;
	        		$level_to_drop = $sub_data->level_id;
	        		$user_id = $sub_data->user_id;
	        		$match_user = ID_Member::match_user($user_id);
	        		if (isset($match_user)) {
	        			$level_array = unserialize($match_user->access_level);
	        			$key = array_search($level_to_drop, $level_array);
	        			unset($level_array[$key]);
	        			$cancel = ID_Member_Order::cancel_subscription($sub_data->id);
	        			//fwrite($log, $cancel);
	        			$data = unserialize($match_user->data);
	        			$i = 0;
	        			foreach ($data as $record) {
	        				//fwrite($log, 'record'."\n");
	        				foreach ($record as $key=>$value) {
	        					//fwrite($log, $key."\n");
	        					//fwrite($log, $value."\n");
		        				if ($value == $sub_id) {
	        						//fwrite($log, 'value = sub id'."\n");
	        						$record_id = $i;
	        						//fwrite($log, $record_id);
	        					}
	        				}
        					$i++;
	        			}
	        			if (isset($record_id)) {
	        				$cut_data = $data[$record_id];
	        				$cut_data['cancel_date'] = date('Y-m-d h:i:s');
	        				unset($data[$record_id]);
	        				$data[] = $cut_data;
	        			}
	        			$data = serialize($data);
	        			$access_level = serialize($level_array);
	        			//fwrite($log, $data."\n");
						//fwrite($log, $access_level."\n");
	        			$user = array('user_id' => $user_id, 'level' => $access_level, 'data' => $data);
	        			$update_user = ID_Member::update_user($user);
	        		}
	        	}
	        }
	        else if (isset($dispute) && $dispute == true) {
	        	$txn_id = $vars['txn_id'];
	        	$order = new ID_Member_Order(null, null, null, null, $txn_id);
	        	$transaction = $order->get_transaction();
	        	if (!empty($transaction->subscription_id)) {
	        		$sub_id = $transaction->subscription_id;
	        		$level_to_drop = $transaction->level_id;
	        		$user_id = $transaction->user_id;
	        		$match_user = ID_Member::match_user($user_id);
	        		if (isset($match_user)) {
	        			$level_array = unserialize($match_user->access_level);
	        			$key = array_search($level_to_drop, $level_array);
	        			unset($level_array[$key]);
	        			$cancel = ID_Member_Order::cancel_subscription($transaction->id);
	        			//fwrite($log, $cancel);
	        			$data = unserialize($match_user->data);
	        			$i = 0;
	        			foreach ($data as $record) {
	        				foreach ($record as $key=>$value) {
		        				if ($value == $sub_id) {
	        						$record_id = $i;
	        					}
	        				}
        					$i++;
	        			}
	        			if (isset($record_id)) {
	        				$cut_data = $data[$record_id];
	        				$cut_data['dispute_date'] = date('Y-m-d h:i:s');
	        				unset($data[$record_id]);
	        				$data[] = $cut_data;
	        			}
	        			$data = serialize($data);
	        			$access_level = serialize($level_array);
	        			$user = array('user_id' => $user_id, 'level' => $access_level, 'data' => $data);
	        			$update_user = ID_Member::update_user($user);
	        		}
	        	}
	        	else {
	        		// not a subscription, but a regular purchase
	        		$level_to_drop = $transaction->level_id;
	        		$user_id = $transaction->user_id;
	        		$match_user = ID_Member::match_user($user_id);
	        		if (isset($match_user)) {
	        			$level_array = unserialize($match_user->access_level);
	        			$key = array_search($level_to_drop, $level_array);
	        			unset($level_array[$key]);
	        			$cancel = ID_Member_Order::cancel_subscription($transaction->id);
	        			$data = unserialize($match_user->data);
	        			$data['dispute_date'] = date('Y-m-d h:i:s');
	        			$data = serialize($data);
	        			$access_level = serialize($level_array);
	        			$user = array('user_id' => $user_id, 'level' => $access_level, 'data' => $data);
	        			$update_user = ID_Member::update_user($user);
	        		}
	        	}
	        }
		}
		// Paypal Payment functions ends here #paypalpayments
		else if (isset($_GET['memberdeck_notify']) && $_GET['memberdeck_notify'] == 'stripe') {
			//fwrite($log, 'inside stripe'."\n");

			$json = @file_get_contents('php://input');
			//fwrite($log, $json."\n");

			$object = json_decode($json);
			//fwrite($log, $object->type."\n");
			if ($object->type == 'invoice.payment_succeeded') {
				$data = $object->data;
				$txn_id = $data->object->charge;
				//fwrite($log, $txn_id."\n");
				$customer = $data->object->customer;
				//fwrite($log, $customer."\n");
				$plan = $data->object->lines->data[0]->plan->id;
				$start = $data->object->lines->data[0]->period->start;
				//fwrite($log, 'start: '.$start."\n");
				//fwrite($log, $plan."\n");
				if (isset($customer)) {
					$member = ID_Member::get_customer_data($customer);
					$user_id = $member->user_id;
					$userdata = get_userdata($user_id);
					$user_email = $userdata->user_email;
					//fwrite($log, $user_id."\n");
					if (isset($user_id)) {
						$txn_check = ID_Member_Order::check_order_exists($txn_id);
						if (empty($txn_check)) {
							//fwrite($log, 'check is empty'."\n");
							$product_id = ID_Member_Level::get_level_by_plan($plan);
							//fwrite($log, $product_id."\n");
							$level = ID_Member_Level::get_level($product_id);
							$recurring_type = $level->recurring_type;
							if ($recurring_type == 'weekly') {
								// weekly
								$exp = strtotime('+1 week');
							}
							else if ($recurring_type == 'monthly') {
								// monthly
								$exp = strtotime('+1 month');
							}
							else {
								// annually
								$exp = strtotime('+1 years');
							}
							$e_date = date('Y-m-d h:i:s', $exp);
							//fwrite($log, $e_date);
							if ($level->limit_term == 1) {
								$term_length = $level->term_length;
							}
							$paykey = md5($user_email.time());
							$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, $plan, 'active', $e_date, $level->level_price);
							$new_order = $order->add_order();
							//fwrite($log, 'new order: '.$new_order."\n");
							// we need to pass any extra post fields set during checkout
							if (isset($_GET)) {
								$fields = $_GET;
							}
							else {
								$fields = array();
							}
							//
							if ($crowdfunding) {
								$user_meta = get_user_meta($user_id);
								$fname = $user_meta['first_name'][0]; // var
								$lname = $user_meta['last_name'][0]; // var
								$price = $level->level_price; // var
								$order = new ID_Member_Order($new_order);
								$the_order = $order->get_order();
								$created_at = $the_order->order_date; // var
								// txn id is null, so this won't work fug
								$check = mdid_start_check($start);
								//fwrite($log, serialize($check)."\n");
								if (!empty($check)) {
									// this is the first payment, pay id and mdid order are already set. time to update.
									$pay_id = $check->pay_info_id;
									//fwrite($log, 'pay id: '.$pay_id."\n");
									if (isset($pay_id)) {
										$mdid_order = mdid_payid_check($pay_id);
										if (isset($mdid_order)) {
											//fwrite($log, 'mdid order id: '.$mdid_order->id."\n");
											mdid_transaction_to_order($mdid_order->id, $txn_id);
											mdid_payinfo_transaction($pay_id, $txn_id);
										}
									}
								}
								else {
									// this is 2+ payments
									$order_check = mdid_order_by_customer_plan($customer, $plan);
									if (!empty($order_check)) {
										$pay_info = $order_check->pay_info_id;
										if (isset($pay_info_id)) {
											$id_order = getOrderById($pay_info_id);
											if (isset($id_order)) {
												$project_id = $id_order->product_id;
												$proj_level = $id_order->product_level;
												$pay_id = mdid_insert_payinfo($fname, $lname, $user_email, $project_id, $txn_id, $proj_level, $price, 'C', $created_at);
												$mdid_order = mdid_insert_order($customer, $pay_id, null, $plan);
												do_action('id_payment_success', $pay_id);
											}
										}
									}
								}
								//
							}
							//
							do_action('memberdeck_payment_success', $user_id, $new_order, $paykey, $fields, 'stripe');
							do_action('memberdeck_recurring_success', 'stripe', $user_id, $new_order, (isset($term_length) ? $term_length : null));
							do_action('memberdeck_stripe_success', $user_id, $email);
							do_action('idmember_receipt', $user_id, $level->level_price, $product_id, 'stripe', $new_order);
						}
					}
					
				}
			}
		}
		else if (isset($_GET['reg']) && $_GET['reg'] !== '') {
			$reg_key = $_GET['reg'];
			$user = ID_Member::retrieve_user_key($reg_key);
			//print_r($user);
			// maybe do some sort of email verification here
			if (!empty($user)) {
				$userdata = get_userdata($user->user_id);
				$url = home_url('/membership-registration').'?email='.urlencode($userdata->user_email).'&key_valid='.$reg_key;
				echo '<script>location.href="'.$url.'";</script>';
			}
		}
		else if (isset($_GET['ppsuccess']) && $_GET['ppsuccess'] == 1) {
			$settings = get_option('memberdeck_gateways');
			if (!empty($settings)) {
				if (is_array($settings) && isset($settings['paypal_redirect'])) {
					$url = $settings['paypal_redirect'];
					if (!empty($url)) {
						echo '<script>location.href="'.$url.'";</script>';
					}
				}
			}
		}
		else if (isset($_GET['coinbase_success']) && $_GET['coinbase_success'] == 1) {
			$json = @file_get_contents('php://input');

			$object = json_decode($json);

			// File writing for testing
			$filename = __('CoinbaseCallback', 'memberdeck').'-'.date('Y-m-d h-i-s').'.txt';
			$uploads = wp_upload_dir();
			$filepath = trailingslashit($uploads['basedir']).$filename;
			$baseurl = trailingslashit($uploads['baseurl']).$filename;
			file_put_contents($filepath, $json);

			$status = null;

			if (isset($object->order) && is_object($object->order)) {
				$order = $object->order;
				if ($order->status == "completed") {
					// Getting the custom variable sent using the button
					$custom = json_decode($order->custom);
					$product_id = $custom->product_id;
					if ($global_currency == 'BTC') {
						$price = sprintf('%f', floatval($order->total_btc->cents / 100000000));
					}
					else {
						$price = $order->total_native->cents / 100;
					}
					$fname = $custom->user_fname;
					$lname = $custom->user_lname;
					$email = $custom->user_email;
					$txn_id = $order->transaction->id;
					// Payment is successful
					// Checking if the level is recurring
					if (isset($order->button->subscription) && !empty($order->button->subscription)) {
						$sub_id = $order->button->id;
		            	$recurring = true;
		            }
		            else {
		            	$recurring = false;
		            }
		            // Setting the access level as array
		            $access_levels = array(absint($product_id));

		            // Getting level details, will be used later
					$level = ID_Member_Level::get_level($custom->product_id);

		            // now we need to see if this user exists in our db
		            $member = new ID_Member();
		            $check_user = $member->check_user($email);
		            $txn_check = ID_Member_Order::check_order_exists($txn_id);
		            if (empty($txn_check)) {
			            //fwrite($log, serialize($check_user)."\n");
			            if (!empty($check_user)) {
			        		//fwrite($log, 'user exists'."\n");
			            	// now we know this user exists we need to see if he is a current ID_Member
			            	$user_id = $check_user->ID;
			            	$match_user = $member->match_user($user_id);
			            	if (!isset($match_user)) {
			            		//fwrite($log, 'first purchase'."\n");
			            		// not a member, this is their first purchase
			            		if ($recurring == true) {
			            			$recurring_type = $level->recurring_type;
			            			if ($recurring_type == 'weekly') {
			            				// weekly
			            				$exp = strtotime('+1 week');
			            			}
			            			else if ($recurring_type == 'monthly') {
			            				// monthly
			            				$exp = strtotime('+1 month');
			            			}
			            			else {
			            				// annually
			            				$exp = strtotime('+1 years');
										
			            			}
			            			$e_date = date('Y-m-d h:i:s', $exp);
			            			// $data = array('ipn_id' => $ipn_id, 'sub_id' => $sub_id);
			            		}
			            		else if ($level->e_date == 'lifetime') {
			            			$e_date = null;
			            		}
			            		else {
			            			$exp = strtotime('+1 years');
									$e_date = date('Y-m-d h:i:s', $exp);
			            			// $data = array('ipn_id' => $ipn_id);
			            		}
			            		

			            		$user = array('user_id' => $user_id, 'level' => $access_levels/*, 'data' => $data*/);
								$new = ID_Member::add_user($user);
								$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price);
								$new_order = $order->add_order();
			            	}
			            	else {
			            		//fwrite($log, 'more than one purchase'."\n");
			            		// is a member, we need to push new data to their info table
			            		if (isset($match_user->access_level)) {
			            			$levels = unserialize($match_user->access_level);
			            			foreach ($levels as $key['val']) {
										$access_levels[] = absint($key['val']);
									}
			            		}

			            		// IF the data field is set and contains some data already, we need to append our new transaction data
			            		if (isset($match_user->data)) {
			            			$data = unserialize($match_user->data);
			            			if (!is_array($data)) {
			            				$data = array($data);
			            			}
			            			if ($recurring == true) {
			            				$recurring_type = $level->recurring_type;
				            			if ($recurring_type == 'weekly') {
				            				// weekly
				            				$exp = strtotime('+1 week');
				            			}
				            			else if ($recurring_type == 'monthly') {
				            				// monthly
				            				$exp = strtotime('+1 month');
				            			}
				            			else {
				            				// annually
				            				$exp = strtotime('+1 years');
											
				            			}
				            			$e_date = date('Y-m-d h:i:s', $exp);
			            				//#LATER $data[] = array('ipn_id' => $ipn_id, 'sub_id' => $sub_id);
			            			}
			            			else if ($level->level_type == 'lifetime') {
			            				$e_date = null;
			            			}
			            			else {
			            				$exp = strtotime('+1 years');
										$e_date = date('Y-m-d h:i:s', $exp);
			            				//#LATER $data[] = array('ipn_id' => $ipn_id);
			            			}
			            		}
			            		// There is no data in data field of memberdeck_members, so we will add new data only
			            		else {
			            			if ($recurring == true) {
			            				$recurring_type = $level->recurring_type;
				            			if ($recurring_type == 'weekly') {
				            				// weekly
				            				$exp = strtotime('+1 week');
				            			}
				            			else if ($recurring_type == 'monthly') {
				            				// monthly
				            				$exp = strtotime('+1 month');
				            			}
				            			else {
				            				// annually
				            				$exp = strtotime('+1 years');
											
				            			}
				            			$e_date = date('Y-m-d h:i:s', $exp);
			            				//#LATER $data[] = array('ipn_id' => $ipn_id, 'sub_id' => $sub_id);
			            			}
			            			else if ($level->e_date == 'lifetime') {
			            				$e_date = null;
			            			}
			            			else {
			            				$exp = strtotime('+1 years');
										$e_date = date('Y-m-d h:i:s', $exp);
			            				//#LATER $data[] = array('ipn_id' => $ipn_id);
			            			}
			            		}

								$user = array('user_id' => $user_id, 'level' => $access_levels/*, 'data' => $data*/);
								$new = ID_Member::update_user($user);
								//fwrite($log, $user_id);
								$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price);
								$new_order = $order->add_order();
			            	}
			            }
			            // User/Member doesn't exists so a new member will be added
			            else {
			            	//fwrite($log, 'new user: '."\n");
			            	// user does not exist, we must create them
			            	// gen random pw they can change later
			            	$pw = idmember_pw_gen();
			            	// gen our user input
			            	$userdata = array('user_pass' => $pw,
			            		'first_name' => $fname,
			            		'last_name' => $lname,
			            		'user_login' => $email,
			            		'user_email' => $email,
			            		'display_name' => $fname);
			            	//fwrite($log, serialize($userdata));
			            	// insert user into WP db and return user id
			            	$user_id = wp_insert_user($userdata);
			            	//fwrite($log, $user_id."\n");
			            	// now add user to our member table
			            	if ($recurring == true) {
			            		$recurring_type = $level->recurring_type;
			            		//fwrite($log, 'recurring type: '.$recurring_type."\n");
		            			if ($recurring_type == 'weekly') {
		            				// weekly
		            				$exp = strtotime('+1 week');
		            			}
		            			else if ($recurring_type == 'monthly') {
		            				// monthly
		            				$exp = strtotime('+1 month');
		            			}
		            			else {
		            				// annually
		            				$exp = strtotime('+1 years');
									
		            			}
		            			$e_date = date('Y-m-d h:i:s', $exp);
								//#LATER $data = array('ipn_id' => $ipn_id, 'sub_id' => $sub_id);
			            	}
			            	else if ($level->e_date == 'lifetime') {
		            			$e_date = null;
		            		}
			            	else {
			            		$exp = strtotime('+1 years');
								$e_date = date('Y-m-d h:i:s', $exp);
			            		//#LATER $data = array('ipn_id' => $ipn_id);
			            	}
			            	//fwrite($log, 'exp: '.$exp."\n");
			            	$reg_key = md5($email.time());
			            	$user = array('user_id' => $user_id, 'level' => $access_levels, 'reg_key' => $reg_key/*, 'data' => $data*/);
							$new = ID_Member::add_paypal_user($user);
							//fwrite($log, $new."\n");
							$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price);
							$new_order = $order->add_order();
							//fwrite($log, 'order added: '.$new_order."\n");
							do_action('idmember_registration_email', $user_id, $reg_key, $new_order);
			            }

			            // we need to pass any extra post fields set during checkout
						if (isset($_GET)) {
							$fields = $_GET;
						}
						else {
							$fields = array();
						}

						// If crowdfunding is enabled
						if ($crowdfunding) {
							if (isset($fields['mdid_checkout'])) {
								$mdid_checkout = $fields['mdid_checkout'];
							}
							if (isset($fields['project_id'])) {
								$project_id = $fields['project_id'];
							}
							if (isset($fields['project_level'])) {
								$proj_level = $fields['project_level'];
							}
							$order = new ID_Member_Order($new_order);
							$order_info = $order->get_order();
							$created_at = $order_info->order_date;
							$pay_id = mdid_insert_payinfo($fname, $lname, $email, $project_id, $txn_id, $proj_level, $price, $status, $created_at);
							if (isset($pay_id)) {
								if ($recurring) {
									$start = strtotime("now");
									$mdid_id = mdid_insert_order('', $pay_id, $start, $sub_id);
								}
								else {
									$mdid_id = mdid_insert_order('', $pay_id, $new_order, null);
								}
								do_action('id_payment_success', $pay_id);
							}
						}
						// Calling the actions for hooks
						do_action('memberdeck_payment_success', $user_id, $new_order, $reg_key, $fields, 'coinbase');
						if ($recurring) {
							do_action('memberdeck_recurring_success', 'coinbase', $user_id, $new_order, (isset($term_length) ? $term_length : null));
						}
					}
				}
			}
		}
		else if (isset($_GET['memberdeck_notify']) && $_GET['memberdeck_notify'] == 'pp_adaptive') {
			// fwrite($log, print_r($_POST));
			$preauth = false;
			$payment_complete = false;
			$recurring = false;
			$preauth_check = (isset($_GET['preauth_check']) ? $_GET['preauth_check'] : '');
			$vars = array();
			$plain_content = @file_get_contents('php://input');
			$plain_content = str_replace("transaction%5B0%5D", "transaction", $plain_content);
			parse_str($plain_content, $vars);
			// fwrite($log, "plain_content:\n ".$plain_content."\n");
			// fwrite($log, print_r($vars, true)."\n");
			// fwrite($log, 'GET vars:'."\n");
			// fwrite($log, print_r($_GET, true)."\n");
			// fwrite($log, "payment_complete: ".$payment_complete."\n");

			 // we need to pass any extra post fields set during checkout
            if (isset($_GET)) {
            	$fields = $_GET;
            }
            else {
            	$fields = array();
            }

			if (strtoupper($vars['status']) == "COMPLETED") {
                $payment_complete = true;
                // fwrite($log, 'complete'."\n");
                // Setting transaction id
	            if (isset($vars['preapproval_key']) && isset($vars['pay_key'])) {
					// its a completed payment of Preauth
					if ($crowdfunding) {
						$txn_id = (!empty($vars['transaction_id']) ? $vars['transaction_id'] : $vars['transaction_id_for_sender_txn']);
						$preorders = ID_Member_Order::get_md_preorders($product_id);
						// fwrite($log, '------- txn_id: '.$txn_id."\n");
						$mdid_order = mdid_orders_bycustid($vars['preapproval_key']);
						$mdid_order = $mdid_order[0];
						// fwrite($log, 'mdid_orders_bycustid: '.print_r($mdid_order, true)."\n");
						if (!empty($mdid_order)) {
							$customer_id = $mdid_order->customer_id;
							if (isset($mdid_order->pay_info_id) && $mdid_order->pay_info_id !== '') {
								$pay_id = $mdid_order->pay_info_id;
							}
						}
						// fwrite($log, '------- pay_id: '.$pay_id."\n");
	            		// Setting IDCF order as complete
	            		if (isset($pay_id)) {
							mdid_set_collected($pay_id, $txn_id);
						}
					}
	            }
            }
            // If status is active and the call is from Pre Auth then make payment as complete here except that we have W
            // instead of 'C' in pay_info
            else if (strtoupper($vars['status']) == "ACTIVE") {
            	$payment_complete = true;
            	if (!empty($preauth_check) && $preauth_check == "PREAPPROVAL-Authorization") {
					$preauth = true;
            	}
            	else {
            		$recurring = true;
					$preauth = true;
					$sub_id = $vars['preapproval_key'];
            	}
            }
            else if (strtoupper($vars['status']) == "CANCELED" && strtoupper($vars['transaction_type']) == "ADAPTIVE PAYMENT PREAPPROVAL") {
            	$subscription_cancel = true;
            }
            else if (strtoupper($vars['transaction_type']) == 'NEW_CASE') {
            	if (strtoupper($vars['case_type']) == 'COMPLAINT') {
            		$dispute = true;
            	}
            }
			
			if ($payment_complete) {
	        	// lets get our vars
	            $fname = $_GET['user_fname'];
	            $lname = $_GET['user_lname'];
	            $price = $_GET['price'];
	            $payer_email = $vars['sender_email'];
	            $email = $_GET['user_email'];
	            $product_id = $_GET['product_id'];
	            $pay_key = (isset($vars['preapproval_key']) ? $vars['preapproval_key'] : '');
	            $level = ID_Member_Level::get_level($product_id);
	            if ($level->limit_term == '1') {
					$term_length = $level->term_length;
				}
	            $store_new = true;
	            if ($preauth) {
	            	$txn_id = 'pre';
		            $prior_preorder = ID_Member_Order::get_preorder_by_token($pay_key);
		            if (!empty($prior_preorder)) {
		            	$prior_order_obj = new ID_Member_Order($prior_preorder->order_id);
		            	$prior_order = $prior_order_obj->get_order();
		            	if (!empty($prior_order)) {
		            		$store_new = false;
		            		// reserved for future use
		            		$prior_order_status = $prior_order->status;
		            		$prior_order_txn = $prior_order->transaction_id;
		            	}
		            }
	            } else if ($recurring) {
	            	$txn_id = $sub_id;
	            	$prior_sub = ID_Member_Order::get_subscription_by_sub($sub_id);
	            	if (!empty($prior_sub)) {
	            		$store_new = false;
	            	}
	            } else {
	            	$txn_id = $vars['transaction_id'];
	            	$prior_order = ID_Member_Order::check_order_exists($txn_id);
	            	if (!empty($prior_order)) {
	            		// this may not ever happen? 
	            		$store_new = false;
	            	}
	            	else {
	            		// is still in pre status
	            		$prior_preorder = ID_Member_Order::get_preorder_by_token($pay_key);
			            if (!empty($prior_preorder)) {
			            	$prior_order_obj = new ID_Member_Order($prior_preorder->order_id);
			            	$prior_order = $prior_order_obj->get_order();
			            	if (!empty($prior_order) && $prior_order->transaction_id == 'pre') {
			            		$store_new = false;
			            		// reserved for future use
			            		$prior_order_status = $prior_order->status;
			            	}
			            }
	            	}
	            }

	            $access_levels = array(absint($product_id));
	            //fwrite($log, 'id: '.$product_id."\n");
	            //fwrite($log, $email."\n");
	            // now we need to see if this user exists in our db
	            $ID_Member = new ID_Member();
	            $check_user = $ID_Member->check_user($email);
	            //fwrite($log, serialize($check_user)."\n");
	            if (!empty($check_user) && $store_new) {
	        		//fwrite($log, 'user exists'."\n");
	            	// now we know this user exists we need to see if he is a current ID_Member
	            	$user_id = $check_user->ID;
	            	$match_user = $ID_Member->match_user($user_id);
	            	if (!isset($match_user)) {
	            		//fwrite($log, 'first purchase'."\n");
	            		// not a member, this is their first purchase
	            		if ($recurring == true) {
	            			$recurring_type = $level->recurring_type;
	            			if ($recurring_type == 'weekly') {
	            				// weekly
	            				$exp = strtotime('+1 week');
	            			}
	            			else if ($recurring_type == 'monthly') {
	            				// monthly
	            				$exp = strtotime('+1 month');
	            			}
	            			else {
	            				// annually
	            				$exp = strtotime('+1 years');
								
	            			}
	            			$e_date = date('Y-m-d h:i:s', $exp);
	            			$data = array('pay_key' => $pay_key, 'sub_id' => $sub_id);
	            		}
	            		else if ($level->e_date == 'lifetime') {
	            			$e_date = null;
	            		}
	            		else {
	            			$exp = strtotime('+1 years');
							$e_date = date('Y-m-d h:i:s', $exp);
	            			$data = array('pay_key' => $pay_key);
	            		}
	            		
	            		// does this ever happen?
	            		// not a duplicate because user is in wp_users but not in memberdeck table
	            		$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $data);
						$new = ID_Member::add_user($user);
						$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, (isset($sub_id) ? $sub_id : ''), 'active', $e_date, $price);
						$new_order = $order->add_order();
	            	}

	            	else {
	            		//fwrite($log, 'more than one purchase'."\n");
	            		// is a member, we need to push new data to their info table
	            		if (isset($match_user->access_level)) {
	            			$levels = unserialize($match_user->access_level);
	            			foreach ($levels as $key['val']) {
								$access_levels[] = absint($key['val']);
							}
	            		}

	            		if (isset($match_user->data)) {
	            			$data = unserialize($match_user->data);
	            			if (!is_array($data)) {
	            				$data = array($data);
	            			}
	            			if ($recurring == true) {
	            				$recurring_type = $level->recurring_type;
		            			if ($recurring_type == 'weekly') {
		            				// weekly
		            				$exp = strtotime('+1 week');
		            			}
		            			else if ($recurring_type == 'monthly') {
		            				// monthly
		            				$exp = strtotime('+1 month');
		            			}
		            			else {
		            				// annually
		            				$exp = strtotime('+1 years');
									
		            			}
		            			$e_date = date('Y-m-d h:i:s', $exp);
	            				$data[] = array('pay_key' => $pay_key, 'sub_id' => $sub_id);
	            			}
	            			else if ($level->level_type == 'lifetime') {
	            				$e_date = null;
	            			}
	            			else {
	            				$exp = strtotime('+1 years');
								$e_date = date('Y-m-d h:i:s', $exp);
	            				$data[] = array('pay_key' => $pay_key);
	            			}
	            		}
	            		else {
	            			if ($recurring == true) {
	            				$recurring_type = $level->recurring_type;
		            			if ($recurring_type == 'weekly') {
		            				// weekly
		            				$exp = strtotime('+1 week');
		            			}
		            			else if ($recurring_type == 'monthly') {
		            				// monthly
		            				$exp = strtotime('+1 month');
		            			}
		            			else {
		            				// annually
		            				$exp = strtotime('+1 years');
									
		            			}
		            			$e_date = date('Y-m-d h:i:s', $exp);
	            				$data[] = array('pay_key' => $pay_key, 'sub_id' => $sub_id);
	            			}
	            			else if ($level->e_date == 'lifetime') {
	            				$e_date = null;
	            			}
	            			else {
	            				$exp = strtotime('+1 years');
								$e_date = date('Y-m-d h:i:s', $exp);
	            				$data[] = array('pay_key' => $pay_key);
	            			}
	            		}
						$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $data);
						$new = ID_Member::update_user($user);
						//fwrite($log, $user_id);
						$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, (isset($sub_id) ? $sub_id : ''), 'active', $e_date, $price);
						$new_order = $order->add_order();
	            	}

	            	// Adding pre-auth order
	            	if (isset($preauth) && $preauth == true) {
						//echo 'sending a preorder';
						$preorder_entry = ID_Member_Order::add_preorder($new_order, $pay_key, 'pp-adaptive');
						do_action('memberdeck_preauth_receipt', $user_id, $price, $product_id, 'pp-adaptive', $new_order);
						do_action('memberdeck_preauth_success', $user_id, $new_order, $txn_id, $fields, 'pp-adaptive');
					}
	            }
	            else if ($store_new) {
	            	// users first purchase via paypal, which does not require login info
	            	//fwrite($log, 'new user: '."\n");
	            	// user does not exist, we must create them
	            	// gen random pw they can change later
	            	$pw = idmember_pw_gen();
	            	// gen our user input
	            	$userdata = array('user_pass' => $pw,
	            		'first_name' => $fname,
	            		'last_name' => $lname,
	            		'user_login' => $email,
	            		'user_email' => $email,
	            		'display_name' => $fname);
	            	//fwrite($log, serialize($userdata));
	            	// insert user into WP db and return user id
	            	$user_id = wp_insert_user($userdata);
	            	//fwrite($log, $user_id."\n");
	            	// now add user to our member table
	            	if ($recurring == true) {
	            		$recurring_type = $level->recurring_type;
	            		//fwrite($log, 'recurring type: '.$recurring_type."\n");
            			if ($recurring_type == 'weekly') {
            				// weekly
            				$exp = strtotime('+1 week');
            			}
            			else if ($recurring_type == 'monthly') {
            				// monthly
            				$exp = strtotime('+1 month');
            			}
            			else {
            				// annually
            				$exp = strtotime('+1 years');
							
            			}
            			$e_date = date('Y-m-d h:i:s', $exp);
						$data = array('pay_key' => $pay_key, 'sub_id' => $sub_id);
	            	}
	            	else if ($level->e_date == 'lifetime') {
            			$e_date = null;
            		}
	            	else {
	            		$exp = strtotime('+1 years');
						$e_date = date('Y-m-d h:i:s', $exp);
	            		$data = array('pay_key' => $pay_key);
	            	}
	            	//fwrite($log, 'exp: '.$exp."\n");
	            	$reg_key = md5($email.time());
	            	$user = array('user_id' => $user_id, 'level' => $access_levels, 'reg_key' => $reg_key, 'data' => $data);
					$new = ID_Member::add_paypal_user($user);
					//fwrite($log, $new."\n");
					$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, (isset($sub_id) ? $sub_id : ''), 'active', $e_date, $price);
					$new_order = $order->add_order();
					//fwrite($log, 'order added: '.$new_order."\n");
					
					// Adding pre-auth order
	            	if (isset($preauth) && $preauth == true) {
	            		$preorder_entry = ID_Member_Order::add_preorder($new_order, $pay_key, 'pp-adaptive');
						do_action('memberdeck_preauth_receipt', $user_id, $price, $product_id, 'pp-adaptive', $new_order);
						do_action('memberdeck_preauth_success', $user_id, $new_order, $txn_id, $fields, 'pp-adaptive');
	            	}
					do_action('idmember_registration_email', $user_id, $reg_key, $new_order);
	            }
	            if ($store_new) {
		            if (empty($reg_key)) {
		            	$reg_key = '';
		            }
		            //
	            	// fwrite($log, 'crowdfunding: '.$crowdfunding."\n");
		            if ($crowdfunding) {
						if (isset($fields['mdid_checkout'])) {
							$mdid_checkout = $fields['mdid_checkout'];
						}
						if (isset($fields['project_id'])) {
							$project_id = $fields['project_id'];
						}
						if (isset($fields['project_level'])) {
							$proj_level = $fields['project_level'];
						}
	            		// fwrite($log, 'product_id: '.$product_id."\n");
	            		// fwrite($log, 'proj_level: '.$proj_level."\n");
						if (isset($project_id) && !empty($project_id) && isset($proj_level)) {
							$order = new ID_Member_Order($new_order);
							$order_info = $order->get_order();
							$created_at = $order_info->order_date;
							if ($preauth) {
								$status = 'W';
							} else {
			        			// we need to update the IDCF order
			        			$status = 'C';
							}
							$pay_id = mdid_insert_payinfo($fname, $lname, $email, $project_id, $txn_id, $proj_level, $price, $status, $created_at);
							if (isset($pay_id)) {
								if ($recurring) {
									$start = strtotime("now");
									$mdid_id = mdid_insert_order($pay_key, $pay_id, $new_order, $sub_id);
								}
								else {
									$mdid_id = mdid_insert_order($pay_key, $pay_id, $new_order, null);
								}
								do_action('id_payment_success', $pay_id);
							}
						}
					}	
		            //
		            do_action('memberdeck_payment_success', $user_id, $new_order, $reg_key, $fields, 'paypal');
		            if ($recurring) {
		            	if ($preauth) {
		            		$new_sub = new ID_Member_Subscription(null, $user_id, $level->id, $sub_id, 'paypal');
							$filed_sub = $new_sub->add_subscription();
							$item = new stdClass();
			            	$item->product_id = $product_id;
			            	$item->price = $price;
			            	$item->first_name = $fname;
			            	$item->last_name = $lname;
			            	$item->email = $email;
			            	$item->key = $pay_key;
			            	$response = adaptive_pay_request($item, $fields);
			            	update_option('adaptive_response', $reponse);
						}
						else {
							update_option('term_length', $level->term_length);
			            	do_action('memberdeck_recurring_success', 'adaptive', $user_id, $new_order, (isset($level->term_length) ? $level->term_length : null));
			            }
		            }
		            //fwrite($log, 'user added');
		        }
		        else {
		        	// reserved for future use
		        }
	        }
	        else if (isset($subscription_cancel) && $subscription_cancel == true) {
	        	// we shouldn't see this because we aren't doing subscriptions, but keep as a watch this
	        	$sub_id = $vars['preapproval_key'];
	        	//fwrite($log, 'subscription cancelled with id: '.$sub_id."\n");
	        	$order = new ID_Member_Order(null, null, null, null, null, $sub_id);
	        	$sub_data = $order->get_subscription($sub_id);
	        	if (!empty($sub_data)) {
	        		//fwrite($log, $sub_data->user_id."\n");
	        		$sub_id = $sub_data->subscription_id;
	        		$level_to_drop = $sub_data->level_id;
	        		$user_id = $sub_data->user_id;
	        		$match_user = ID_Member::match_user($user_id);
	        		if (isset($match_user)) {
	        			$level_array = unserialize($match_user->access_level);
	        			$key = array_search($level_to_drop, $level_array);
	        			unset($level_array[$key]);
	        			$cancel = ID_Member_Order::cancel_subscription($sub_data->id);
	        			//fwrite($log, $cancel);
	        			$data = unserialize($match_user->data);
	        			$i = 0;
	        			foreach ($data as $record) {
	        				//fwrite($log, 'record'."\n");
	        				foreach ($record as $key=>$value) {
	        					//fwrite($log, $key."\n");
	        					//fwrite($log, $value."\n");
		        				if ($value == $sub_id) {
	        						//fwrite($log, 'value = sub id'."\n");
	        						$record_id = $i;
	        						//fwrite($log, $record_id);
	        					}
	        				}
        					$i++;
	        			}
	        			if (isset($record_id)) {
	        				$cut_data = $data[$record_id];
	        				$cut_data['cancel_date'] = date('Y-m-d h:i:s');
	        				unset($data[$record_id]);
	        				$data[] = $cut_data;
	        			}
	        			$data = serialize($data);
	        			$access_level = serialize($level_array);
	        			//fwrite($log, $data."\n");
						//fwrite($log, $access_level."\n");
	        			$user = array('user_id' => $user_id, 'level' => $access_level, 'data' => $data);
	        			$update_user = ID_Member::update_user($user);
	        		}
	        	}
	        }
	        else if (isset($dispute) && $dispute == true) {
	        	$txn_id = $vars['transaction_id'];
	        	$order = new ID_Member_Order(null, null, null, null, $txn_id);
	        	$transaction = $order->get_transaction();
	        	if (!empty($transaction->subscription_id)) {
	        		$sub_id = $transaction->subscription_id;
	        		$level_to_drop = $transaction->level_id;
	        		$user_id = $transaction->user_id;
	        		$match_user = ID_Member::match_user($user_id);
	        		if (isset($match_user)) {
	        			$level_array = unserialize($match_user->access_level);
	        			$key = array_search($level_to_drop, $level_array);
	        			unset($level_array[$key]);
	        			$cancel = ID_Member_Order::cancel_subscription($transaction->id);
	        			//fwrite($log, $cancel);
	        			$data = unserialize($match_user->data);
	        			$i = 0;
	        			foreach ($data as $record) {
	        				foreach ($record as $key=>$value) {
		        				if ($value == $sub_id) {
	        						$record_id = $i;
	        					}
	        				}
        					$i++;
	        			}
	        			if (isset($record_id)) {
	        				$cut_data = $data[$record_id];
	        				$cut_data['dispute_date'] = date('Y-m-d h:i:s');
	        				unset($data[$record_id]);
	        				$data[] = $cut_data;
	        			}
	        			$data = serialize($data);
	        			$access_level = serialize($level_array);
	        			$user = array('user_id' => $user_id, 'level' => $access_level, 'data' => $data);
	        			$update_user = ID_Member::update_user($user);
	        		}
	        	}
	        	else {
	        		// not a subscription, but a regular purchase
	        		$level_to_drop = $transaction->level_id;
	        		$user_id = $transaction->user_id;
	        		$match_user = ID_Member::match_user($user_id);
	        		if (isset($match_user)) {
	        			$level_array = unserialize($match_user->access_level);
	        			$key = array_search($level_to_drop, $level_array);
	        			unset($level_array[$key]);
	        			$cancel = ID_Member_Order::cancel_subscription($transaction->id);
	        			$data = unserialize($match_user->data);
	        			$data['dispute_date'] = date('Y-m-d h:i:s');
	        			$data = serialize($data);
	        			$access_level = serialize($level_array);
	        			$user = array('user_id' => $user_id, 'level' => $access_level, 'data' => $data);
	        			$update_user = ID_Member::update_user($user);
	        		}
	        	}
	        }
			
		}
		//fwrite($log, 'booyah');
		//fclose($log);
	}
}

add_action('init', 'memberdeck_webhook_listener');

add_action('init', 'memberdeck_disable_others', 1);

function memberdeck_disable_others() {
	$get_array = array('payment_settings', 'backer_profile', 'edit-profile', 'creator_projects', 'mdid_checkout');
	if (isset($_GET['action']) && $_GET['action'] == 'register') {
		if (class_exists('WPSEO_OpenGraph')) {
			remove_action('init', 'initialize_wpseo_front');
		}
		add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );
		remove_filter('the_content', 'wpautop');
	}
	else if (isset($_GET['key_valid']) && isset($_GET['email'])) {
		remove_filter('the_content', 'wpautop');
	}
	foreach ($get_array as $get) {
		if (isset($_GET[$get])) {
			if (class_exists('WPSEO_OpenGraph')) {
				remove_action('init', 'initialize_wpseo_front');
			}
			add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );
		}
	}
}

add_filter('the_content', 'idmember_registration_form', 1);

function idmember_registration_form($content) {
	if (isset($_GET['key_valid']) && isset($_GET['email'])) {
		$reg_key = $_GET['key_valid'];
		$email = urldecode($_GET['email']);
		$user = ID_Member::retrieve_user_key($reg_key);
		$member = new ID_Member();
		$check_user = $member->check_user($email);

		if (isset($user) && isset($check_user) && $check_user->ID == $user->user_id) {
			$valid = true;
		}
		else {
			$valid = false;
		}
		if ($valid == true) {
			ob_start();
			$user_id = $user->user_id;
			$current_user = get_userdata($user_id);
			$user_firstname = $current_user->user_firstname;
			$user_lastname = $current_user->user_lastname;
			$extra_fields = null;
			include_once 'templates/_regForm.php';
			$content = ob_get_contents();
			ob_end_clean();
			do_action('memberdeck_reg_form', $user_id);
			return $content;
		}
		else {
			$durl = md_get_durl();
            echo '<script>window.location="'.$durl.'";</script>';
		}
	}
	else if (isset($_GET['action']) && $_GET['action'] == 'register') {
		if (!is_user_logged_in()) {
			ob_start();
			include_once 'templates/_regForm.php';
			$content = ob_get_contents();
			ob_end_clean();
		}
		else {
			$durl = md_get_durl();
            echo '<script>window.location="'.$durl.'";</script>';
		}
	}
	return $content;
}

/**
 * PayPal Adaptive the_content filter to close the embedded box using javascript
 */
function ppadap_webhook_content($content) {
	$content .= '<div id="idc_ppadap_return"></div>';
	return $content;
}
add_action('init', 'ppadap_webhook_content_check');

function ppadap_webhook_content_check() {
	if ((isset($_GET['ppadap_success']) && $_GET['ppadap_success']) || (isset($_GET['ppadap_cancel'])  && $_GET['ppadap_cancel'] == 1)) {
		add_filter('the_content', 'ppadap_webhook_content');
	}
}

add_action('init', 'md_export_handler');

function md_export_handler() {
	//global $phpmailer;
	//print_r($phpmailer);
	if (isset($_POST['export_customers'])) {
		$product_id = absint($_POST['export_product_choice']);
		$force_download = ID_Member::export_members($product_id);
	}
}

function md_s3_enabled() {
	// a function to see if any downloads are using S3
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_downloads WHERE enable_s3 = %d LIMIT 1', absint(1));
	$res = $wpdb->get_row($sql);
	if (!empty($res)) {
		return true;
	}
	else {
		return false;
	}
}
?>