<?php

add_action('admin_menu', 'memberdeck_add_menus', 11);

function memberdeck_add_menus() {
	//if (current_user_can('manage_options')) {
		$settings = add_menu_page(__('IDC', 'memberdeck'), 'IDC', 'idc_manage_products', 'idc', 'idc_settings', plugins_url( '/images/ignitiondeck-menu.png', __FILE__ ));
		//$settings = add_submenu_page('options-general.php', 'MemberDeck', 'MemberDeck', 'manage_options', 'memberdeck-settings', 'memberdeck_settings');
		$users = add_submenu_page('idc', __('Members', 'memberdeck'), __('Members', 'memberdeck'), 'idc_manage_members', 'idc-users', 'idc_users');
		$orders = add_submenu_page('idc', __('Orders', 'memberdeck'), __('Orders', 'memberdeck'), 'idc_manage_orders', 'idc-orders', 'idc_orders');
		$payments = add_submenu_page('idc', __('Gateways', 'memberdeck'), __('Gateways', 'memberdeck'), 'idc_manage_gateways', 'idc-gateways', 'idc_gateways');
		$email = add_submenu_page('idc', __('Email', 'memberdeck'), __('Email', 'memberdeck'), 'idc_manage_email', 'idc-email', 'idc_email');
		add_action('admin_print_styles-'.$email, 'idc_email_scripts');
		global $crowdfunding;
		if ($crowdfunding) {
			$bridge_settings = add_submenu_page('idc', __('Crowdfunding', 'mdid'), __('Crowdfunding', 'mdid'), 'idc_manage_crowdfunding', 'idc-bridge-settings', 'idc_bridge_settings');
			add_action('admin_print_styles-'.$bridge_settings, 'mdid_admin_scripts');
			if (is_id_pro()) {
				$enterprise_settings = add_submenu_page('idc', __('Enterprise Settings', 'mdid'), __('Enterprise Settings', 'mdid'), 'idc_manage_gateways', 'idc-enterprise-settings', 'idc_enterprise_settings');
				add_action('admin_print_styles-'.$enterprise_settings, 'md_sc_scripts');
			}
		}
		$gateways = get_option('memberdeck_gateways');
		if (isset($gateways)) {
			if (!is_array($gateways)) {
				$gateways = unserialize($gateways);
			}
			if (isset($gateways['esc']) && $gateways['esc'] == 1) {
				$sc_menu = add_submenu_page('idc', __('Stripe Connect', 'mdid'), __('Stripe Connect', 'mdid'), 'idc_manage_gateways', 'idc-sc-settings', 'idc_sc_settings');
				add_action('admin_print_styles-'.$sc_menu, 'md_sc_scripts');
			}
			else if (isset($gateways['ebm']) && $gateways['ebm'] == 1) {
				$sc_menu = add_submenu_page('idc', __('Balanced Marketplaces', 'mdid'), __('Balanced Marketplaces', 'mdid'), 'idc_manage_gateways', 'idc-bm-settings', 'idc_bm_settings');
				add_action('admin_print_styles-'.$sc_menu, 'md_bm_scripts');
			}
		}
		global $s3;
		if ($s3) {
			$s3_menu = add_submenu_page('idc', __('S3 Settings', 'mdid'), __('S3 Settings', 'mdid'), 'idc_manage_extensions', 'idc-s3-settings', 'idc_s3_settings');
		}
	//}
}

function idc_settings() {
	//$levels = idmember_get_levels();
	global $crowdfunding;
	$gateways = get_option('memberdeck_gateways', true);
	if (!empty($gateways)) {
		$es = $gateways['es'];
		$eb = $gateways['eb'];
		$epp = $gateways['epp'];
	}
	if (isset($_POST['level-submit'])) {
		//$product_type = esc_attr($_POST['product-type']);
		$product_type = (!empty($_POST['product-type']) ? sanitize_text_field($_POST['product-type']) : 'purchase');
		$name = sanitize_text_field($_POST['level-name']);
		$price = sanitize_text_field(str_replace(',', '', $_POST['level-price']));
		$credit = absint($_POST['credit-value']);
		$txn_type = sanitize_text_field($_POST['txn-type']);
		$license_type = sanitize_text_field($_POST['level-type']);
		if ($license_type == 'recurring') {
			$recurring = sanitize_text_field($_POST['recurring-type']);
		}
		else {
			$recurring = 'none';
		}
		if (isset($_POST['limit_term'])) {
			$limit_term = absint($_POST['limit_term']);
		}
		else {
			$limit_term = 0;
		}
		$term_length = sanitize_text_field($_POST['term_length']);
		$plan = sanitize_text_field($_POST['plan']);
		$license_count = sanitize_text_field($_POST['license-count']);
		if (isset($_POST['enable_renewals'])) {
			$enable_renewals = absint($_POST['enable_renewals']);
		}
		else {
			$enable_renewals = 0;
		}
		$renewal_price = sanitize_text_field($_POST['renewal_price']);
		if (isset($_POST['enable_multiples'])) {
			$enable_multiples = absint($_POST['enable_multiples']);
		}
		else {
			$enable_multiples = 0;
		}
		if (isset($_POST['create_page'])) {
			$create_page = absint($_POST['create_page']);
		}
		else {
			$create_page = 0;
		}
		$level = apply_filters('idc_level_data', array('product_type' => $product_type,
			'level_name' => $name,
			'level_price' => $price,
			'credit_value' => $credit,
			'txn_type' => $txn_type,
			'level_type' => $license_type,
			'recurring_type' => $recurring,
			'limit_term' => $limit_term,
			'term_length' => $term_length,
			'plan' => $plan,
			'license_count' => $license_count,
			'enable_renewals' => $enable_renewals,
			'renewal_price' => $renewal_price,
			'enable_multiples' => $enable_multiples,
			'create_page' => $create_page), 'admin');

		if (sanitize_text_field($_POST['level-submit']) == 'Create') {
			$level_create = new ID_Member_Level();
			$new = $level_create->add_level($level);
			$level_id = $new['level_id'];
			$post_id = $new['post_id'];
			do_action('idc_product_create', $level, $level_id);
			echo '<div id="message" class="updated">'.__('Level Created', 'memberdeck').($create_page ? ' | <a href="'.get_edit_post_link($post_id).'">'.__('Edit Checkout Page', 'memberdeck').'</a>' : '').'</div>';
		}
		else if ($_POST['level-submit'] == 'Update') {
			$level['level_id'] = absint($_POST['edit-level']);
			$level_update = ID_Member_Level::update_level($level);
			$product_type = '';
			$name = '';
			$price = '';
			$credit = 0;
			$txn_type = '';
			$license_type = '';
			$recurring = '';
			$limit_term = 0;
			$term_length = '';
			$plan = '';
			$license_count = '';
			$enable_renewals = 0;
			$renewal_price = '';
			$enable_multiples = 0;
			do_action('idc_product_update', $level, $level['level_id']);
			echo '<div id="message" class="updated">'.__('Level Saved', 'memberdeck').'</div>';
		}
		
	}

	if (isset($_POST['level-delete'])) {
		$level = array('level_id' => absint($_POST['edit-level']));
		do_action('idc_before_delete_level', $level['level_id']);
		$delete_level = ID_Member_Level::delete_level($level);
		do_action('idc_delete_level', $level['level_id']);
		$name = '';
		$price = '';
		echo '<div id="message" class="updated">'.__('Level Deleted', 'memberdeck').'</div>';
	}

	if (isset($_POST['credit-submit'])) {
		$credit_name = sanitize_text_field($_POST['credit-name']);
		$credit_price = sanitize_text_field($_POST['credit-price']);
		$credit_count = sanitize_text_field($_POST['credit-count']);
		$credit = array('credit_name' => $credit_name,
			'credit_price' => $credit_price,
			'credit_count' => $credit_count);
		if (isset($_POST['credit-assign'])) {
			$credit['credit_level'] = absint($_POST['credit-assign']);
		}
		else {
			$credit['credit_level'] = '';
		}
		if (sanitize_text_field($_POST['credit-submit']) == 'Create') {
			$credit_create = new ID_Member_Credit($credit);
			$credit_create->add_credit();
		}
		else if (sanitize_text_field($_POST['credit-submit']) == 'Update') {
			$credit['credit_id'] = absint($_POST['edit-credit']);
			$credit_update = ID_Member_Credit::update_credit($credit);
			$credit_name = '';
			$credit_price = '';
			$credit_count = '';
		}
		echo '<div id="message" class="updated">'.__('Credit Saved', 'memberdeck').'</div>';
	}

	if (isset($_POST['download-submit'])) {
		$download_name = sanitize_text_field($_POST['download-name']);
		$version = sanitize_text_field($_POST['download-version']);
		if (isset($_POST['enable_occ'])) {
			$enable_occ = absint($_POST['enable_occ']);
		}
		else {
			$enable_occ = 0;
		}
		if (isset($_POST['hidden'])) {
			$hidden = absint($_POST['hidden']);
		}
		else {
			$hidden = 0;
		}
		if (isset($_POST['enable_s3'])) {
			$enable_s3 = absint($_POST['enable_s3']);
		}
		else {
			$enable_s3 = 0;
		}
		if (isset($_POST['occ_level'])) {
			$occ_level = absint($_POST['occ_level']);
		}
		else {
			$occ_level = null;
		}
		if (isset($_POST['id_project'])) {
			$id_project = absint($_POST['id_project']);
		}
		else {
			$id_project = null;
		}
		$position = sanitize_text_field($_POST['dash-position']);
		$licensed = sanitize_text_field($_POST['licensed']);
		$levels = array();
		if (isset($_POST['lassign'])) {
			foreach ($_POST['lassign'] as $lassign) {
				$levels[] = $lassign;
			}
		}
		$dlink = sanitize_text_field($_POST['download-link']);
		$ilink = sanitize_text_field($_POST['info-link']);
		$doclink = sanitize_text_field($_POST['doc-link']);
		$imagelink = sanitize_text_field($_POST['image-link']);
		$button_text = sanitize_text_field($_POST['button-text']);

		if (sanitize_text_field($_POST['download-submit']) == 'Create') {
			$download_create = new ID_Member_Download(
			null,
			$download_name,
			$version,
			$hidden,
			$enable_s3,
			$enable_occ,
			$occ_level,
			$id_project,
			$position,
			$licensed,
			$levels, 
			$dlink, 
			$ilink, 
			$doclink,
			$imagelink,
			$button_text);
			$download_create->add_download();
		}
		else if (sanitize_text_field($_POST['download-submit']) == 'Update') {
			$id = sanitize_text_field($_POST['edit-download']);
			$download = new ID_Member_Download(
				$id,
				$download_name,
				$version,
				$hidden,
				$enable_s3,
				$enable_occ,
				$occ_level,
				$id_project,
				$position,
				$licensed,
				$levels, 
				$dlink, 
				$ilink, 
				$doclink,
				$imagelink,
				$button_text
			);
			$check_dl = $download->get_download();
			if (isset($check_dl->levels)) {
				$old_levels = unserialize($download->download_levels);
				foreach ($old_levels as $new) {
					if (!in_array($new, $levels)) {
						$levels[] = $new;
					}
				}
			}
			$download->update_download();
		}
		echo '<div id="message" class="updated">'.__('Download Saved', 'memberdeck').'</div>';
	}
	if (isset($_POST['download-delete'])) {
		$download_id = sanitize_text_field($_POST['edit-download']);
		ID_Member_Download::delete_download($download_id);
		unset($_POST);
		echo '<div id="message" class="updated">'.__('Download Deleted', 'memberdeck').'</div>';
	}
	if (isset($_POST['credit-delete'])) {
			$credit = array('credit_id' => sanitize_text_field($_POST['edit-credit']));
			$delete_credit = ID_Member_Credit::delete_credit($credit);
			$name = '';
			$price = '';
			echo '<div id="message" class="updated">'.__('Credit Deleted', 'memberdeck').'</div>';
	}
	$dash = get_option('md_dash_settings');
	if (!empty($dash)) {
		if (!is_array($dash)) {
			$dash = unserialize($dash);
		}
		if (isset($dash['durl'])) {
			$durl = $dash['durl'];
		}
		else {
			$durl = home_url('/dashboard');
		}
		if (isset($dash['alayout'])) {
			$alayout = $dash['alayout'];
		}
		else {
			$alayout = 'md-featured';
		}
		$aname = $dash['aname'];
		if (isset($dash['blayout'])) {
			$blayout = $dash['blayout'];
		}
		else {
			$blayout = 'md-featured';
		}
		$bname = $dash['bname'];
		if (isset($dash['clayout'])) {
			$clayout = $dash['clayout'];
		}
		else {
			$clayout = 'md-featured';
		}
		$cname = $dash['cname'];
		if (isset($dash['layout'])) {
			$layout = $dash['layout'];
		}
		else {
			$layout = 1;
		}
		if (isset($dash['powered_by'])) {
			$powered_by = $dash['powered_by'];
		}
		else {
			$powered_by = 1;
		}
		if (isset($dash['aff_link'])) {
			$aff_link = $dash['aff_link'];
		}
		else {
			$aff_link = '';
		}
	}
	$d_pages = get_pages(array('sort_column' => 'post_title', 'sort_order' => 'asc'));
	if (isset($_POST['dash-submit'])) {
		$durl = sanitize_text_field($_POST['durl']);
		$alayout = sanitize_text_field($_POST['a-layout']);
		$aname = sanitize_text_field($_POST['a-name']);
		$blayout = sanitize_text_field($_POST['b-layout']);
		$bname = sanitize_text_field($_POST['b-name']);
		$clayout = sanitize_text_field($_POST['c-layout']);
		$cname = sanitize_text_field($_POST['c-name']);
		$layout = sanitize_text_field($_POST['layout-select']);
		if (isset($_POST['powered_by'])) {
			$powered_by = absint($_POST['powered_by']);
		}
		else {
			$powered_by = 0;
		}
		$aff_link = sanitize_text_field($_POST['aff_link']);
		$dash = array('durl' => $durl, 'alayout' => $alayout, 'aname' => $aname, 'blayout' => $blayout, 'bname' => $bname, 'clayout' => $clayout, 'cname' => $cname, 'layout' => $layout, 'powered_by' => $powered_by, 'aff_link' => $aff_link);
		update_option('md_dash_settings', serialize($dash));
		echo '<div id="message" class="updated">'.__('Dashboard Saved', 'memberdeck').'</div>';
	}
	$general = get_option('md_receipt_settings');
	$is_licensed = get_option('is_idc_licensed');
	$pages = idmember_get_pages();
	if (!empty($general)) {
		if (!is_array($general)) {
			$general = unserialize($general);
		}
		$license_key = $general['license_key'];
		$coname = $general['coname'];
		$coemail = $general['coemail'];
		
		if (isset($general['disable_toolbar'])) {
			$disable_toolbar = $general['disable_toolbar'];
		}
		else {
			$disable_toolbar = 0;
		}
		if (isset($general['s3'])) {
			$s3 = $general['s3'];
		}
		else {
			$s3 = 0;
		}
		if (isset($general['enable_creator'])) {
			$enable_creator = $general['enable_creator'];
		}
		else {
			$enable_creator = 0;
		} 
		
		//Load Creator Permissions menu setting
		if (isset($general['creator_permissions'])) {
			$creator_permissions = $general['creator_permissions'];
		}
		else {
			$creator_permissions = 1;
		}
		
		//Load level-based creator permission values
		if (isset($general['cassign'])) {
			$allowed_creator_levels = array();
			foreach ($general['cassign'] as $ac_assign) {
				$allowed_creator_levels[] = $ac_assign;
			}
		}
		if (isset($general['guest_checkout'])) {
			$guest_checkout = $general['guest_checkout'];
		}
		else {
			$guest_checkout = 0;
		}
		if (isset($general['show_terms'])) {
			$show_terms = $general['show_terms'];
		}
		else {
			$show_terms = 0;
		}
		if (isset($general['terms_page'])) {
			$terms_page = $general['terms_page'];
		}
		else {
			$terms_page = null;
		}
		if (isset($general['privacy_page'])) {
			$privacy_page = $general['privacy_page'];
		}
		else {
			$privacy_page = null;
		}

		// Load default product settings
		if (isset($general['enable_default_product'])) {
			$enable_default_product = $general['enable_default_product'];
			$default_product = $general['default_product'];
		}
		// Credits enabled checkbox
		if (isset($general['enable_credits'])) {
			$enable_credits = $general['enable_credits'];
		}
	}
	if (isset($_POST['receipt-submit'])) {
		$license_key = sanitize_text_field($_POST['license_key']);
		$valid = 0;
		$validate = idc_validate_key($license_key);
		if (isset($validate['response'])) {
			if ($validate['response']) {
				if (isset($validate['download'])) {
					if ($validate['download'] == '29') {
						$valid = 1;
					}
				}
			}
		}
		update_option('is_idc_licensed', $valid);
		$is_licensed = $valid;
		$coname = sanitize_text_field($_POST['co-name']);
		$coemail = sanitize_text_field($_POST['co-email']);
		
		if (isset($_POST['disable_toolbar'])) {
			$disable_toolbar = absint($_POST['disable_toolbar']);
		}
		else {
			$disable_toolbar = 0;
		}
		if (isset($_POST['s3'])) {
			$s3 = absint($_POST['s3']);
		}
		else {
			$s3 = 0;
		}
		if (isset($_POST['enable_creator'])) {
			$enable_creator = absint($_POST['enable_creator']);
		}
		else {
			$enable_creator = 0;
		}
		//Save Creator Permission menu
		if (isset($_POST['creator_permissions'])) {
			$creator_permissions = absint($_POST['creator_permissions']);
		}
		else {
			$creator_permissions = 1;
		}
		//Save level-based permissions
		$allowed_creator_levels = array();
		if (isset($_POST['cassign'])) {
			foreach ($_POST['cassign'] as $ac_assign) {
				$allowed_creator_levels[] = $ac_assign;
			}
		}
		if (isset($_POST['guest_checkout'])) {
			$guest_checkout = absint($_POST['guest_checkout']);
		}
		else {
			$guest_checkout = 0;
		}
		if (isset($_POST['show_terms'])) {
			$show_terms = absint($_POST['show_terms']);
		}
		else {
			$show_terms = 0;
		}
		if (isset($_POST['terms_page'])) {
			$terms_page = sanitize_text_field($_POST['terms_page']);
		}
		else {
			$terms_page = null;
		}
		if (isset($_POST['privacy_page'])) {
			$privacy_page = sanitize_text_field($_POST['privacy_page']);
		}
		else {
			$privacy_page = null;
		}
		// Save default product settings
		if (isset($_POST['enable_default_product'])) {
			$enable_default_product = absint($_POST['enable_default_product']);
			$default_product = sanitize_text_field($_POST['default_product']);
		}
		else {
			$enable_default_product = 0;
			$default_product = null;
		}
		// Credits settings
		if (isset($_POST['enable_credits'])) {
			$enable_credits = absint($_POST['enable_credits']);
		}
		else {
			$enable_credits = 0;
		}
		$receipts = array('license_key' => $license_key, 'coname' => $coname, 'coemail' => $coemail, 'disable_toolbar' => $disable_toolbar, 's3' => $s3, 'enable_creator' => $enable_creator, 'creator_permissions' => $creator_permissions, 'allowed_creator_levels' => $allowed_creator_levels, 'guest_checkout' => $guest_checkout, 'show_terms' => $show_terms, 'terms_page' => $terms_page, 'privacy_page' => $privacy_page, 'enable_default_product' => $enable_default_product, 'default_product' => $default_product, 'enable_credits' => $enable_credits);
		update_option('md_receipt_settings', serialize($receipts));
		echo '<div id="message" class="updated">'.__('Settings Saved', 'memberdeck').'</div>';
	}
	$crm_settings = get_option('crm_settings');
	if (!empty($crm_settings)) {
		$shipping_info = $crm_settings['shipping_info'];
		$mailchimp_key = $crm_settings['mailchimp_key'];
		$mailchimp_list = $crm_settings['mailchimp_list'];
		$enable_mailchimp = $crm_settings['enable_mailchimp'];
		$sendgrid_username = $crm_settings['sendgrid_username'];
		$sendgrid_pw = $crm_settings['sendgrid_pw'];
		$enable_sendgrid = $crm_settings['enable_sendgrid'];
		$mandrill_key = $crm_settings['mandrill_key'];
		$enable_mandrill = $crm_settings['enable_mandrill'];
	}
	if (isset($_POST['crm_submit'])) {
		if (isset($_POST['shipping_info'])) {
			$shipping_info = absint($_POST['shipping_info']);
		}
		else {
			$shipping_info = 0;
		}
		$mailchimp_key = esc_attr($_POST['mailchimp_key']);
		$mailchimp_list = esc_attr($_POST['mailchimp_list']);
		if (isset($_POST['enable_mailchimp'])) {
			$enable_mailchimp = absint($_POST['enable_mailchimp']);
		}
		else {
			$enable_mailchimp = 0;
		}
		$sendgrid_username = esc_attr($_POST['sendgrid_username']);
		$sendgrid_pw = esc_attr($_POST['sendgrid_pw']);
		if (isset($_POST['enable_sendgrid'])) {
			$enable_sendgrid = absint($_POST['enable_sendgrid']);
		}
		else {
			$enable_sendgrid = 0;
		}
		$mandrill_key = esc_attr($_POST['mandrill_key']);
		if (isset($_POST['enable_mandrill'])) {
			$enable_mandrill = absint($_POST['enable_mandrill']);
		}
		else {
			$enable_mandrill = 0;
		}
		$crm_settings = array(
			'shipping_info' => $shipping_info,
			'mailchimp_key' => $mailchimp_key,
			'mailchimp_list' => $mailchimp_list,
			'enable_mailchimp' => $enable_mailchimp,
			'sendgrid_username' => $sendgrid_username,
			'sendgrid_pw' => $sendgrid_pw,
			'enable_sendgrid' => $enable_sendgrid,
			'mandrill_key' => $mandrill_key,
			'enable_mandrill' => $enable_mandrill);
		update_option('crm_settings', $crm_settings);
		echo '<div id="message" class="updated">'.__('CRM Settings Saved', 'memberdeck').'</div>';
	}

	// Saving the label for Virtual currency
	$virtual_currency_labels = get_option('virtual_currency_labels');
	if (isset($_POST['virtual_currency_submit'])) {
		$virtual_currency_label_plural = sanitize_text_field($_POST['id_virtual_curr_label_plural']);
		$virtual_currency_label_singular = sanitize_text_field($_POST['id_virtual_curr_label_singular']);
		if (!empty($virtual_currency_label_singular)) {
			$virtual_currency_labels = array(
				'label_singular' => $virtual_currency_label_singular,
				'label_plural' => $virtual_currency_label_plural
			);
			update_option('virtual_currency_labels', $virtual_currency_labels);
		}
		else {
			// Default option
			$virtual_currency_labels = array(
				'label_singular' => 'Credit',
				'label_plural' => 'Credits'
			);
			update_option('virtual_currency_labels', $virtual_currency_labels);
		}
		echo '<div id="message" class="updated">'.__('Virtual Currency Settings Saved', 'memberdeck').'</div>';
	}

	/***
	Export handler tied to init hook in plugin base
	***/

	include 'templates/admin/_settingsMenu.php';
}

function idc_gateways() {
	global $first_data;
	require("lib/Coinbase/lib/Coinbase.php");
	$pp_currency = 'USD';
	$stripe_currency = 'USD';
	$cb_currency = 'BTC';
	$settings = get_option('memberdeck_gateways');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		if (is_array($settings)) {
			$pp_currency = $settings['pp_currency'];
			$pp_symbol = $settings['pp_symbol'];
			$pp_email = $settings['pp_email'];
			$test_email = $settings['test_email'];
			$paypal_test_redirect = (isset($settings['paypal_test_redirect']) ? $settings['paypal_test_redirect'] : '');
			$paypal_redirect = $settings['paypal_redirect'];
			$stripe_currency = $settings['stripe_currency'];
			$pk = $settings['pk'];
			$sk = $settings['sk'];
			$tpk = $settings['tpk'];
			$tsk = $settings['tsk'];
			$test = $settings['test'];
			$https = $settings['https'];
			$manual_checkout = $settings['manual_checkout'];
			$epp = $settings['epp'];
			$epp_fes = $settings['epp_fes'];
			$es = $settings['es'];
			// Setting for enabling PWYW in stripe
			$epwyw_stripe = ((isset($settings['epwyw_stripe'])) ? $settings['epwyw_stripe'] : '');
			$esc = $settings['esc'];
			$bk = $settings['bk'];
			$burl = $settings['burl'];
			$btk = (isset($settings['btk']) ? $settings['btk'] : '364');
			$bturl = $settings['bturl'];
			$eb = $settings['eb'];
			$ebm = $settings['ebm'];
			$eppadap = (isset($settings['eppadap']) ? $settings['eppadap'] : '0');
			// Coinbase
			$ecb = (isset($settings['ecb']) ? $settings['ecb'] : 0);
			$cb_api_key = (isset($settings['cb_api_key']) ? $settings['cb_api_key'] : '');
			$cb_api_secret = (isset($settings['cb_api_secret']) ? $settings['cb_api_secret'] : '');
			// Authorize.Net
			$eauthnet = (isset($settings['eauthnet']) ? $settings['eauthnet'] : 0);
			$auth_login_id = (isset($settings['auth_login_id']) ? $settings['auth_login_id'] : '');
			$auth_transaction_key = (isset($settings['auth_transaction_key']) ? $settings['auth_transaction_key'] : '');
		}
		if (isset($first_data) && $first_data) {
			$gateway_id = $settings['gateway_id'];
			$fd_pw = $settings['fd_pw'];
			$key_id = $settings['key_id'];
			$hmac = $settings['hmac'];
			$efd = $settings['efd'];
		}
		// Getting PayPal adaptive settings if it's enabled
		if ($eppadap == 1) {
			$ppada_currency = $settings['ppada_currency'];
			$ppadap_api_username = $settings['ppadap_api_username'];
			$ppadap_api_password = $settings['ppadap_api_password'];
			$ppadap_api_signature = $settings['ppadap_api_signature'];
			$ppadap_app_id = $settings['ppadap_app_id'];
			$ppadap_receiver_email = $settings['ppadap_receiver_email'];
			$pp_email = $settings['ppadap_receiver_email'];
			$ppadap_max_preauth_period = $settings['ppadap_max_preauth_period'];
			// Setting Test settings variables
			$ppadap_api_username_test = $settings['ppadap_api_username_test'];
			$ppadap_api_password_test = $settings['ppadap_api_password_test'];
			$ppadap_api_signature_test = $settings['ppadap_api_signature_test'];
			$ppadap_app_id_test = $settings['ppadap_app_id_test'];
			$test_email = $settings['ppadap_receiver_email_test'];
		}
	}
	$coinbase = Coinbase::withApiKey((!empty($cb_api_key) ? $cb_api_key : ''), (!empty($cb_api_secret) ? $cb_api_secret : ''));
	$cb_currencies = $coinbase->getCurrencies();
	if (isset($_POST['gateway-submit'])) {
		$pp_currency = sanitize_text_field($_POST['pp-currency']);
		$pp_symbol = sanitize_text_field($_POST['pp-symbol']);
		$pp_email = sanitize_text_field($_POST['pp-email']);
		$test_email = sanitize_text_field($_POST['test-email']);
		$paypal_test_redirect = sanitize_text_field($_POST['paypal-test-redirect']);
		$paypal_redirect = sanitize_text_field($_POST['paypal-redirect']);
		$stripe_currency = sanitize_text_field($_POST['stripe_currency']);
		$pk = sanitize_text_field($_POST['pk']);
		$sk = sanitize_text_field($_POST['sk']);
		$tpk = sanitize_text_field($_POST['tpk']);
		$tsk = sanitize_text_field($_POST['tsk']);
		$bk = sanitize_text_field($_POST['bk']);
		$burl = sanitize_text_field($_POST['burl']);
		$btk = sanitize_text_field($_POST['btk']);
		$bturl = sanitize_text_field($_POST['bturl']);

		if (isset($_POST['test'])) {
			$test = absint($_POST['test']);
		}
		else {
			$test = '0';
		}
		if (isset($_POST['https'])) {
			$https = absint($_POST['https']);
		}
		else {
			$https = '0';
		}
		if (isset($_POST['manual_checkout'])) {
			$manual_checkout = absint($_POST['manual_checkout']);
		}
		else {
			$manual_checkout = '0';
		}
		if (isset($_POST['epp'])) {
			$epp = absint($_POST['epp']);
		}
		else {
			$epp = '0';
		}
		if (isset($_POST['epp_fes'])) {
			$epp_fes = absint($_POST['epp_fes']);
		}
		else {
			$epp_fes = '0';
		}
		if (isset($_POST['es'])) {
			$es = absint($_POST['es']);
			if (isset($_POST['epwyw_stripe'])) {
				$epwyw_stripe = absint($_POST['epwyw_stripe']);
			} else {
				$epwyw_stripe = 0;
			}
		}
		else {
			$es = '0';
		}
		if (isset($_POST['esc'])) {
			$esc = absint($_POST['esc']);
		}
		else {
			$esc = 0;
		}
		if (isset($_POST['eb'])) {
			$eb = absint($_POST['eb']);
		}
		else {
			$eb = 0;
		}
		if (isset($_POST['ebm'])) {
			$ebm = absint($_POST['ebm']);
		}
		else {
			$ebm = 0;
		}
		// For adaptive
		if (isset($_POST['eppadap'])) {
			$eppadap = absint($_POST['eppadap']);
		}
		else {
			$eppadap = 0;
		}
		if (isset($first_data) && $first_data) {
			$gateway_id = sanitize_text_field($_POST['gateway_id']);
			$fd_pw = sanitize_text_field($_POST['fd_pw']);
			$key_id = sanitize_text_field($_POST['key_id']);
			$hmac = sanitize_text_field($_POST['hmac']);
			if (isset($_POST['efd'])) {
				$efd = absint($_POST['efd']);
			}
			else {
				$efd = 0;
			}
		}
		if (isset($_POST['ecb'])) {
			$ecb = absint($_POST['ecb']);
		}
		else {
			$ecb = 0;
		}
		$cb_currency = sanitize_text_field($_POST['cb_currency']);
		$cb_api_key  = sanitize_text_field($_POST['coinbase_api_key']);
		$cb_api_secret = sanitize_text_field($_POST['coinbase_api_secret']);
		// Authorize.Net 
		if (isset($_POST['eauthnet'])) {
			$eauthnet = absint($_POST['eauthnet']);
		}
		else {
			$eauthnet = 0;
		}
		$auth_login_id = sanitize_text_field($_POST['auth_login_id']);
		$auth_transaction_key = sanitize_text_field($_POST['auth_transaction_key']);

		$settings = array(
			'pp_currency' => $pp_currency,
			'pp_symbol' => $pp_symbol,
			'pp_email' => $pp_email,
			'test_email' => $test_email,
			'paypal_test_redirect' => $paypal_test_redirect,
			'paypal_redirect' => $paypal_redirect,
			'stripe_currency' => $stripe_currency,
			'pk' => $pk,
			'sk' => $sk,
			'tpk' => $tpk,
			'tsk' => $tsk,
			'test' => $test,
			'https' => $https,
			'manual_checkout' => $manual_checkout,
			'epp' => $epp,
			'epp_fes' => $epp_fes,
			'es' => $es,
			'epwyw_stripe' => $epwyw_stripe,
			'esc' => $esc,
			'bk' => $bk,
			'burl' => $burl,
			'btk' => $btk,
			'bturl' => $bturl,
			'eb' => $eb,
			'ebm' => $ebm,
			'ecb' => $ecb,
			'eauthnet' => $eauthnet,
			'cb_currency' => $cb_currency,
			'cb_api_key' => $cb_api_key,
			'cb_api_secret' => $cb_api_secret,
			'auth_login_id' => $auth_login_id,
			'auth_transaction_key' => $auth_transaction_key
		);
		if (isset($first_data) && $first_data) {
			$settings['gateway_id'] = $gateway_id;
			$settings['fd_pw'] = $fd_pw;
			$settings['key_id'] = $key_id;
			$settings['hmac'] = $hmac;
			$settings['efd'] = $efd;
		}
		// Saving PP Adaptive settings
		$settings['eppadap'] = $eppadap;
		if (isset($eppadap) && $eppadap == 1) {
			$settings['ppada_currency'] = sanitize_text_field($_POST['pp-currency']);
			$settings['ppadap_api_username'] = sanitize_text_field($_POST['ppadap_api_username']);
			$settings['ppadap_api_password'] = sanitize_text_field($_POST['ppadap_api_password']);
			$settings['ppadap_api_signature'] = sanitize_text_field($_POST['ppadap_api_signature']);
			$settings['ppadap_app_id'] = sanitize_text_field($_POST['ppadap_app_id']);
			$settings['ppadap_receiver_email'] = sanitize_text_field($_POST['pp-email']);
			// test fields
			$settings['ppadap_api_username_test'] = sanitize_text_field($_POST['ppadap_api_username_test']);
			$settings['ppadap_api_password_test'] = sanitize_text_field($_POST['ppadap_api_password_test']);
			$settings['ppadap_api_signature_test'] = sanitize_text_field($_POST['ppadap_api_signature_test']);
			$settings['ppadap_app_id_test'] = sanitize_text_field($_POST['ppadap_app_id_test']);
			$settings['ppadap_receiver_email_test'] = sanitize_text_field($_POST['test-email']);
			$settings['ppadap_max_preauth_period'] = sanitize_text_field($_POST['ppadap_max_preauth_period']);
			// $ppada_currency = sanitize_text_field($_POST['ppada_currency']);
			$ppadap_api_username = sanitize_text_field($_POST['ppadap_api_username']);
			$ppadap_api_password = sanitize_text_field($_POST['ppadap_api_password']);
			$ppadap_api_signature = sanitize_text_field($_POST['ppadap_api_signature']);
			$ppadap_app_id = sanitize_text_field($_POST['ppadap_app_id']);
			$pp_email = $settings['ppadap_receiver_email'];
			$ppadap_max_preauth_period = sanitize_text_field($_POST['ppadap_max_preauth_period']);
			// Test vars
			$ppadap_api_username_test = sanitize_text_field($_POST['ppadap_api_username_test']);
			$ppadap_api_password_test = sanitize_text_field($_POST['ppadap_api_password_test']);
			$ppadap_api_signature_test = sanitize_text_field($_POST['ppadap_api_signature_test']);
			$ppadap_app_id_test = sanitize_text_field($_POST['ppadap_app_id_test']);
		}
		$idc_gateway_settings = apply_filters('idc_gateway_settings', $settings);
		update_option('memberdeck_gateways', $idc_gateway_settings);
		echo '<div id="message" class="updated">'.__('Gateways Saved', 'memberdeck').'</div>';
	}
	include 'templates/admin/_gatewaySettings.php';
}

function idc_email() {
	$registration_email = stripslashes(get_option('registration_email'));
	$welcome_email = stripslashes(get_option('welcome_email'));
	$purchase_receipt = stripslashes(get_option('purchase_receipt'));
	$preorder_receipt = stripslashes(get_option('preorder_receipt'));
	if (function_exists('is_id_pro') && is_id_pro()) {
		$success_notification = stripslashes(get_option('success_notification'));
		$success_notification_admin = stripslashes(get_option('success_notification_admin'));
		$update_notification = stripslashes(get_option('update_notification'));
	}
	if (isset($_POST['edit_template'])) {
		if ($registration_email !== $_POST['registration_email_text']) {
			$registration_email = wp_kses_post(balanceTags($_POST['registration_email_text']));
			update_option('registration_email', $registration_email);
		}
		if ($welcome_email !== $_POST['welcome_email_text']) {
			$welcome_email = wp_kses_post(balanceTags($_POST['welcome_email_text']));
			update_option('welcome_email', $welcome_email);
		}
		if ($purchase_receipt !== $_POST['purchase_receipt_text']) {
			$purchase_receipt = wp_kses_post(balanceTags($_POST['purchase_receipt_text']));
			update_option('purchase_receipt', $purchase_receipt);
		}
		if ($preorder_receipt !== $_POST['preorder_receipt_text']) {
			$preorder_receipt = wp_kses_post(balanceTags($_POST['preorder_receipt_text']));
			update_option('preorder_receipt', $preorder_receipt);
		}
		if (isset($_POST['success_notification_text']) && $success_notification !== $_POST['success_notification_text']) {
			$success_notification = wp_kses_post(balanceTags($_POST['success_notification_text']));
			update_option('success_notification', $success_notification);
		}
		if (isset($_POST['success_notification_admin_text']) && $success_notification_admin !== $_POST['success_notification_admin_text']) {
			$success_notification_admin = wp_kses_post(balanceTags($_POST['success_notification_admin_text']));
			update_option('success_notification_admin', $success_notification_admin);
		}
		if (isset($_POST['update_notification_text']) && $update_notification !== $_POST['update_notification_text']) {
			$update_notification = wp_kses_post(balanceTags($_POST['update_notification_text']));
			update_option('update_notification', $update_notification);
		}
	}
	else if (isset($_POST['restore_default_registration_email'])) {
		$registration_email = get_option('registration_email_default');
		update_option('registration_email', $registration_email);
	}
	else if (isset($_POST['restore_default_welcome_email'])) {
		$welcome_email = get_option('welcome_email_default');
		update_option('welcome_email', $welcome_email);
	}
	else if (isset($_POST['restore_default_purchase_receipt'])) {
		$purchase_receipt = get_option('purchase_receipt_default');
		update_option('purchase_receipt', $purchase_receipt);
	}
	else if (isset($_POST['restore_default_preorder_receipt'])) {
		$preorder_receipt = get_option('preorder_receipt_default');
		update_option('preorder_receipt', $preorder_receipt);
	}
	else if (isset($_POST['restore_default_success_notification'])) {
		$success_notification = get_option('success_notification_default');
		update_option('success_notification', $success_notification);
	}
	else if (isset($_POST['restore_default_update_notification'])) {
		$update_notification = get_option('update_notification_default');
		update_option('update_notification', $update_notification);
	}
	include 'templates/admin/_emailSettings.php';
}

function idc_enterprise_settings() {
	$gateways = get_option('memberdeck_gateways');
	if (isset($gateways)) {
		if (!is_array($gateways)) {
			$gateways = unserialize($gateways);
		}
		if (is_array($gateways)) {
			$eppadap = (isset($gateways['eppadap']) ? $gateways['eppadap'] : 0);
		}
	}
	$enterprise_settings = get_option('idc_enterprise_settings');
	if (!empty($enterprise_settings)) {
		$fee_type = (isset($enterprise_settings['fee_type']) ? $enterprise_settings['fee_type'] : 'flat');
		$enterprise_fee = (isset($enterprise_settings['enterprise_fee']) ? $enterprise_settings['enterprise_fee'] : '');
	}
	if (isset($_POST['enterprise_submit'])) {
		$fee_type = sanitize_text_field($_POST['fee_type']);
		$enterprise_fee = sanitize_text_field($_POST['enterprise_fee']);
		$enterprise_settings = array('fee_type' => $fee_type, 'enterprise_fee' => $enterprise_fee);
		update_option('idc_enterprise_settings', $enterprise_settings);
	}
	include 'templates/admin/_enterpriseSettings.php';
}

function idc_sc_settings() {
	// Stripe Connect Admin
	$client_id = '';
	$dev_client_id = '';
	$fee_type = 'flat';
	$app_fee = 0;
	$dev_mode = 0;
	$button_style = 'stripe-connect';
	$sc_settings = get_option('md_sc_settings');
	if (!empty($sc_settings)) {
		if (!is_array($sc_settings)) {
			$sc_settings = unserialize($sc_settings);
		}
		if (is_array($sc_settings)) {
			$client_id = $sc_settings['client_id'];
			$dev_client_id = $sc_settings['dev_client_id'];
			$fee_type = $sc_settings['fee_type'];
			$app_fee = $sc_settings['app_fee'];
			$dev_mode = $sc_settings['dev_mode'];
			$button_style = (isset($sc_settings['button_style']) ? $sc_settings['button_style'] : 'stripe-connect');
		}
	}
	if (isset($_POST['sc_submit'])) {
		$client_id = sanitize_text_field($_POST['client_id']);
		$dev_client_id = sanitize_text_field($_POST['dev_client_id']);
		$fee_type = sanitize_text_field($_POST['fee_type']);
		$app_fee = sanitize_text_field($_POST['app_fee']);
		if (isset($_POST['dev_mode'])) {
			$dev_mode = 1;
		}
		else {
			$dev_mode = 0;
		}
		$button_style = sanitize_text_field($_POST['button-style']);
		$sc_settings = array('client_id' => $client_id,
			'dev_client_id' => $dev_client_id,
			'fee_type' => $fee_type,
			'app_fee' => $app_fee,
			'dev_mode' => $dev_mode,
			'button_style' => $button_style);
		update_option('md_sc_settings', serialize($sc_settings));

	}
	if ($dev_mode == 1) {
		$link_id = $dev_client_id;
	}
	else {
		$link_id = $client_id;
	}
	include 'templates/admin/_stripeConnect.php';
}

function idc_bm_settings() {
	$bm_settings = get_option('md_bm_settings');
	if (empty($bm_settings)) {
		$bm_settings = array();
	}
	if (isset($_POST['bm_submit'])) {
		foreach ($_POST as $k=>$v) {
			$bm_settings[$k] = sanitize_text_field($v);
		}
		update_option('md_bm_settings', $bm_settings);
	}
	include 'templates/admin/_balancedMarketplace.php';
}

function idc_s3_settings() {
	$access_key = '';
	$secret_key = '';
	$settings = get_option('md_s3_settings');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		if (is_array($settings)) {
			$access_key = $settings['access_key'];
			$secret_key = $settings['secret_key'];
			$bucket = $settings['bucket'];
		}
	}
	if (isset($_POST['s3_submit'])) {
		$access_key = esc_attr($_POST['access_key']);
		$secret_key = esc_attr($_POST['secret_key']);
		$bucket = esc_attr($_POST['bucket']);
		$settings = array('access_key' => $access_key, 'secret_key' => $secret_key, 'bucket' => $bucket);
		update_option('md_s3_settings', serialize($settings));
	}
	include 'templates/admin/_s3Settings.php';
}

function idmember_admin_js() {
	wp_register_script('idcommerce-admin-js', plugins_url('js/idcommerce-admin.js', __FILE__));
	wp_enqueue_script('jquery');
	$ajaxurl = site_url('/wp-admin/admin-ajax.php');
	$currencies = plugins_url('/templates/admin/currencies.json', __FILE__);
	wp_localize_script('idcommerce-admin-js', 'md_ajaxurl', $ajaxurl);
	wp_localize_script('idcommerce-admin-js', 'md_currencies', $currencies);
	wp_localize_script('idcommerce-admin-js', 'idc_localization_strings', apply_filters('idc_localization_strings', ''));
	wp_enqueue_script('idcommerce-admin-js');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-ui-core', '//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');
}

function mdid_admin_scripts() {
	wp_register_script('cf', plugins_url('/js/cf.js', __FILE__));
	wp_enqueue_script('cf');
}

function idc_email_scripts() {
	wp_register_script('idc-email', plugins_url('/js/idc-email.js', __FILE__));
	wp_enqueue_script('idc-email');
}

function md_sc_scripts() {
	wp_register_script('md_sc', plugins_url('/js/mdSC.js', __FILE__));
	wp_register_style('sc_buttons', plugins_url('/lib/connect-buttons.css', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('md_sc');
	wp_enqueue_style('sc_buttons');
	$sc_settings = get_option('md_sc_settings');
	if (!empty($sc_settings)) {
		if (!is_array($sc_settings)) {
			$sc_settings = unserialize($sc_settings);
		}
		if (is_array($sc_settings)) {
			$client_id = $sc_settings['client_id'];
			$dev_client_id = $sc_settings['dev_client_id'];
			$dev_mode = $sc_settings['dev_mode'];
			if ($dev_mode == 1) {
				$md_sc_clientid = $dev_client_id;
			}
			else {
				$md_sc_clientid = $client_id;
			}
			wp_localize_script('md_sc', 'md_sc_clientid', $md_sc_clientid);
		}
	}
}

function md_bm_scripts() {
	wp_register_script('md_bm-admin', plugins_url('/js/mdBM.js', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('idcommerce-admin-js');
	wp_enqueue_script('md_bm-admin');
}

function idmember_admin_styles() {
	wp_register_style('idcommerce-admin', plugins_url('css/admin-style.css', __FILE__));
	wp_enqueue_style('idcommerce-admin');
}

function idmember_metabox_styles() {
	wp_register_script('idcommerce-metabox', plugins_url('js/idcommerce-metabox.js', __FILE__));
	wp_enqueue_script('idcommerce-metabox');
	$ajaxurl = admin_url('/admin-ajax.php');
	wp_localize_script('idcommerce-metabox', 'md_ajaxurl', $ajaxurl);
}

function idmember_load_metabox_styles() {
	global $pagenow;
	if (isset($pagenow)) {
		if ($pagenow == 'post.php' || $pagenow == 'post-new.php') {
			add_action('admin_enqueue_scripts', 'idmember_metabox_styles');
			call_level_metabox();
		}
		else if ($pagenow == 'edit-tags.php') {
			add_action('admin_enqueue_scripts', 'idmember_metabox_styles');
			call_level_metabox();
		}
	}
}

add_action ('admin_init', 'idmember_load_metabox_styles');

function idmember_load_admin_scripts() {
	// we're only going to load the js inside of the memberdeck menu
	global $pagenow;
	if (isset($pagenow)) {
		if ($pagenow == 'admin.php') {
			if (isset($_GET['page'])) {
				if (strpos($_GET['page'], 'idc') !== FALSE) {
					add_action('admin_enqueue_scripts', 'idmember_admin_js');
				}
			}
			else {
				add_action('admin_enqueue_scripts', 'idmember_admin_js');
			}
		}
	}
	add_action('admin_enqueue_scripts', 'idmember_admin_styles');
}

add_action('admin_init', 'idmember_load_admin_scripts');

function idc_users() {
	global $pagenow;
	$users = array();
	$levels = array();
	$member = new ID_Member();
	$users = array_reverse($member->get_allowed_users());
	$total_users = count($users);
	$level = new ID_Member_Level();
	$levels = $level->get_levels();
	for ($i = 0; $i < count($levels); $i++) {
		$count = ID_Member_Level::get_level_member_updated_count($levels[$i]->id);
		$levels[$i]->count = $count;
	}

	if (isset($_GET['level']) && $_GET['level'] !== '') {
		$level_filter = $_GET['level'];
		$users = ID_Member::get_level_users($level_filter);
	}

	if (isset($_GET['s']) && $_GET['s'] !== '') {
		$search = $_GET['s'];
		$users = ID_Member::get_like_users($search);
	}

	$pages = ceil(count($users) / 20);

	if ($pages == 0) {
		$pages = 1;
	}
	if (isset($_GET['p'])) {
		// if we have a page query, we get that page number
		$page = $_GET['p'];
		if ($page < $pages) {
			$nextp = $page + 1;
		}
		else {
			$nextp = $page;
		}
		
		if ($page == 1) {
			// still page 1
			$start = 0;
			$lastp = 1;
		}
		else {
			// start counting by 20, 30, 40, etc
			$start = ($page*20) - 20;
			$lastp = $page -1;
		}
		if (count($users) < 19) {
			// if we have less than a full page, we only show those users
			$count = count($users)-1;
		}
		else {
			// we have more, so we show the next 19
			// this will trigger a warning if we go over the true count
			$count = $start + 19;
		}
	}
	else {
		// start on 0 if no page set
		$page = 1;
		$start = 0;
		$nextp = 2;
		$lastp = 1;
		$count = $start + 19;
	}
	$section = 'idc-users';
	$query = array('page' => $section);
	$next_query = array('page' => $section, 'p' => $nextp);
	$prev_query = array('page' => $section, 'p' => $lastp);
	$end_query = array('page' => $section, 'p' => $pages);
	$first_query = array('page' => $section, 'p' => 1);

	if (isset($search)) {
		//$query['s'] = $search;
		$next_query['s'] = $search;
		$prev_query['s'] = $search;
		$end_query['s'] = $search;
		$first_query['s'] = $search;
	}
	if (isset($level_filter)) {
		//$query['level'] = $level_filter;
		$next_query['level'] = $level_filter;
		$prev_query['level'] = $level_filter;
		$end_query['level'] = $level_filter;
		$first_query['level'] = $level_filter;
	}
	$gets = $_SERVER['QUERY_STRING'];
	$mail_url = '?'.$gets.'&send_mail=1';
	$query_string = http_build_query($query);
	$query_next = http_build_query($next_query);
	$query_prev = http_build_query($prev_query);
	$query_last = http_build_query($end_query);
	$query_first = http_build_query($first_query);

	if (isset($_GET['send_mail']) && $_GET['send_mail'] == 1) {
		$emails = array();
		foreach ($users as $user) {
			$emails[] = $user->user_email;
		}
		$back_url = admin_url('admin.php?').str_replace('send_mail=1', 'send_mail=0', $_SERVER['QUERY_STRING']);
		if (isset($_POST['send_mail'])) {
			$subject = str_replace('&#039;', "'", stripslashes(esc_attr($_POST['subject'])));
			$message = wpautop(html_entity_decode(stripslashes(esc_html($_POST['message']))));
			$general = get_option('md_receipt_settings');
			if (!empty($general)) {
				if (!is_array($general)) {
					$general = unserialize($general);
				}
				$coname = $general['coname'];
				$coemail = $general['coemail'];
				foreach ($emails as $email) {
					md_send_mail($email, null, $subject, $message);
				}
				echo '<script>location.href="'.$back_url.'";</script>';
			}
		}
		include_once 'templates/admin/_sendMail.php';
	}
	else {
		include 'templates/admin/_userMenu.php';
	}
}

function idc_orders() {
	// number of results to show per page
	$row_count = 20;
	// what's on the page now?
	$query = $_SERVER['QUERY_STRING'];
	// quick query to get number of orders
	$order_count = ID_Member_Order::get_order_count();
	if (!empty($order_count)) {
		$order_count = $order_count->count;
	}
	else {
		$order_count = 0;
	}
	// handle search query
	if (isset($_GET['s'])) {
		$search = esc_attr($_GET['s']);
	}
	else {
		$search = null;
	}
	// what page are we on?
	if (isset($_GET['p'])) {
		$page = absint($_GET['p']);
	}
	else {
		$page = 1;
	}
	// calculate number of total pages
	$pages = ceil(($order_count) / $row_count);
	// now handle where our next page arrows should point to
	if ($pages == 0) {
		$pages = 1;
	}
	if ($page < $pages) {
		$nextp = $page + 1;
	}
	else {
		$nextp = $page;
	}
	if ($page == 1) {
		$limit = '0, '.($row_count - 1);
		// back arrow
		$lastp = 1;
	}
	else {
		// calculate limit based on current page number and row count
		$start = ($page * 20) - $row_count;
		$end = ($row_count * $page) - 1;
		$limit = $start.', '.$end;
		// back arrow
		$lastp = $page -1;
	}
	parse_str($query, $query_array);
	$query_last = $query_array;
	$query_first = $query_array;
	$query_next = $query_array;
	$query_prev = $query_array;
	$query_last['p'] = $pages;
	$query_last = http_build_query($query_last);
	$query_first['p'] = 1;
	$query_first = http_build_query($query_first);
	$query_next['p'] = $nextp;
	$query_next = http_build_query($query_next);
	$query_prev['p'] = $lastp;
	$query_prev = http_build_query($query_prev);
	$misc = ' ORDER BY id DESC';
	$orders = ID_Member_Order::get_orders($search, $limit, $misc);
	include_once 'templates/admin/_orderList.php';
}

function idc_bridge_settings() {
	global $crowdfunding;
	if (class_exists('ID_Project')) {
		$projects = ID_Project::get_all_projects();

	}
	$fund_type = get_option('idc_cf_fund_type');
	$global_currency = get_option('idc_global_currency');
	if (isset($_POST['save_idc_cf_settings'])) {
		if (isset($_POST['project_fund_type'])) {
			$fund_type = sanitize_text_field($_POST['project_fund_type']);
			update_option('idc_cf_fund_type', $fund_type);
		}
		$global_currency = esc_attr($_POST['global_currency']);
		update_option('idc_global_currency', $global_currency);
	}
	include_once 'templates/admin/_bridgeSettings.php';
}

// This function calls the metabox function inside of our levels class

function call_level_metabox() {
	$metabox = new ID_Member_Metaboxes();
}

// Bridge Metaboxes
add_action('plugins_loaded', 'load_project_crowdfunding');

function load_project_crowdfunding() {
	global $crowdfunding;
	if ($crowdfunding) {
		//add_action('add_meta_boxes', 'mdid_project_metaboxes');
		//add_action('save_post', 'md_extension_save');
	}
}

function mdid_project_metaboxes() {
	$screens = array('ignition_product');
	foreach ($screens as $screen) {
		add_meta_box(
			'mdid_project_activate',
			__('Make Available for Memberships', 'mdid'),
			'mdid_project_activate',
			$screen,
			'side'
		);
	}
}

function mdid_project_activate($post) {
	wp_nonce_field(plugin_basename(__FILE__), 'mdid_project_activation');
	$active = get_post_meta($post->ID, 'mdid_project_activate', true);
	if (empty($active)) {
		$active = 'no';
	}
	echo '<p><label for="mdid_project_activate">Activate for Membership</label></p>';
	echo '<p><input type="hidden" name="mdid_project_activate" id="mdid_project_activate" value="yes" checked="checked"/> ';
	//echo '<p><input type="radio" name="mdid_project_activate" id="mdid_project_activate" value="no" '.(isset($active) && $active == 'no' ? 'checked="checked"' : '').'/> '.__('No', 'mdid').'</p>';
}

function md_extension_save($post_id) {
	
	if (!isset($_POST['mdid_project_activation']) || !wp_verify_nonce($_POST['mdid_project_activation'], plugin_basename(__FILE__))) {
  		return;
  	}

  	if ( 'page' == $_REQUEST['post_type'] ) {
   		if ( ! current_user_can( 'edit_page', $post_id ) ) {
        	return;
    	}
  	}

  	else {
    	if ( ! current_user_can( 'edit_post', $post_id ) ) {
        	return;
        }
  	}
  	$post_id = $_POST['post_ID'];

  	$active = 'yes';//$_POST['mdid_project_activate'];
  	update_post_meta($post_id, 'mdid_project_activate', $active);
}

/**
* Category and Tag Metaboxes
*/

// if we're editing a category or tag, use this form
add_action('edit_category_form_fields', 'md_protect_old_cat');
add_action('edit_tag_form_fields', 'md_protect_old_cat');

function md_protect_old_cat($tag) {
	$term_id = $tag->term_id;
	$protect = get_option('protect_term_'.$term_id);
	$class = new ID_Member_Level();
  	$levels = $class->get_levels();
	if (empty($protect) || !isset($protect) || !$protect) {
		$protect = 0;
	}
	else {
		$allowed = get_option('term_'.$term_id.'_allowed_levels');
		if (!empty($allowed)) {
			$array = unserialize($allowed);
		}
	}
	ob_start();
	include_once 'templates/admin/_metaboxCategory.php';
	$content = ob_get_contents();
	ob_end_clean();
	echo $content;
}
// if we're on a new category or tag, use this form
add_action('category_add_form_fields', 'md_protect_new_cat');
add_action('post_tag_add_form_fields', 'md_protect_new_cat');

function md_protect_new_cat($taxonomy) {
	$class = new ID_Member_Level();
  	$levels = $class->get_levels();
	ob_start();
	include_once 'templates/admin/_metaboxContent.php';
	$content = ob_get_contents();
	ob_end_clean();
	echo $content;
}

// save protection regardless of new tag, category or edit tag/category
add_action('edit_category', 'md_protect_cat_save');
add_action('create_category', 'md_protect_cat_save');
add_action('edit_tag', 'md_protect_cat_save');
add_action('create_post_tag', 'md_protect_cat_save');
add_action('edit_term', 'md_protect_cat_save');
add_action('create_term', 'md_protect_cat_save');

function md_protect_cat_save($id) {
	if (isset($_POST['tag_ID'])) {
		$term_id = $_POST['tag_ID'];
	}
	else if (isset($id)) {
		$term_id = $id;
	}
	else {
		return;
	}
	if (isset($_POST['protect-choice'])) {
		// saving new
		$protect = esc_attr($_POST['protect-choice']);
		if ($protect) {
	  		$protected = array();
	  		if (isset($_POST['protect-level'])) {
	  			foreach ($_POST['protect-level'] as $protect_level) {
		  			$protected[] = $protect_level;
		  		}
	  		}
	  		$serialize = serialize($protected);
	  		update_option('term_'.$term_id.'_allowed_levels', $serialize);	  	
		}
		else {
		  	delete_option('term_'.$term_id.'_allowed_levels');
		  	delete_option('protect_term_'.$term_id);
		  	return;
		 }
	}
	else {
		return;
	}
	update_option('protect_term_'.$term_id, $protect);
}

// Customize the WP Customizer Register
add_action('customize_register', 'idc_customize_menu');

function idc_customize_menu($wp_customize) {
	$wp_customize->add_section('idc_menu_items', array(
		'title' => __('IDC Account Links', 'memberdeck'),
		'priority' => 85,
		'description' => __('Use this setting to automatically add the My Account and Login/Register/Logout links to your navigation menu. Do not use if using Theme 500.', 'memberdeck')
	));
	$menus = get_registered_nav_menus();
	foreach ($menus as $k=>$v) {
		$wp_customize->add_setting('idc_menu_'.$k, array(
			'capability' => 'edit_theme_options',
			'type' => 'option',
		));
		$wp_customize->add_control('idc_menu_'.$k, array(
			'label' => $v,
			'section' => 'idc_menu_items',
			'settings' => 'idc_menu_'.$k,
			'type' => 'checkbox',
		));
	}
	
	do_action('idc_customize_menu', $wp_customize);
	return $wp_customize;
}

add_action('after_setup_theme', 'idc_check_customizer');

function idc_check_customizer() {
	add_filter('wp_nav_menu_items', 'idc_update_menus', 10, 2);
}

function idc_update_menus($nav, $args) {
	global $permalink_structure;
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	else {
		$prefix = '?';
	}
	$durl = md_get_durl();
	$location = $args->theme_location;
	$option = get_option('idc_menu_'.$location);
	if ($option) {
		if (is_user_logged_in()) {
			$nav .= '<li class="createaccount buttonpadding"><a href="'.$durl.'">'.__('My Account', 'memberdeck').'</a></li>';
			$nav .= '<li class="login right"><a href="'.wp_logout_url( home_url() ).'">'.__('Logout', 'memberdeck').'</a></li>';
		}
		else {
			$nav .= '<li class="createaccount buttonpadding"><a href="'.$durl.$prefix.'action=register">'.__('Create Account', 'memberdeck').'</a></li>';
			$nav .= '<li class="login right"><a href="'.$durl.'">'.__('Login', 'memberdeck').'</a></li>';
		}
	}
	return $nav;
}

?>