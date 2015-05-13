<?php
class ID_Member_Credit {
	var $credit_id;
	var $credt_name;
	var $credit_price;
	var $credit_count;
	var $credit_level;

	function __construct($credit) {
		$this->credit_name = $credit['credit_name'];
		$this->credit_price = $credit['credit_price'];
		$this->credit_count = $credit['credit_count'];
		$this->credit_level = $credit['credit_level'];
	}

	function add_credit() {
		global $wpdb;
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_credits (credit_name, credit_price, credit_count, credit_level) VALUES (%s, %s, %d, %d)', $this->credit_name, $this->credit_price, $this->credit_count, $this->credit_level);
		$res = $wpdb->query($sql);
		$this->credit_id = $wpdb->insert_id;

		return $this->credit_id;
	}

	public static function get_all_credits() {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_credits';
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function get_credit_by_level($credit_level) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_credits WHERE credit_level = %d', $credit_level);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function update_credit($credit) {
		global $wpdb;
		$credit_id = $credit['credit_id'];
		$credit_name = $credit['credit_name'];
		$credit_price = $credit['credit_price'];
		$credit_count = $credit['credit_count'];
		$credit_level = $credit['credit_level'];
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_credits SET credit_name=%s, credit_price=%s, credit_count=%d, credit_level=%d WHERE id=%d', $credit_name, $credit_price, $credit_count, $credit_level, $credit_id);
		$res = $wpdb->query($sql);
	}

	public static function delete_credit($credit) {
		global $wpdb;
		$credit_id = $credit['credit_id'];
		$sql = 'DELETE FROM '.$wpdb->prefix.'memberdeck_credits WHERE id='.$credit_id;
		$res = $wpdb->query($sql);
	}

	public static function new_order_credit($order_info) {
		//global $wpdb;
		$credit_level = $order_info['level_id'];
		$credit_data = self::get_credit_by_level($credit_level);
		if (!empty($credit_data)) {
			$credit_count = $credit_data->credit_count;
			$user_id = $order_info['user_id'];
			ID_Member::add_credits($user_id, $credit_count);
		}
	}

	public static function use_credits($user_id, $spent) {
		$member = new ID_Member();
		$match = $member->match_user($user_id);
		if (isset($match)) {
			$credits = $match->credits;
			$sum = absint($credits) - absint($spent);
			$spend = $member->set_credits($user_id, $sum);
		}
	}

}
?>