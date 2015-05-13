<?php
/**
 * Authorize.Net class for handling all the transactions
 */
class ID_Authorize_Net {
	private $auth_api_login;
	private $auth_transaction_key;
	private $test_mode;
	private $customer_profile_id;
	private $customer_payment_profile_id;
	private $error_exists;
	
	function __construct($api_login, $transaction_key, $test) {
		$this->auth_api_login = $api_login;
		$this->auth_transaction_key = $transaction_key;
		$this->test_mode = $test;

		// Adding filters
		add_filter('idc_authnet_avs_info_add', array($this, 'idc_authnet_add_address'), 10, 3);
	}

	public function set_profile_ids($customer_profile_id, $customer_payment_profile_id) {
		$this->customer_profile_id = $customer_profile_id;
		$this->customer_payment_profile_id = $customer_payment_profile_id;
	}

	public function get_profile_id() {
		return $this->customer_profile_id;
	}

	public function get_payment_profile_id() {
		return $this->customer_payment_profile_id;
	}

	public function check_payment_profile_exists($fname, $lname, $email, $cc_number, $cc_expiry, $cc_code, $Fields) {
		global $avs;
		$settings = get_option('memberdeck_gateways');
		if (!empty($settings) && is_array($settings)) {
			$test = $settings['test'];
		}
		// Add new card but 1st check if it's already added, getting detail of Customer profile
		$profileRequest = new AuthorizeNetCIM;
		$profileResponse = $profileRequest->getCustomerProfile($this->customer_profile_id);

		// Customer profile exists
		if ($profileResponse->isOk()) {
			// We have the profile details, getting the payment profiles and checking the card
			$profileResponseArray = json_decode(json_encode((array)$profileResponse), TRUE);	//$profileResponse->xml->profile->paymentProfiles;
			$payment_profiles = $profileResponseArray['xml']['profile']['paymentProfiles'];
			// Get key to check if there are more than 1 profile, if 1st index is 0, then it contains more than 1 profiles, otherwise
			// it contains 1 profile only
			$profile_keys = array_keys($payment_profiles);
			if ($profile_keys[0] === 'billTo') {
				$payment_profiles = array($payment_profiles);
			}
			// echo "payment_profiles: "; print_r($payment_profiles); echo "\n";

			$last4 = substr($cc_number, -4);
			$card_exists = false;
			$index = 0;
			foreach ($payment_profiles as $payment_profile) {
				$payment_profile = json_decode(json_encode($payment_profile), FALSE);
				// echo "payment_profile: "; print_r($payment_profile); echo "\n";
				$card_last4 = substr($payment_profile->payment->creditCard->cardNumber, -4);
				$card_expiry = $payment_profile->payment->creditCard->expirationDate;
				// echo "last4: ".$last4.", cc_expiry: ".$cc_expiry.", card_last4: ".$card_last4.", card_expiry: ".$card_expiry."\n";

				// Comparing the cards, one the coming and this one in profiles
				if ($card_last4 == $last4) {
					// Same cards, no need to add new payment profile, but check the expiry first
					if ($card_expiry == $cc_expiry || $test) {
						// Same cards now
						$card_exists = true;
						$exist_index = $index;
						// break the loop as we have now what we needed, we already have the card in payment profile
						break;
					}
				}
				$index++;
			}
			// If card doesn't exist in payment profiles, create new payment profile
			if (!$card_exists) {
				$request = new AuthorizeNetCIM;

				$customerPaymentProfile = new AuthorizeNetPaymentProfile;
				$customerPaymentProfile->billTo->firstName = $fname;
				$customerPaymentProfile->billTo->lastName = $lname;
				$customerPaymentProfile->payment->creditCard->cardNumber = $cc_number;
				$customerPaymentProfile->payment->creditCard->expirationDate = $cc_expiry;
				$customerPaymentProfile->payment->creditCard->cardCode = $cc_code;
				// Action for AVS information addition
				$customerPaymentProfile = apply_filters('idc_authnet_avs_info_add', $customerPaymentProfile, $Fields, __LINE__);

				$responsePayment = $request->createCustomerPaymentProfile($this->customer_profile_id, $customerPaymentProfile);
				// echo "responsePayment: "; print_r($responsePayment);
				if ($responsePayment->isOk()) {
					$customerPaymentProfileId = $responsePayment->getPaymentProfileId();
					// $custid = $customerPaymentProfileId;
					$this->customer_payment_profile_id = $customerPaymentProfileId;
				} else {
					print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $responsePayment->getErrorMessage())));
					exit;
					// error occurred
					if ($responsePayment->getMessageCode() == "E00039") {
						// Already exists a payment profile, so get this profile, but how?
					}
				}
			}
			else {
				// Card exists, so get its payment profile id
				// $custid = $payment_profiles[$exist_index]['customerPaymentProfileId'];
				$this->customer_payment_profile_id = $payment_profiles[$exist_index]['customerPaymentProfileId'];
				// If AVS is enabled, we need to update the address in Auth.Net, but first check that we have the same address or not
				if (isset($avs) && $avs) {
					$this->update_profile_address_avs($payment_profiles[$exist_index], $Fields);
				}
			}
		}
		// No Customer profile with the id given, that means a wrong id is stored or something have gone wrong
		else {
			// Show nothing to user, just make a new customer profile and payment profile, and overwrite
			$request = new AuthorizeNetCIM;
			// Create new customer profile
			$customerProfile = new AuthorizeNetCustomer;
			$customerProfile->merchantCustomerId = time();
			$customerProfile->email = $email;
			$response = $request->createCustomerProfile($customerProfile);
			if ($response->isOk()) {
				$customerProfileId = $response->getCustomerProfileId();
				$this->customer_profile_id = $customerProfileId;

				// Now creating payment profile
				$customerPaymentProfile = new AuthorizeNetPaymentProfile;
				$customerPaymentProfile->billTo->firstName = $fname;
				$customerPaymentProfile->billTo->lastName = $lname;
				$customerPaymentProfile->payment->creditCard->cardNumber = $cc_number;
				$customerPaymentProfile->payment->creditCard->expirationDate = $cc_expiry;
				$customerPaymentProfile->payment->creditCard->cardCode = $cc_code;
				// Action for AVS information addition
				$customerPaymentProfile = apply_filters('idc_authnet_avs_info_add', $customerPaymentProfile, $_POST['Fields'], __LINE__);

				$responsePayment = $request->createCustomerPaymentProfile($customerProfileId, $customerPaymentProfile);
				if ($responsePayment->isOk()) {
					$customerPaymentProfileId = $responsePayment->getPaymentProfileId();
					// $custid = $customerPaymentProfileId;
					$this->customer_payment_profile_id = $customerPaymentProfileId;
				}
				else {
					// Customer payment profile couldn't be created
					print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $response->getErrorMessage())));
					exit;
				}
			}
			else {
				// Customer profile couldn't be created
				print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $response->getErrorMessage())));
				exit;
			}
		}
	}

	public function create_charge_token($level_price) {
		$price = str_replace(',', '', $level_price);
		$transaction = new AuthorizeNetTransaction;
		$transaction->amount = $price;
		$transaction->customerPaymentProfileId = $this->customer_payment_profile_id;
		$transaction->customerProfileId = $this->customer_profile_id;
		$transaction->order->invoiceNumber = time();

		$request = new AuthorizeNetCIM;
		$response = $request->createCustomerProfileTransaction('AuthOnly', $transaction);
		if ($response->isOk()) {
			$transactionResponse = $response->getTransactionResponse();
			$charge_token = $transactionResponse->authorization_code;
			// echo "transaction done\n";
		} else if ($response->isError()) {
			print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => __('Could not create charge token', 'memberdeck').': '.__LINE__)));
			exit();
		}

		return $charge_token;
	}

	public function update_profile_address_avs($payment_profile, $fields) {
		foreach ($fields as $field) {
			// Address 1 field storing
			if ($field['name'] == "idc_address_1") {
				$idc_address_1 = sanitize_text_field($field['value']);
			}
			// Address 2 field storing
			if ($field['name'] == "idc_address_2") {
				$idc_address_2 = '';
				if (!empty($field['value'])) {
					$idc_address_2 = sanitize_text_field($field['value']);
				}
			}
			// State field storing
			if ($field['name'] == "idc_address_city") {
				$idc_address_city = sanitize_text_field($field['value']);
			}
			// City field storing
			if ($field['name'] == "idc_address_state") {
				$idc_address_state = sanitize_text_field($field['value']);
			}
			// Zip code field storing
			if ($field['name'] == "idc_address_zip_code") {
				$idc_address_zip_code = sanitize_text_field($field['value']);
			}
		}

		// Check that posted address fields match in already stored profile
		if  (
				$payment_profile['billTo']['address'] == ($idc_address_1 . " " . $idc_address_2) &&
				$payment_profile['billTo']['city'] == $idc_address_city &&
				$payment_profile['billTo']['state'] == $idc_address_state &&
				$payment_profile['billTo']['zip'] == $idc_address_zip_code
			) {
			// Addresses has matched so no need to update
		}
		else {
			$this->update_payment_profile_address( $fields, $idc_address_1 . " " . $idc_address_2, $idc_address_city, $idc_address_state, $idc_address_zip_code );
		}
	}

	public function update_payment_profile_address($fields, $address="", $city="", $state="", $zip_code="") {
		global $avs;
		// Update only if $avs is set and true
		if (isset($avs) && $avs) {
			// Getting the payment profile
			$paymentProfileRequest = new AuthorizeNetCIM;
			$storedPaymentProfile = $paymentProfileRequest->getCustomerPaymentProfile($this->customer_profile_id, $this->customer_payment_profile_id);
			// echo "update_payment_profile_address() storedPaymentProfile: "; print_r($storedPaymentProfile); echo "\n";
			
			$paymentProfile = new AuthorizeNetPaymentProfile;	//$storedPaymentProfile->xml->paymentProfile;
			// Adding address to Payment Profile Id
			$billingAddress = new AuthorizeNetAddress;
			$billingAddress->firstName = (string) $storedPaymentProfile->xml->paymentProfile->billTo->firstName;
			$billingAddress->lastName = (string) $storedPaymentProfile->xml->paymentProfile->billTo->lastName;
			$billingAddress->address = $address;
			$billingAddress->zip = $zip_code;
			$billingAddress->city = $city;
			$billingAddress->state = $state;
			// Storing the new billing address
			$paymentProfile->billTo = $billingAddress;
			
			// Storing the $paymentProfile->payment into AuthorizeNetPayment class object
			$paymentProfile->payment->creditCard->cardNumber = (string) $storedPaymentProfile->xml->paymentProfile->payment->creditCard->cardNumber;
			$paymentProfile->payment->creditCard->expirationDate = (string) $storedPaymentProfile->xml->paymentProfile->payment->creditCard->expirationDate;
			// Unsetting bank account information
			unset ($paymentProfile->payment->bankAccount);
			// echo "update_payment_profile_address() paymentProfile: "; print_r($paymentProfile); echo "\n";
			
			$paymentProfile = apply_filters('idc_authnet_avs_info_add', $paymentProfile, $fields, __LINE__);
	
			$addressRequest = new AuthorizeNetCIM;
			$addressResponse = $addressRequest->updateCustomerPaymentProfile($this->customer_profile_id, $this->customer_payment_profile_id, $paymentProfile);
			// echo "update_payment_profile_address() addressResponse: "; print_r($addressResponse); echo "\n";
			if ($addressResponse->isOk()) {
				return true;
			}
			else {
				// Error occurring while updating the address
				// print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $addressResponse->getErrorMessage(), "line" => "youthfront.php:129")));
				// exit();
			}
		}
	}

	/**
	 * Filters relating to Auth.Net
	 */
	// Filter for adding address to customer payment profile being created
	public function idc_authnet_add_address($customer_payment_profile, $fields, $line) {
		global $avs;
		// if AVS (Address verification system) is turned on, add address in billing
		if (isset($avs) && $avs) {
			// echo "fields: "; print_r($fields); echo "\n";
			foreach ($fields as $field) {
				// Address 1 field storing
				if ($field['name'] == "idc_address_1") {
					$idc_address_1 = sanitize_text_field($field['value']);
				}
				// Address 2 field storing
				if ($field['name'] == "idc_address_2") {
					if (!empty($field['value'])) {
						$idc_address_2 = sanitize_text_field($field['value']);
					}
				}
				// State field storing
				if ($field['name'] == "idc_address_city") {
					$idc_address_city = sanitize_text_field($field['value']);
				}
				// City field storing
				if ($field['name'] == "idc_address_state") {
					$idc_address_state = sanitize_text_field($field['value']);
				}
				// Zip code field storing
				if ($field['name'] == "idc_address_zip_code") {
					$idc_address_zip_code = sanitize_text_field($field['value']);
				}
			}

			$address_1 = apply_filters('idc_authnet_address_1_filter', $idc_address_1);
			$address_2 = apply_filters('idc_authnet_address_2_filter', ((isset($idc_address_2)) ? $idc_address_2 : ''));
			$address_city = apply_filters('idc_authnet_address_city_filter', $idc_address_city);
			$address_state = apply_filters('idc_authnet_address_state_filter', $idc_address_state);
			$address_zip_code = apply_filters('idc_authnet_address_zip_code_filter', $idc_address_zip_code);

			// Adding it to payment_profile
			$customer_payment_profile->billTo->address = $address_1 . " " . $address_2;
			$customer_payment_profile->billTo->city = $address_city;
			$customer_payment_profile->billTo->state = $address_state;
			$customer_payment_profile->billTo->zip = $address_zip_code;

			unset ($customer_payment_profile->payment->bankAccount);
		}
		// echo "customer_payment_profile: "; print_r($customer_payment_profile); echo "\n";

		return $customer_payment_profile;
	}
}