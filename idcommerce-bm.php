<?php
add_action('init', 'md_check_show_bm');

function md_check_show_bm() {
	$settings = get_option('memberdeck_gateways');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		if (is_array($settings)) {
			$ebm = $settings['ebm'];
			if ($ebm == 1) {
				add_action('md_payment_settings_extrafields', 'md_ebm_signup', 1, 1);
				$bm_settings = get_option('md_bm_settings', 0);
				if (!empty($bm_settings)) {
					$bm_fee = $bm_settings['bm_fee'];
					$fee_type = $bm_settings['fee_type'];
					if ($bm_fee > 0) {
						$fee_payer = $bm_settings['fee_payer'];
						$fee_type = $bm_settings['fee_type'];
						if ($fee_type == 'percentage') {
							$message = __('You will be charged an additional transaction fee of ', 'memberdeck').$bm_fee.__('%, which will be added to the total and reflect on your credit card statement.', 'memberdeck');
						}
						else {
							$message = __('You will be charged an additional transaction fee of $', 'memberdeck').number_format($bm_fee / 100, 2, '.', ',').__(', which will be added to the total and reflect on your credit card statement.', 'memberdeck');
						}
						if ($fee_payer == 'buyer') {
							add_filter('md_purchase_footer', function($content) use ($message) {
								return $content.'<p class="fee-note">'.$message.'</p>';
							});
						}
					}
				}
			}
		}
	}
}


function md_ebm_signup($payment_settings) {
	if (is_user_logged_in()) {
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		$fields = array(
			array(
				'label' => __('Name on Bank Account', 'memberdeck'),
				'value' => (isset($payment_settings['b_account_name']) ? $payment_settings['b_account_name'] : ''),
				'type' => 'text',
				'name' => 'b_account_name',
				'class' => 'required text',
				'wclass' => 'form-row'
			),
			array(
				'label' => __('Account Number', 'memberdeck'),
				'value' => (isset($payment_settings['b_account_number']) ? $payment_settings['b_account_number'] : ''),
				'type' => 'text',
				'name' => 'b_account_number',
				'class' => 'required text',
				'wclass' => 'form-row'
			),
			array(
				'label' => __('Routing Number', 'memberdeck'),
				'value' => (isset($payment_settings['b_routing_number']) ? $payment_settings['b_routing_number'] : ''),
				'type' => 'text',
				'name' => 'b_routing_number',
				'class' => 'required text',
				'wclass' => 'form-row'
			),
			array(
				'label' => __('Account Type', 'memberdeck'),
				'value' => (isset($payment_settings['b_account_type']) ? $payment_settings['b_account_type'] : ''),
				'type' => 'select',
				'name' => 'b_account_type',
				'class' => 'required select',
				'wclass' => 'form-row',
				'options' => 
					array(
						array('value' => 'checking', 'title' => __('Checking', 'memberdeck')),
						array('value' => 'savings', 'title' => __('Savings', 'memberdeck'))
				)
			),
		);
		$form = new MD_Form($fields);
		$output = $form->build_form();
		include_once 'templates/_bmSignup.php';
	}
}

add_action('md_payment_settings_post', 'md_ebm_settings', 1, 2);

function md_ebm_settings($values, $user) {
	$payment_settings = array();
	if (isset($values)) {
		$ebm_settings = array();
		if (isset($values['b_account_name'])) {
			$ebm_settings['b_account_name'] = esc_attr($values['b_account_name']);
		}
		if (isset($values['b_account_number'])) {
			$account_number = esc_attr($values['b_account_number']);
			$length = strlen($account_number) - 4;
			$last_four = substr($account_number, -4, 4);
			$ebm_settings['b_account_number'] = str_repeat('*', $length).$last_four;
		}
		if (isset($values['b_routing_number'])) {
			$ebm_settings['b_routing_number'] = esc_attr($values['b_routing_number']);
		}
		if (isset($values['b_account_type'])) {
			$ebm_settings['b_account_type'] = esc_attr($values['b_account_type']);
		}
		$user_id = $user->ID;
		$email = $user->user_email;
		// let's validate
		$settings = get_option('memberdeck_gateways');
		if (!empty($settings)) {
			if (!is_array($settings)) {
				$settings = unserialize($settings);
			}
			if (is_array($settings)) {
				$test = $settings['test'];
			}
		}
		if ($test == '1') {
			$bk = $settings['btk'];
			$burl = $settings['bturl'];
		}
		else {
			$bk = $settings['bk'];
			$burl = $settings['burl'];
		}
		$balanced_customer_id = balanced_customer_id();

		require("lib/Balanced/Httpful/Bootstrap.php");
		require("lib/Balanced/RESTful/Bootstrap.php");
		require("lib/Balanced/Bootstrap.php");

		Httpful\Bootstrap::init();
		RESTful\Bootstrap::init();
		Balanced\Bootstrap::init();

		Balanced\Settings::$api_key = $bk;
		if (empty($balanced_customer_id)) {
			try {
				$customer = new \Balanced\Customer(array(
	  				"name" => $ebm_settings['b_account_name'],
	  				"email" => $email,
				));
				$customer->save();
				if (isset($customer)) {
					$balanced_customer_id = $customer->id;
					update_user_meta($user_id, 'balanced_customer_id');
					try {
						$bank_account = $customer->addBankAccount(array(
							'name' => $ebm_settings['b_account_name'],
							'account_number' => $account_number,
							'routing_number' => $ebm_settings['b_routing_number'],
							'account_type' => $ebm_settings['b_account_type']
						));
						if (isset($bank_account)) {
							$bank_account->save();
							$account_id = $bank_account->id;
							update_user_meta($user_id, 'bm_bank_account', $bank_account);
						}
					}
					catch (Exception $e) {
						//print_r($e);
						$message = $e->response->body->description;
						add_filter('md_payment_settings_error', function($content) use ($message) {
							return $content.'<p>'.$message.'</p>';
						});
					}	
				}
			}
			catch (Exception $e) {
					echo $e;
				}
		}
		else {
			try {
				$customer = \Balanced\Customer::get("/v1/customers/".$balanced_customer_id);
				$account_id = get_user_meta($user_id, 'bm_bank_account', true);
			}
			catch (Exception $e) {
				echo $e;
			}
			if (!empty($account_id)) {
				try {
					$bank_account = Balanced\BankAccount::get("/v1/bank_accounts/".$account_id);
					if (!empty($bank_account)) {
						$bank_account->unstore();
					}
				}
				catch (Exception $e) {
					// No account to be deleted
					//echo $e;
				}
			}
			try {
				$bank_account = $customer->addBankAccount(array(
					'name' => $ebm_settings['b_account_name'],
					'account_number' => $account_number,
					'routing_number' => $ebm_settings['b_routing_number'],
					'account_type' => $ebm_settings['b_account_type']
				));
				if (isset($bank_account)) {
					$bank_account->save();
					$bank_accounts = $customer->activeBankAccount();
					$uri = $bank_accounts->uri;
					$uri_array = explode('/', $uri);
					$account_id = $uri_array[5];
					update_user_meta($user_id, 'bm_bank_account', $account_id);
				}
			}
			catch (Exception $e) {
				//print_r($e);
				$message = $e->response->body->description;
				add_filter('md_payment_settings_error', function($content) use ($message) {
					return $content.'<p>'.$message.'</p>';
				});
			}
		}
		add_filter('md_payment_settings', function($settings) use ($ebm_settings) {
			if (isset($settings)) {
				$payment_settings = array_merge($settings, $ebm_settings);
				return $payment_settings;
			}
		});
	}
}

add_action('memberdeck_payment_success', 'md_bm_payouts_tab', 1, 5);

function md_bm_tab($user_id, $order_id, $paykey, $fields, $source) {
	if (isset($source) && $source == 'balanced') {
		$order = new ID_Member_Order($order_id);
		$order_info = $order->get_order();
		if (isset($order_info)) {
			$level_id = $order_info->level_id;
			$owner = get_option('md_level_'.$level_id.'_owner');
			if (isset($owner)) {
				$new_tab = md_bm_set_tab($order_id);
			}
		}
	}
}

function md_bm_set_tab($order_id) {
	global $wpdb;
	$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'bm_payouts (order_id, status) VALUES (%d, %s)', $order_id, 'UNPAID');
	$res = $wpdb->query($sql);
	return $res;
}

function md_bm_get_tabs($level_id) {
	global $wpdb;
	$sql = 'SELECT * FROM '.$wpdb->prefix.'bm_payouts LEFT JOIN '.$wpdb->prefix.'memberdeck_orders ON ('.$wpdb->prefix.'bm_payouts.order_id='.$wpdb->prefix.'memberdeck_orders.id) WHERE level_id = '.$level_id;
	$res = $wpdb->get_results($sql);
	return $res;
}

function md_bm_clear_tabs($level_id) {
	$tabs = md_bm_get_tabs($level_id);
	foreach ($tabs as $tab) {
		$order_id = $tab->order_id;
		md_bm_clear_tab($order_id);
	}
}

function md_bm_clear_tab($order_id) {
	global $wpdb;
	$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'bm_payouts SET payout_status = %s WHERE order_id = %d', 'PAID', $order_id);
	$res = $wpdb->query($sql);
}

function md_bm_process_prep() {
	$response = array('orders' => '0', 'total' => '0', 'name' => '', 'customer_id' => '');
	if (isset($_POST['ID'])) {
		$level_id = absint($_POST['ID']);
		$user_id = get_option('md_level_'.$level_id.'_owner');
		if (!empty($user_id)) {
			$user = get_user_by('id', $user_id);
			$name = $user->first_name.' '.$user->last_name;
			$bank_account = get_user_meta($user_id, 'bm_bank_account');
			$orders = md_bm_get_tabs($level_id);
			if (!empty($orders)) {
				$bm_settings = get_option('md_bm_settings', 0);
				if (!empty($bm_settings)) {
					$bm_fee = $bm_settings['bm_fee'];
					$fee_type = $bm_settings['fee_type'];
					if ($bm_fee > 0) {
						$fee_payer = $bm_settings['fee_payer'];
						$fee_type = $bm_settings['fee_type'];
					}
				}
				$total = 0;
				$count = 0;
				foreach ($orders as $order) {
					if ($order->payout_status == 'UNPAID') {
						if ($fee_type == 'percentage') {
							$price = $order->price / ((100 + $bm_fee) / 100);
							$total = $price + $total;
						}
						else {
							// flat
							$price = (($order->price * 100) - $bm_fee) / 100;
							$total = $price + $total;
						}
						$count = $count + 1;
					}
				}
				$response['orders'] = number_format($count);
				$response['total'] = number_format($total, 2, '.', ',');
				$response['name'] = $name;
				$response['customer_id'] = balanced_customer_id_ajax($user_id);
			}
		}
	}
	print_r(json_encode($response));
	exit;
}

add_action('wp_ajax_md_bm_process_prep', 'md_bm_process_prep');
add_action('wp_ajax_nopriv_md_bm_process_prep', 'md_bm_process_prep');

function md_bm_process_payout() {
	$response = array('status' => __('failure', 'memberdeck'), 'message' => '');
	if (isset($_POST['ID'])) {
		$level_id = absint($_POST['ID']);
	}
	if (isset($_POST['Payout'])) {
		$payout = esc_attr($_POST['Payout']);
	}
	if (isset($level_id) && isset($payout)) {
		$user_id = get_option('md_level_'.$level_id.'_owner');
		if (!empty($user_id)) {
			$bank_account = get_user_meta($user_id, 'bm_bank_account');
			$settings = get_option('memberdeck_gateways');
			if (!empty($settings)) {
				if (!is_array($settings)) {
					$settings = unserialize($settings);
				}
				if (is_array($settings)) {
					$test = $settings['test'];
				}
			}
			if ($test == '1') {
				$bk = $settings['btk'];
				$burl = $settings['bturl'];
			}
			else {
				$bk = $settings['bk'];
				$burl = $settings['burl'];
			}
			require("lib/Balanced/Httpful/Bootstrap.php");
			require("lib/Balanced/RESTful/Bootstrap.php");
			require("lib/Balanced/Bootstrap.php");

			Httpful\Bootstrap::init();
			RESTful\Bootstrap::init();
			Balanced\Bootstrap::init();

			Balanced\Settings::$api_key = $bk;
			$balanced_customer_id = balanced_customer_id_ajax($user_id);
			$customer = Balanced\Customer::get("/v1/customers/".$balanced_customer_id);
			if (isset($customer)) {
				try {
					$customer->credit($payout * 100);
					$response['status'] = __('success', 'memberdeck');
					md_bm_clear_tabs($level_id);
				}
				catch (Exception $e) {
					$response['message'] = $e->response->body->description;
				}
			}
		}
	}
	print_r(json_encode($response));
	exit;
}

add_action('wp_ajax_md_bm_process_payout', 'md_bm_process_payout');
add_action('wp_ajax_nopriv_md_bm_process_payout', 'md_bm_process_payout');
?>