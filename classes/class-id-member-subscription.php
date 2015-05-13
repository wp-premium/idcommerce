<?php

class ID_Member_Subscription {
	var $id;
	var $user_id;
	var $level_id;
	var $subscription_id;
	var $source;
	var $total_charges;
	var $charges_left;
	var $status;

	function __construct($id = null,
		$user_id = null,
		$level_id = null,
		$subscription_id = '',
		$source = '',
		$payments = 0,
		$status = '')
	{
		$this->id = $id;
		$this->user_id = $user_id;
		$this->level_id = $level_id;
		$this->subscription_id = $subscription_id;
		$this->source = $source;
		$this->payments = $payments;
		$this->status = $status;
	}

	function get_subscription() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_subscriptions WHERE id = %s', $this->id);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	function add_subscription() {
		global $wpdb;
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_subscriptions (user_id, level_id, subscription_id, source, payments, status) VALUES (%d, %d, %s, %s, %d, %s)', $this->user_id, $this->level_id, $this->subscription_id, $this->source, $this->payments, 'active');
		$res = $wpdb->query($sql);
		if (!empty($res)) {
			return $wpdb->insert_id;
		}
		return 0;
	}

	function find_subscription() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_subscriptions WHERE user_id = %d AND level_id = %d AND status = %s ORDER BY id DESC', $this->user_id, $this->level_id, 'active');
		$res = $wpdb->get_row($sql);
		return $res;
	}

	function add_charge($new_count) {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_subscriptions SET payments = %d WHERE id = %d', $new_count, $this->id);
		$res = $wpdb->query($sql);
	}

	function cancel_subscription() {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_subscriptions SET status = %s WHERE id = %d', 'inactive', $this->id);
		$res = $wpdb->query($sql);
	}

	public static function has_subscription($user_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_subscriptions WHERE user_id = %d and status = %s', $user_id, 'active');
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function cancel_subscription_id($subscription_id) {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_subscriptions SET status = %s WHERE subscription_id = %d', 'inactive', $subscription_id);
		$res = $wpdb->query($sql);
	}
}

?>