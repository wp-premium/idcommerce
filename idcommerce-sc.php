<?php

function save_sc_params($user_id, $params) {
	if (!empty($params) && $user_id > 0) {
		global $wpdb;
		$access_token = $params['access_token'];
		$refresh_token = $params['refresh_token'];
		$stripe_publishable_key = $params['stripe_publishable_key'];
		$stripe_user_id = $params['stripe_user_id'];
		$oldparams = get_sc_params($user_id);
		if (empty($oldparams)) {
			$original = md_sc_original($refresh_token);
			if (empty($original)) {
				//echo '1';
				$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_sc_params (user_id,
					access_token,
					refresh_token,
					stripe_publishable_key,
					stripe_user_id) VALUES (%d, %s, %s, %s, %s)', $user_id, $access_token, $refresh_token, $stripe_publishable_key, $stripe_user_id);
				$res = $wpdb->query($sql);
				$insert_id = $wpdb->insert_id;
				if ($insert_id > 0) {
					//echo '2';
					return $insert_id;
				}
				else {
					//echo '3';
					return null;
				}
			}
			else {
				//echo '4';
				return $original;
			}
		}
		else {
			$valid_params = validate_sc_params($user_id);
			if ($valid_params) {
				//echo '5';
				return $params->id;
			}
			else {
				delete_sc_params($user_id);
				$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_sc_params (user_id,
					access_token,
					refresh_token,
					stripe_publishable_key,
					stripe_user_id) VALUES (%d, %s, %s, %s, %s)', $user_id, $access_token, $refresh_token, $stripe_publishable_key, $stripe_user_id);
				$res = $wpdb->query($sql);
				$insert_id = $wpdb->insert_id;
				if ($insert_id > 0) {
					//echo '6';
					return $insert_id;
				}
				else {
					//echo '7';
					return null;
				}
			}
		}
	}
}

function validate_sc_params($user_id) {
	global $stripe_api_version;
	$params = get_sc_params($user_id);
	$account = 0;
	if (!empty($params)) {
		require_once 'lib/Stripe.php';
		$api_key = $params->access_token;
		try {
			Stripe::setApiKey($api_key);
			Stripe::setApiVersion($stripe_api_version);
			$stripe_account = Stripe_Account::retrieve();
			if (!empty($stripe_account)) {
				$account = 1;
			}
		}
		catch(Exception $e) {
			$account = 0;
		}
	}
	return $account;
}

function get_sc_params($user_id) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_sc_params WHERE user_id = %d', $user_id);
	$res = $wpdb->get_row($sql);
	return $res;
}

function delete_sc_params($user_id) {
	global $wpdb;
	$sql = $wpdb->prepare('DELETE FROM '.$wpdb->prefix.'memberdeck_sc_params WHERE user_id = %d', $user_id);
	$res = $wpdb->query($sql);
}

function md_sc_original($refresh_token) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_sc_params WHERE refresh_token = %s', $refresh_token);
	$res = $wpdb->get_row($sql);
	if (isset($res->id) && $res->id > 0) {
		$result =  $res->id;
	}
	else {
		$result =  null;
	}
	return $result;
}

function md_sc_creds($user_id) {
	global $wpdb;
	global $stripe_api_version;
	$params = get_sc_params($user_id);
	if (isset($params->id) && $params->id > 0) {
		require_once 'lib/Stripe.php';
		$api_key = $params->access_token;
		//echo $api_key;
		try {
			Stripe::setApiKey($api_key);
			Stripe::setApiVersion($stripe_api_version);
			$account = Stripe_Account::retrieve();
			return $params->id;
		}
		catch(Exception $e) {
			// we have an error, we probably need to delete and try again
			delete_sc_params($user_id);
			//echo $e;
		 	return null;
		}
	}
	else {
		return null;
	}
}

function md_sc_users() {
	global $wpdb;
	$sql = 'SELECT user_id FROM '.$wpdb->prefix.'memberdeck_sc_params';
	$res = $wpdb->get_results($sql);
	return ($res);
}

add_action('init', 'md_check_show_sc');

function md_check_show_sc() {
	$settings = get_option('memberdeck_gateways');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		if (is_array($settings)) {
			$esc = $settings['esc'];
			if ($esc == 1) {
				add_action('md_payment_settings_sidebar', 'md_sc_signup');
			}
		}
	}
}


function md_sc_signup() {
	if (is_user_logged_in()) {
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		$check_creds = md_sc_creds($user_id);
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
					$client_id = $dev_client_id;
				}
				$button_style = (isset($sc_settings['button_style']) ? $sc_settings['button_style'] : 'stripe-connect');
				include_once 'templates/_scSignup.php';
				if (empty($client_id)) {
					$message = __('No client id set', 'memberdeck');
				}
				else {
					$message = null;
				}
			}
		}

	}
}

add_action('init', 'md_sc_return_handler');

function md_sc_return_handler() {
	if (isset($_GET['ipn_handler']) && $_GET['ipn_handler'] == 'sc_return') {
		// we're in
		if (isset($_GET['error'])) {
			$error = $_GET['error'];
		}
		if (isset($_GET['code'])) {
			$code = $_GET['code'];
		}
		if (isset($_GET['state'])) {
			$state = $_GET['state'];
		}
		else {
			global $current_user;
			get_currentuserinfo();
			$user_id = $current_user->ID;
			$state = $user_id;
		}
		if (isset($code) && isset($state)) {
			if (is_user_logged_in()) {
				if (is_multisite()) {
					require (ABSPATH . WPINC . '/pluggable.php');
				}
				global $current_user;
				get_currentuserinfo();
				$user_id = $current_user->ID;
				$check_creds = md_sc_creds($user_id);
				if (empty($check_creds)) {
					$url = 'https://connect.stripe.com/oauth/token?code='.$code.'&grant_type=authorization_code';
					$ch = curl_init($url);
					$settings = get_option('memberdeck_gateways');
					if (!empty($settings)) {
						if (is_array($settings)) {
							$test = $settings['test'];
							if ($test == 1) {
								$key = $settings['tsk'];
							}
							else {
								$key = $settings['sk'];
							}
							if (!empty($key)) {
								$params = array('client_secret' => $key);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								$json = curl_exec($ch);
								curl_close($ch);
								if (isset($json)) {
									$response = json_decode($json);
									if (isset($response->error_description)) {
										//add_filter('the_content', 'md_sc_return_error', 5, $content, $response->error_description);
										$message = $response->error_description;
										//echo $message;
									}
									else {
										$access_token = $response->access_token;
										$refresh_token = $response->refresh_token;
										$stripe_publishable_key = $response->stripe_publishable_key;
										$stripe_user_id = $response->stripe_user_id;
										$params = array('access_token' => $access_token,
											'refresh_token' => $refresh_token,
											'stripe_publishable_key' => $stripe_publishable_key,
											'stripe_user_id' => $stripe_user_id);
										$user_id = $_GET['state'];
										$insert_id = save_sc_params($user_id, $params);
										if ($insert_id > 0) {
											//add_filter('the_content', 'md_sc_return_success');
											$message = 'Success';
											//echo $message;
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

function idsc_revoke_creds() {
	if (isset($_POST['user_id'])) {
		$user_id = absint($_POST['user_id']);
		$delete = delete_sc_params($user_id);
	}
	exit;
}

add_action('wp_ajax_idsc_revoke_creds', 'idsc_revoke_creds');
add_action('wp_ajax_nopriv_idsc_revoke_creds', 'idsc_revoke_creds');

function idsc_get_users() {
	$users = md_sc_users();
	if (!empty($users)) {
		$new_users = array();
		foreach ($users as $user) {
			$user_id = $user->user_id;
			$wp_user = get_user_by('id', $user_id);
			if (!empty($wp_user)) {
				$email = $wp_user->user_email;
				$fname = $wp_user->user_firstname;
				$lname = $wp_user->user_lastname;
				$user->display = $fname.' '.$lname.', '.$email;
			}
			$new_users[] = $user;
		}
	}
	if (isset($new_users)) {
		$users = $new_users;
	}
	print_r(json_encode($users));
	exit;
}

add_action('wp_ajax_idsc_get_users', 'idsc_get_users');
add_action('wp_ajax_nopriv_idsc_get_users', 'idsc_get_users');

function idsc_assignment_list() {
	$users = md_sc_users();
	$user_array = array();
	if (count($users) > 0) {
		foreach ($users as $user) {
			$user_id = $user->user_id;
			$wp_user = get_user_by('id', $user_id);
			if (!empty($wp_user)) {
				$user_array[] = $wp_user;
			}
		}
	}
	//print_r(json_encode($user_array));
	$products = ID_Member_Level::get_levels();
	$product_array = array();
	if (count($products) > 0) {
		foreach ($products as $product) {
			$product_array[] = $product;
		}
	}
	print_r(json_encode(array('users' => $user_array, 'products' => $product_array)));
	exit;
}

add_action('wp_ajax_idsc_assignment_list', 'idsc_assignment_list');
add_action('wp_ajax_nopriv_idsc_assignment_list', 'idsc_assignment_list');
?>