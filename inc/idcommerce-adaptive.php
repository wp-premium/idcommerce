<?php
function adaptive_pay_request($item, $fields) {
	update_option('apr1', 1);
	require IDC_PATH.'lib/PayPalAdaptive/lib/vendor/autoload.php';
	$tz = get_option('timezone_string');
    if (empty($tz)) {
        $tz = 'UTC';
    }
    date_default_timezone_set($tz);
	$permalink_structure = get_option('permalink_structure');
	$prefix = '?';
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	$https = false;
	if ((isset($_SERVER['https']) && $_SERVER['https'] == 'on') || $_SERVER['SERVER_PORT'] == 443) {
		$https = true;
	}
	$fields['price'] = $price;
	$query_string = http_build_query($query_string);
	$gateways = get_option('memberdeck_gateways');
	$level = ID_Member_Level::get_level($item->product_id);
	// PayPal adaptive Classic API keys
	if ($gateways['test'] == 1) {
		$ppada_currency = $gateways['ppada_currency'];
		$ppadap_api_username = $gateways['ppadap_api_username_test'];
		$ppadap_api_password = $gateways['ppadap_api_password_test'];
		$ppadap_api_signature = $gateways['ppadap_api_signature_test'];
		$ppadap_app_id = $gateways['ppadap_app_id_test'];
		$ppadap_receiver_email = $gateways['ppadap_receiver_email_test'];
	} else {
		$ppada_currency = $gateways['ppada_currency'];
		$ppadap_api_username = $gateways['ppadap_api_username'];
		$ppadap_api_password = $gateways['ppadap_api_password'];
		$ppadap_api_signature = $gateways['ppadap_api_signature'];
		$ppadap_app_id = $gateways['ppadap_app_id'];
		$ppadap_receiver_email = $gateways['ppadap_receiver_email'];
	}

	$chained = false;
	if ($gateways['epp_fes'] && function_exists('is_id_pro') && is_id_pro()) {
		$check_claim = get_option('md_level_'.$item->product_id.'_owner');
		if (!empty($check_claim)) {
			$payment_settings = apply_filters('md_payment_settings', get_user_meta($check_claim, 'md_payment_settings', true));
			if (!empty($payment_settings)) {
				// we process this now, since it is during pay request
				$secondary_receiver = $payment_settings['paypal_email'];
				$enterprise_settings = get_option('idc_enterprise_settings');
				if (!empty($enterprise_settings) && !empty($secondary_receiver)) {
					$chained = true;
					$fee_type = (isset($enterprise_settings['fee_type']) ? $enterprise_settings['fee_type'] : 'flat');
					$enterprise_fee = (isset($enterprise_settings['enterprise_fee']) ? $enterprise_settings['enterprise_fee'] : null);
				}
			}
		}
	}
	$receiver = array();
	if (!$chained) {
		$receiver[0] = new \PayPal\Types\AP\Receiver();
		$receiver[0]->email = $ppadap_receiver_email;
		$receiver[0]->amount = $item->price;
	}
	else {
		//  add chained payment details and remove fee
		$standard_price = $item->price;
		if ($fee_type == 'percentage') {
			$enterprise_fee = floatval($item->price) * (floatval($enterprise_fee) / 100);
		}
		else {
			$enterprise_fee = $enterprise_fee / 100;
		}
		$receiver[0] = new \PayPal\Types\AP\Receiver();
		$receiver[0]->email = $ppadap_receiver_email;
		$receiver[0]->amount = $item->price;
		$receiver[0]->primary = true;
		$receiver[1] = new \PayPal\Types\AP\Receiver();
		$receiver[1]->email = $secondary_receiver;
		$receiver[1]->amount = $item->price - $enterprise_fee;
	}
	try {
		update_option('apr2', 2);
		$receiverList = new \PayPal\Types\AP\ReceiverList($receiver);
	}
	catch (Exception $e) {
		$error = $e->getMessage();
	}
	try {
		update_option('apr3', 3);
		$payRequest = new \PayPal\Types\AP\PayRequest(new \PayPal\Types\Common\RequestEnvelope("en_US"), 'PAY', home_url('/'), $ppada_currency, $receiverList, home_url('/'));
	}
	catch (Exception $e) {
		$error = $e->getMessage();
	}
	// (Optional) The URL to which you want all IPN messages for this payment to be sent. Maximum length: 1024 characters 
	$payRequest->ipnNotificationUrl = home_url('/').$prefix.'memberdeck_notify=pp_adaptive&user_id=&user_email='.$item->user['email'].'&user_fname='.$item->user['first_name'].'&user_lname='.$item->user['last_name'].'&product_id='.$item->product_id.'&stringvar='.$query_string;
	$payRequest->preapprovalKey  = $item->key;
	$payRequest->feesPayer = "EACHRECEIVER";
	$payRequest->memo = (isset($level) ? $level->level_name : '');
	// $payRequest->senderEmail  = 'platfo_1255077030_biz@gmail.com';

	$config = array(
		"mode" => (($gateways['test'] == 1) ? "sandbox" : "live"),
		// Signature Credential
		"acct1.UserName" => $ppadap_api_username,
		"acct1.Password" => $ppadap_api_password,
		"acct1.Signature" => $ppadap_api_signature,
		"acct1.AppId" => $ppadap_app_id
	);
	try {
		update_option('apr4', 4);
		$service = new \PayPal\Service\AdaptivePaymentsService($config);
		$response = $service->Pay($payRequest);
		if (strtoupper($response->responseEnvelope->ack) == "SUCCESS") {
			update_option('apr5', 5);
			return 1;
		}
		else {
			$error = $response->error[0]->message;
			ID_Member_Order::update_order_meta($item->order_id, 'preauth_error', serialize($error));
			update_option('apr6', 6);
			return $error;
		}
	}
	catch (Exception $e) {
		$error = $e->getMessage();
	}
	return (isset($error) ? $error : $response);
}
?>