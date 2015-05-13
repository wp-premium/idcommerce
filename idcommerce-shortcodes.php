<?php
add_shortcode('memberdeck_dashboard', 'memberdeck_dashboard');
add_shortcode('memberdeck_checkout', 'memberdeck_checkout');
add_shortcode('idc_button', 'idc_button');

function memberdeck_dashboard() {
	ob_start();
	global $crowdfunding;
	$instant_checkout = instant_checkout();
	/* Mange Dashboard Visibility */
	if (is_user_logged_in()) {
		global $current_user;
		$user_id = $current_user->ID;
		//global $customer_id; --> will trigger 1cc notice
		get_currentuserinfo();
		$fname = $current_user->user_firstname;
		$lname = $current_user->user_lastname;
		$registered = $current_user->user_registered;
		$key = md5($registered.$current_user->ID);
		// expire any levels that they have not renewed
		$level_check = memberdeck_exp_checkondash($current_user->ID);
		// this is an array user options
		$user_levels = ID_Member::user_levels($current_user->ID);
	}

	if (isset($user_levels)) {
		// this is an array of levels a user has access to
		$access_levels = unserialize($user_levels->access_level);
		if (is_array($access_levels)) {
			$unique_levels = array_unique($access_levels);
		}
	}
	
	$downloads = ID_Member_Download::get_downloads();
	// we have a list of downloads, but we need to get to the levels by unserializing and then restoring as an array
	if (!empty($downloads)) {
		// this will be a new array of downloads with array of levels
		$download_array = array();
		foreach ($downloads as $download) {
			$new_levels = unserialize($download->download_levels);
			unset($download->download_levels);
			// lets loop through each level of each download to see if it matches
			$pass = false;
			if (!empty($new_levels)) {
				foreach ($new_levels as $single_level) {
					if (isset($unique_levels) && in_array($single_level, $unique_levels)) {
						// if this download belongs to our list of user levels, add it to array
						//$download->download_levels = $new_levels;
						$pass = true;
						$e_date = ID_Member_Order::get_expiration_data($user_id, $single_level);
					}
				}
			}
			if (isset($user_id))
				$license_key = MD_Keys::get_license($user_id, $download->id);
			if ($pass) {
				$days_left = idmember_e_date_format($e_date);
				$download->key = $license_key;
				$download->days_left = $days_left;
				$download_array['visible'][] = $download;
			}
			else {
				$download_array['invisible'][] = $download;
			}
		}
		// we should now have an array of downloads that this user has accces to
	}
	if (is_user_logged_in()) {
		$dash = get_option('md_dash_settings');
		$general = maybe_unserialize(get_option('md_receipt_settings'));
		if (!empty($dash)) {
			if (!is_array($dash)) {
				$dash = unserialize($dash);
			}
			if (isset($dash['layout'])) {
				$layout = $dash['layout'];
			}
			else {
				$layout = 1;
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
			if ($layout == 1) {
				$p_width = 'half';
				$a_width = 'half';
				$b_width = 'half';
				$c_width = 'half';
			}
			else if ($layout == 2) {
				$p_width = 'half';
				$a_width = 'half';
				$b_width = 'full';
				$c_width = 'full';
			}
			else if ($layout == 3) {
				$p_width = 'full';
				$a_width = 'full';
				$b_width = 'full';
				$c_width = 'full';
			}
			else if ($layout == 4) {
				$p_width = 'half';
				$a_width = 'half-tall';
				$b_width = 'half';
				$c_width = 'hidden';
			}
			if (isset($dash['powered_by'])) {
				$powered_by = $dash['powered_by'];
			}
			else {
				$powered_by = 1;
			}
		}

		// If credits are enabled from settings, then get available credits, else set them to 0
		if (isset($general['enable_credits']) && $general['enable_credits'] == 1) {
			$md_credits = md_credits();
		} else {
			$md_credits = 0;
		}
		$settings = get_option('memberdeck_gateways', true);
		if (isset($settings)) {
			$es = $settings['es'];
			$eb = $settings['eb'];
			$efd = $settings['efd'];
			$eauthnet = $settings['eauthnet'];
			if ($es == 1) {
				$customer_id = customer_id();
			}
			else if ($eb == 1) {
				$balanced_customer_id = balanced_customer_id();
				$customer_id = $balanced_customer_id;
			}
			else if ($efd == 1) {
				$fd_card_details = fd_customer_id();
				if (!empty($fd_card_details)) {
					$fd_token = $fd_card_details['fd_token'];
					$customer_id = $fd_card_details;
				}
			}
			else if ($eauthnet == 1) {
				$authorize_customer_id = authnet_customer_id();
				if (!empty($authorize_customer_id)) {
					$customer_id = $authorize_customer_id['authorizenet_payment_profile_id'];
				} else {
					$customer_id = "";
				}
			}
		}
		if ($md_credits > 0 || !empty($customer_id)) {
			$show_occ = true;
		}
		else {
			$show_occ = false;
		}
		include_once 'templates/admin/_memberDashboard.php';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	else {
		include_once 'templates/_protectedPage.php';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}

function memberdeck_checkout($attrs) {
	ob_start();
	$url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$customer_id = customer_id();
	$instant_checkout = instant_checkout();
	global $crowdfunding;
	global $first_data;
	global $pwyw;
	global $global_currency;
	global $stripe_api_version;
	// use the shortcode attr to get our level id
	$product_id = $attrs['product'];
	if (isset($pwyw) && $pwyw) {
		if (isset($_GET['price']) && $_GET['price'] > 0) {
			if ($global_currency == 'BTC' || $global_currency == 'credits') {
				$pwyw_price = sprintf('%f', floatval($_GET['price']));
			}
			else {
				$pwyw_price = floatval(esc_attr($_GET['price']));
			}
		}
		else if (isset($_POST['price']) && $_POST['price'] > 0) {
			if ($global_currency == 'BTC' || $global_currency == 'credits') {
				$pwyw_price = sprintf('%f', floatval($_POST['price']));
			}
			else {
				$pwyw_price = floatval(esc_attr($_POST['price']));
			}
		}
	}

	// get the user info
	if (is_user_logged_in()) {
		global $current_user;
		get_currentuserinfo();
		$email = $current_user->user_email;
		$fname = $current_user->user_firstname;
		$lname = $current_user->user_lastname;
		$member = new ID_Member($current_user->ID);
		$user_data = ID_Member::user_levels($current_user->ID);
		if (!empty($user_data)) {
			$user_levels = unserialize($user_data->access_level);
		}
		else {
			$user_levels = null;
		}
		// lets see how many levels this user owns
		if (is_array($user_levels)) {
			foreach ($user_levels as $level) {
				if ($level == $product_id) {
					$renewable = ID_Member_Level::is_level_renewable($level);
					if (!$renewable) {
						$already_valid = true;
					}
				}
			}
		}
	}
	$settings = get_option('md_receipt_settings');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		$coname = $settings['coname'];
		$guest_checkout = $settings['guest_checkout'];
	}
	else {
		$coname = '';
		$guest_checkout = 0;
	}
	// Settings assigning to general variable
	$general = maybe_unserialize($settings);
	
	$gateways = get_option('memberdeck_gateways');
	if (!empty($gateways)) {
		// gateways are saved and we can now get settings from Stripe and Paypal
		if (is_array($gateways)) {
			$mc = $gateways['manual_checkout'];
			$pp_email = $gateways['pp_email'];
			$test_email = $gateways['test_email'];
			$pk = $gateways['pk'];
			$sk = $gateways['sk'];
			$tpk = $gateways['tpk'];
			$tsk = $gateways['tsk'];
			$test = $gateways['test'];
			$epp = $gateways['epp'];
			$es = $gateways['es'];
			$esc = $gateways['esc'];
			$bk = $gateways['bk'];
			$btk = $gateways['btk'];
			$eb = $gateways['eb'];
			$ecb = (isset($gateways['ecb']) ? $gateways['ecb'] : '0');	//Coinbase
			$eauthnet = (isset($gateways['eauthnet']) ? $gateways['eauthnet'] : '0');	//Authorize.Net
			$eppadap = (isset($gateways['eppadap']) ? $gateways['eppadap'] : '0');
			if (isset($first_data) && $first_data) {
				$gateway_id = $gateways['gateway_id'];
				$fd_pw = $gateways['fd_pw'];
				$efd = $gateways['efd'];
			}
		}
	}

	// Now we check for Stripe connect data
	if (function_exists('is_id_pro') && is_id_pro()) {
		$settings = get_option('memberdeck_gateways');
		if (!empty($settings)) {
			if (is_array($settings)) {
				if ($settings['es'] == '1') {
					// Stripe is active, so we use the id that is set above
					$customer_id = $customer_id;
				}
				else if ($settings['eb'] == '1') {
					$balanced_customer_id = balanced_customer_id();
					$customer_id = $balanced_customer_id;
				}
				else if ($settings['efd'] == '1') {
					$fd_card_details = fd_customer_id();
					if (!empty($fd_card_details)) {
						$customer_id = $fd_card_details['fd_token'];
					}
				}
				else if ($settings['eauthnet'] == '1') {
					$authorize_customer_id = authnet_customer_id();
					if (!empty($authorize_customer_id)) {
						$customer_id = $authorize_customer_id['authorizenet_payment_profile_id'];
					} else {
						$customer_id = "";
					}
				}
				$esc = $settings['esc'];
				$check_claim = apply_filters('md_level_owner', get_option('md_level_'.$product_id.'_owner'));
				if (!empty($check_claim)) {
					if ($esc == '1') {						
						$md_sc_creds = get_sc_params($check_claim);
						if (!empty($md_sc_creds)) {
							$sc_accesstoken = $md_sc_creds->access_token;
							$sc_pubkey = $md_sc_creds->stripe_publishable_key;
						}
					}
					if ($epp == '1') {
						$claimed_paypal = get_user_meta($check_claim, 'md_paypal_email', true);
					}
				}
			}
		}
	}
	if ($es == 1) {
		if (!class_exists('Stripe')) {
			require_once 'lib/Stripe.php';
		}
		if (isset($test) && $test == '1') {
			Stripe::setApiKey($tsk);
			Stripe::setApiVersion($stripe_api_version);
		}
		else {
			Stripe::setApiKey($sk);
			Stripe::setApiVersion($stripe_api_version);
		}
		// get stripe currency
		$stripe_currency = 'USD';
		$stripe_symbol = '$';
		$settings = get_option('memberdeck_gateways');
		if (!empty($settings)) {
			if (is_array($settings)) {
				$stripe_currency = $settings['stripe_currency'];
				$stripe_symbol = md_stripe_currency_symbol($stripe_currency);
			}
		}
	}
	else if ($eb == 1) {
		if (isset($test) && $test == '1') {
			$burl = $gateways['bturl'];
		}
		else {
			$burl = $gateways['burl'];
		}
	}
	else if (isset($efd) && $efd == 1) {
		$endpoint = 'https://api.globalgatewaye4.firstdata.com/transaction/v12';
		$wsdl = 'https://api.globalgatewaye4.firstdata.com/transaction/v12/wsdl';
	}

	// use that id to get our level data
	$return = ID_Member_Level::get_level($product_id);
	// we have that data, lets store it in vars
	$level_name = $return->level_name;
	if (isset($renewable) && $renewable) {
		$level_price = $return->renewal_price;
	}
	else {
		$level_price = $return->level_price;
		if (isset($pwyw_price) && $pwyw_price > $level_price) {
			$level_price = $pwyw_price;
		}
	}
	$txn_type = $return->txn_type;
	$currency = memberdeck_pp_currency();
	if (!empty($currency)) {
		$pp_currency = $currency['code'];
		$pp_symbol = $currency['symbol'];
	}
	else {
		$pp_currency = 'USD';
		$pp_symbol = '$';
	}
	// If payment gateway for CC payments is Authorize.Net, and level is recurring, make instant_checkout false
	if ($return->level_type == 'recurring' && $gateways['eauthnet'] == 1) {
		$instant_checkout = false;
	}
	
	$type = $return->level_type;
	$recurring = $return->recurring_type;
	$limit_term = $return->limit_term;
	$term_length = $return->term_length;

	$credit_value = $return->credit_value;
	$cf_level = false;
	if ($crowdfunding) {
		$cf_assignments = get_assignments_by_level($product_id);
		if (!empty($cf_assignments)) {
			$project_id = $cf_assignments[0]->project_id;
			$project = new ID_Project($project_id);
			$the_project = $project->the_project();
			$post_id = $project->get_project_postid();
			$id_disclaimer = get_post_meta($post_id, 'ign_disclaimer', true);
		}
	}

	// Getting credits value, if the product can be purchased using credits and if the user have credits, then add an option to purhcase using credits
	$paybycrd = 0;
	$member_credits = 0;
	if (isset($general['enable_credits']) && $general['enable_credits'] == 1) {
		if (isset($member)) {
			$member_credits = $member->get_user_credits();
		}
		if ($member_credits > 0) {
			if (isset($pwyw_price) && $global_currency == 'credits') {
				$credit_value = $pwyw_price;
			}
			if ($credit_value > 0 && $credit_value <= $member_credits) {
				$paybycrd = 1;
			}
		}
	}

	if ($ecb) {
		$cb_currency = (isset($gateways['cb_currency']) ? $gateways['cb_currency'] : 'BTC');
	}

	if (!isset($already_valid) || $return->enable_multiples) {
		// they don't own this level, send forth the template
		$level_price = apply_filters('idc_product_price', $level_price, $product_id, $return);
		if ($level_price !== '' && $level_price > 0) {
			if ($global_currency == 'BTC' || $global_currency == 'credits') {
				$level_price = sprintf('%f', (float) $level_price);
			}
			else {
				$level_price = number_format(floatval($level_price), 2, '.', ',');
			}
		}

		// Getting the option to show terms checkbox and page content
		$receipt_settings = get_option( "md_receipt_settings" );
		if (!is_array($receipt_settings)) {
			$receipt_settings = unserialize($receipt_settings);
		}
		// Getting the content of the terms page
		if (!empty($receipt_settings['terms_page'])) {
			$terms_content = get_post( $receipt_settings['terms_page'] );
		}
		if (!empty($receipt_settings['privacy_page'])) {
			$privacy_content = get_post( $receipt_settings['privacy_page'] );
		}
		
		include_once 'templates/_checkoutForm.php';
		$content = ob_get_contents();
	}
	else {
		// they already own this one
		$content = '<p>'.__('You already own this product. Please', 'memberdeck').' <a href="'.wp_logout_url().'">'.__('logout', 'memberdeck').'</a> '.__('and create a new account in order to purchase again', 'memberdeck').'.</p>';
	}
	ob_end_clean();
	return $content;
}

function idc_button($args) {
	$args = apply_filters('idc_button_args', $args);
	do_action('idc_button_before', $args);
	$button = '<div class="memberdeck">';
	$button .= '<button type="'.(isset($args['type']) ? $args['type'] : '').'" id="'.(isset($args['id']) ? $args['id'] : '').'" class="idc_shortcode_button submit-button '.(isset($args['classes']) ? $args['classes'] : '').'" '.(isset($args['product']) ? 'data-product="'.$args['product'].'"' : '').' '.(isset($args['source']) ? 'data-source="'.$args['source'].'"' : '').'>'.(isset($args['text']) ? $args['text'] : '').'</button>';
	$button .= '</div>';
	do_action('idc_button_after', $args);
	return apply_filters('idc_button', $button, $args);
}
?>