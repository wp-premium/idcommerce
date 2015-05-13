<?php
class ID_Member_Level {
	var $level_id;
	var $product_type;
	var $level_name;
	var $level_price;
	var $credit_value;
	var $txn_type;
	var $level_type;
	var $recurring_type;
	var $limit_term;
	var $term_length;
	var $plan;
	var $license_count;
	var $enable_renewals;
	var $renewal_price;
	var $enable_multiples;
	var $create_page;

	function __construct() {
		
	}

	function add_level($level) {
		global $wpdb;
		$this->product_type = $level['product_type'];
		$this->level_name = $level['level_name'];
		$this->level_price = $level['level_price'];
		$this->credit_value = $level['credit_value'];
		$this->txn_type = $level['txn_type'];
		$this->level_type = $level['level_type'];
		
		if ($this->level_type !== 'recurring') {
			$this->recurring_type = 'none';
			$this->plan = '';
		}
		else {
			$this->recurring_type = $level['recurring_type'];
			$this->plan = $level['plan'];
		}
		$this->limit_term = $level['limit_term'];
		$this->term_length = $level['term_length'];
		$this->license_count = $level['license_count'];
		$this->enable_renewals = $level['enable_renewals'];
		$this->renewal_price = $level['renewal_price'];
		$this->enable_multiples = $level['enable_multiples'];
		$this->create_page = (isset($level['create_page']) ? $level['create_page'] : false);
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_levels (product_type, level_name, level_price, credit_value, txn_type, level_type, recurring_type, limit_term, term_length, plan, license_count, enable_renewals, renewal_price, enable_multiples) VALUES (%s, %s, %s, %d, %s, %s, %s, %d, %d, %s, %d, %d, %s, %d)', $this->product_type, $this->level_name, $this->level_price, $this->credit_value, $this->txn_type, $this->level_type, $this->recurring_type, $this->limit_term, $this->term_length, $this->plan, $this->license_count, $this->enable_renewals, $this->renewal_price, $this->enable_multiples);
		$res = $wpdb->query($sql);
		$this->level_id = $wpdb->insert_id;
		if ($this->create_page) {
			$post_id = memberdeck_auto_page($this->level_id, $this->level_name);
		}
		return array('level_id' => $this->level_id, 'post_id' => (isset($post_id) ? $post_id : null));
	}

	public static function update_level($level) {
		global $wpdb;
		$product_type = $level['product_type'];
		$level_name = $level['level_name'];
		$level_price = $level['level_price'];
		$credit_value = $level['credit_value'];
		$txn_type = $level['txn_type'];
		$level_id = $level['level_id'];
		$level_type = $level['level_type'];
		$recurring_type = $level['recurring_type'];
		$limit_term = $level['limit_term'];
		$term_length = $level['term_length'];
		$plan = $level['plan'];
		$license_count = $level['license_count'];
		$enable_renewals = $level['enable_renewals'];
		$renewal_price = $level['renewal_price'];
		$enable_multiples = $level['enable_multiples'];
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_levels SET product_type = %s, level_name=%s, level_price=%s, credit_value = %d, txn_type=%s, level_type=%s, recurring_type=%s, limit_term = %d, term_length = %d, plan=%s, license_count=%d, enable_renewals = %d, renewal_price = %s, enable_multiples = %d WHERE id=%d', $product_type, $level_name, $level_price, $credit_value, $txn_type, $level_type, $recurring_type, $limit_term, $term_length, $plan, $license_count, $enable_renewals, $renewal_price, $enable_multiples, $level_id);
		$res = $wpdb->query($sql);
	}

	public static function delete_level($level) {
		global $wpdb;
		$level_id = $level['level_id'];
		$sql = 'DELETE FROM '.$wpdb->prefix.'memberdeck_levels WHERE id='.$level_id;
		$res = $wpdb->query($sql);
	}

	public static function get_levels() {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_levels';
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function get_level($id) {
		global $wpdb;
		$level_id = absint(esc_attr($id));
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_levels WHERE id='.$level_id;
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function get_level_by_plan($plan) {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_levels WHERE plan = "'.$plan.'"';
		$res = $wpdb->get_row($sql);
		return $res->id;
	}

	public static function get_level_member_count($id) {
		global $wpdb;
		$sql = 'SELECT COUNT(*) as count FROM '.$wpdb->prefix.'memberdeck_members WHERE access_level LIKE "%i:'.$id.'%" OR access_level LIKE "%s:1:\"'.$id.'\"%"';
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function get_level_member_updated_count($id) {
		global $wpdb;
		$count = 0;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_member_levels WHERE level_id = %d', $id);
		$res = $wpdb->get_results($sql);
		if (!empty($res)) {
			$count = count($res);
		}
		return $count;
	}

	public static function is_level_renewable($id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_levels WHERE id = %d', $id);
		$res = $wpdb->get_row($sql);
		$renewable = 0;
		if (!empty($res)) {
			if ($res->level_type == 'standard') {
				$renewable = $res->enable_renewals;
			}
		}
		return $renewable;
	}
}
?>