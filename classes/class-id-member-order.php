<?php
class ID_Member_Order {
	var $id;
	var $user_id;
	var $level_id; 

	var $order_date;
	var $transaction_id;
	var $subscription_id;
	var $status;
	var $e_date;
	var $price;

	function __construct( 
		$id = null,
		$user_id = null,
		$level_id = null,
		$order_date = null,
		$transaction_id = 'admin',
		$subscription_id = null,
		$status = 'active',
		$e_date = null,
		$price = '0'
		)
	{
		$this->id = $id;
		$this->user_id = $user_id;
		$this->level_id = $level_id;
		$this->order_date = date('Y-m-d H:i:s');
		$this->transaction_id = $transaction_id;
		$this->subscription_id = $subscription_id;
		$this->status = $status;
		$this->e_date = $e_date;	
		$this->price = $price;
	}

	function add_order() {
		if (empty($this->e_date)) {
			$this->e_date = $this->get_e_date();
		}
		global $wpdb;
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_orders (user_id, 
			level_id, 
			order_date, 
			transaction_id,
			subscription_id,
			status,
			e_date,
			price) VALUES (%d, %d, %s, %s, %s, %s, %s, %s)', 
		$this->user_id, 
		$this->level_id, 
		$this->order_date, 
		$this->transaction_id,
		$this->subscription_id,
		$this->status,
		$this->e_date,
		$this->price);
		$res = $wpdb->query($sql);
		$insert_id = $wpdb->insert_id;
		// $wpdb->print_error();
		if (isset($insert_id)) {
			$order = array('level_id' => $this->level_id,
				'user_id' => $this->user_id);
			ID_Member_Credit::new_order_credit($order);
			return $insert_id;
		}
		else {
			return null;
		}
	}

	function update_order() {
		if (empty($this->e_date)) {
			$this->e_date = &$this->get_e_date();
		}
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_orders SET user_id = %d, 
			level_id = %d, 
			order_date = %s, 
			transaction_id = %s, 
			subscription_id = %s,
			status = %s,
			e_date = %s,
			price = %s WHERE id = %d', 
			$this->user_id, 
			$this->level_id, 
			$this->order_date, 
			$this->transaction_id,
			$this->subscription_id,
			$this->status,
			$this->e_date,
			$this->price, 
			$this->id);
		$res = $wpdb->query($sql);
	}

	function delete_order() {
		global $wpdb;
		$sql = 'DELETE FROM '.$wpdb->prefix.'memberdeck_orders WHERE id = '.$this->id;
		$res = $wpdb->query($sql);
	}

	function get_order() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders WHERE id = %d', $this->id);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	function get_last_order() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders WHERE user_id = %d AND level_id = %d ORDER BY id DESC', $this->user_id, $this->level_id);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	function get_transaction() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders WHERE transaction_id = %s', $this->transaction_id);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	function get_e_date() {
		$exp_level = ID_Member_Level::get_level($this->level_id);
		$level_type = $exp_level->level_type;
		if ($level_type == 'standard') {
			//if ($exp_level->level_price > 0) {
				$exp = strtotime('+1 years');
				$e_date = date('Y-m-d H:i:s', $exp);
			//}
			/*else {
				$e_date = null;
			}*/
		}
		else if ($level_type == 'recurring') {
			$recurring_type = $exp_level->recurring_type;
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
			$e_date = date('Y-m-d H:i:s', $exp);
		}
		else {
			$e_date = null;
		}
		return $e_date;
	}

	function get_subscription() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders WHERE subscription_id = %s', $this->subscription_id);
		$res = $wpdb->get_row($sql);
		return ($res);
	}

	function set_subscription($subscription_number) {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_orders SET subscription_number = %d WHERE id = %d', $subscription_number, $this->id);
		$res = $wpdb->query($sql);
	}

	function cancel_status($e_date = null) {
		global $wpdb;
		if (empty($e_date)) {
			$e_date = date('Y-m-d H:i:s');
		}
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_orders SET status = "cancelled", e_date = %s WHERE id = %d', $e_date, $this->id);
		$res = $wpdb->query($sql);
	}

	/**
	 * get_order_currency_sym(): Function to get the currency symbol of a payment gateway of an order
	 * No param is required
	 * Should be called from an order object
	 */
	public static function get_order_currency($source) {
		global $global_currency;
		$currency_code = 'USD';
		// Getting the memberdeck options to get the gateway settings
		$gateway_options = get_option( "memberdeck_gateways" );
		if ($source == "stripe") {
			$currency_code = $gateway_options['stripe_currency'];
		} 
		else if ($source == "paypal") {
			$currency_code = $gateway_options['pp_currency'];
		}
		else if ($source == 'coinbase') {
			$currency_code = $global_currency;
		}
		else if ($source == 'mc') {
			$currency_code = $global_currency;
		}
		return $currency_code;
	}

	public static function get_order_currency_sym($order_id, $order_meta = "") {
		global $global_currency;
		// If the 2nd argument don't have anything, then get order-meta from db
		if (empty($order_meta)) {
			$order_meta = ID_Member_Order::get_order_meta($order_id, 'gateway_info', true);
		}
		if (!empty($order_meta['currency_code'])) {
			$currency_code = $order_meta['currency_code'];
		}
		else {
			// might not have been added to db yet
			$currency_code = $global_currency;
		}
		// Converting shortcode to symbol and return, but first getting the array for code to symbol from file
		$currency_json = json_decode(file_get_contents(IDC_PATH . "templates/admin/currencies.json"));
		$currencies_array = $currency_json->currency;
		// since currency array is for paypal only, we add some additional
		$btc = new stdClass();
		$btc->code = 'BTC';
		$btc->symbol = '&#3647;';
		$currencies_array[] = $btc;
		// now that we have the array, we loop through it and compare the code and return the required symbol if code is matched
		foreach ($currencies_array as $currency) {
			if ($currency->code == $currency_code) {
				return $currency->symbol;
			}
		}
		return '$';
	}

	/**
	 * Filter whether to retrieve metadata of a specific type.
	 *
	 * @param integer           $order_id  Order id for which meta data is needed
	 * @param null|array|string $value     The value get_order_meta() should
	 *                                     return - a single metadata value,
	 *                                     or an array of values.
	 * @param string            $meta_key  Meta key.
	 * @param string|array      $single    Meta value, or an array of values.
	 */
	public static function get_order_meta($order_id, $meta_key, $single = true) {
		if ( !$order_id = absint($order_id) )
			return "";

		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_order_meta WHERE order_id = %s AND meta_key = %s', $order_id, $meta_key);
		$res = $wpdb->get_row($sql);

		if (empty($res))
			return "";
			
		if ( isset($res->meta_key) ) {
			if ( $single )
				return maybe_unserialize($res->meta_value);
			else
				return array_map('maybe_unserialize', $res->meta_value);
		}

		if ($single)
			return '';
		else
			return array();
	}

	/**
	 * Function to store metadata values for the order
	 * @param integer           $object_id  Order id against which meta data to be stored
	 * @param string 	 		$meta_key 	Meta key against which value to be stored
	 * @param string|integer 	$meta_value	Stores the value against the key
	 * @param datatype 			$prev_value If need to update it if different from previous value
	 * @param booleon           $unique     If need to store value against a key only once
	 */
	public static function update_order_meta($order_id, $meta_key, $meta_value, $prev_value = '', $unique = false) {
		if (empty($meta_key) )
			return;

		global $wpdb;

		// expected_slashed ($meta_key)
		$meta_key = wp_unslash($meta_key);
		$passed_value = $meta_value;
		$meta_value = wp_unslash($meta_value);

		if ( empty($prev_value) ) {
			$old_value = self::get_order_meta($order_id, $meta_key);
			if ( $old_value !== "" ) {
				if ( $old_value === $meta_value )
					return false;
			}
		}

		if ( ! $meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM ".$wpdb->prefix."memberdeck_order_meta WHERE meta_key = %s AND order_id = %d", $meta_key, $order_id ) ) ) {
			return self::add_metadata($order_id, $meta_key, $passed_value);
		}

		$meta_value = maybe_serialize( $meta_value );
		$data  = compact( 'meta_value' );
		$where = array( 'id' => $order_id, 'meta_key' => $meta_key );
		if ( !empty( $prev_value ) ) {
			$prev_value = maybe_serialize($prev_value);
			$where['meta_value'] = $prev_value;
		}

		$result = $wpdb->update( $wpdb->prefix."memberdeck_order_meta", $data, $where );
		if ( ! $result )
			return false;
	}

	public static function add_metadata($order_id, $meta_key, $meta_value, $unique = false) {
		if ( empty($meta_key ))
			return;

		global $wpdb;
		// expected_slashed ($meta_key)
		$meta_key = wp_unslash($meta_key);
		$meta_value = wp_unslash($meta_value);

		if ( $unique && $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."memberdeck_order_meta WHERE meta_key = %s AND order_id = %d", $meta_key, $order_id ) ) ) {
			return false;
		}

		$meta_value = maybe_serialize( $meta_value );
		$result = $wpdb->insert( $wpdb->prefix."memberdeck_order_meta", array(
			'order_id' => $order_id,
			'meta_key' => $meta_key,
			'meta_value' => $meta_value
		) );

		if ( ! $result )
			return false;
	}
	public static function get_order_count() {
		global $wpdb;
		$sql = 'SELECT COUNT(*) as count FROM '.$wpdb->prefix.'memberdeck_orders';
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function get_orders($search = null, $limit = null, $misc = null) {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_orders'.$misc.(!empty($search) ? ' WHERE transaction_id LIKE "'.$search.'"' : '').(!empty($limit) ? ' LIMIT '.$limit : '');
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function get_orders_by_user($user_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders WHERE user_id = %s', $user_id);
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function get_orders_by_level($level_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders WHERE level_id = %d', $level_id);
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function add_preorder($order_id, $charge_token, $source) {
		global $wpdb;
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_preorder_tokens (order_id, charge_token, gateway) VALUES (%d, %s, %s)', $order_id, $charge_token, $source);
		$res = $wpdb->query($sql);
		if (isset($res)) {
			return $wpdb->insert_id;
		}
		else {
			return null;
		}
	}

	public static function get_preorders_by_userid($user_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders WHERE user_id = %d AND transaction_id = %s', $user_id, 'pre');
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function get_preorder_by_orderid($order_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_preorder_tokens WHERE order_id = %d', $order_id);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function get_preorder_by_token($token) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_preorder_tokens WHERE charge_token = %s', $token);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function get_subscription_by_sub($sub_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders WHERE subscription_id = %s', $sub_id);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function cancel_subscription($id) {
		global $wpdb;
		$tz = get_option('timezone_string');
	    if (empty($tz)) {
	        $tz = 'UTC';
	    }
	    date_default_timezone_set($tz);
		$date = date('Y-m-d H:i:s');
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_orders SET e_date = %s, status = "cancelled" WHERE id = %d', $date, $id);
		$res = $wpdb->query($sql);
		return $sql;
	}

	public static function check_order_exists($txn_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders WHERE transaction_id = %s', $txn_id);
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function update_order_date($id, $date) {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_orders SET e_date = %s WHERE id = %d', $date, $id);
		$res = $wpdb->query($sql);
	}

	public static function get_md_preorders($level_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders WHERE transaction_id = "pre" and level_id = %d', $level_id);
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function update_txn_id($id, $txn_id) {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_orders SET transaction_id = %s WHERE id = %d', $txn_id, $id);
		$res = $wpdb->query($sql);
	}

	public static function get_expiration_data($user_id, $level_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT e_date FROM '.$wpdb->prefix.'memberdeck_orders WHERE user_id = %d AND level_id = %d ORDER BY id DESC', $user_id, $level_id);
		$res = $wpdb->get_row($sql);
		return (!empty($res->e_date) ? $res->e_date : null);
	}
}
?>