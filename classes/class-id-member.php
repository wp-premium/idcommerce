<?php
class ID_Member {

	var $user_id;
	var $membership;
	var $r_date;
	var $reg_key;
	var $data;

	function __construct($user_id = null) {
		$this->user_id = $user_id;
	}

	function get_user_credits() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT credits FROM '.$wpdb->prefix.'memberdeck_members WHERE user_id = %s', $this->user_id);
		$res = $wpdb->get_row($sql);
		if (isset($res)) {
			return $res->credits;
		}
		else {
			return null;
		}
	}

	function match_user($user_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_members WHERE user_id=%d', $user_id);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	function check_user($email) {
		global $wpdb;
		$prefix = md_user_prefix();
		$sql = $wpdb->prepare('SELECT * FROM '.$prefix.'users WHERE user_email = %s', $email);
		return $wpdb->get_row($sql);
	}

	function save_user($user_id, $membership) {
		global $wpdb;
		$this->user_id = $user_id;
		$this->membership = serialize($membership);
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_members SET access_level=%s WHERE user_id=%d', $this->membership, $this->user_id);
		$res = $wpdb->query($sql);

		$old_membership = $this->get_membership();
		$old_array = array();
		foreach ($old_membership as $old_level) {
			$old_array[] = $old_level->level_id;
		}

		$diff = array_diff($old_array, $membership);

		foreach ($membership as $level) {
			$this->set_level($level);
		}

		foreach ($diff as $unset) {
			$this->unset_level($unset);
		}
	}

	function set_level($level_id) {
		global $wpdb;
		$response = 0;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_member_levels WHERE user_id = %d AND level_id = %d', $this->user_id, $level_id);
		$res = $wpdb->get_results($sql);
			if (empty($res)) {
				$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_member_levels (user_id, level_id) VALUES (%d, %d)', $this->user_id, $level_id);
				$res = $wpdb->query($sql);
				if (isset($wpdb->insert_id)) {
					$response = $wpdb->insert_id;
				}
			}
		return $response;
	}

	function unset_level($level_id) {
		global $wpdb;
		$sql = $wpdb->prepare('DELETE FROM '.$wpdb->prefix.'memberdeck_member_levels WHERE user_id = %d AND level_id = %d', $this->user_id, $level_id);
		$res = $wpdb->query($sql);
	}

	function get_membership() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT level_id FROM '.$wpdb->prefix.'memberdeck_member_levels WHERE user_id = %d', $this->user_id);
		$res = $wpdb->get_results($sql);
		return $res;
	}

	function clear_membership() {
		global $wpdb;
		$sql = $wpdb->prepare('DELETE FROM '.$wpdb->preifx.'memberdeck_member_levels WHERE user_id = %d', $this->user_id);
		$res = $wpdb->query($sql);
	}

	function set_credits($user_id, $sum) {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_members SET credits = %d WHERE user_id = %d', $sum, $user_id);
		$res = $wpdb->query($sql);
	}

	public static function get_members() {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_members';
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function add_user($user) {
		global $wpdb;
		$user_id = absint($user['user_id']);
		$membership = $user['level'];
		// need to allow for custom exp dates
		$exp = strtotime('+1 years');

		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_members (user_id, access_level, r_date, data) VALUES (%d, %s, %s, %s)', $user_id, serialize($membership), date('Y-m-d h:i:s'), (!empty($user['data']) ? serialize(array($user['data'])) : ''));
		$res = $wpdb->query($sql);
		$id = $wpdb->insert_id;

		$member = new ID_Member($user_id);
		$old_membership = $member->get_membership();
		$old_array = array();
		foreach ($old_membership as $old_level) {
			$old_array[] = $old_level->level_id;
		}

		$diff = array_diff($old_array, $membership);
		foreach ($diff as $unset) {
			$member->unset_level($unset);
		}

		if (is_array($membership)) {
			foreach ($user['level'] as $level) {
				$member->set_level($level);
			}
		}
		else {
			$member->set_level($membership);
		}
	}

	public static function add_paypal_user($user) {
		global $wpdb;

		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_members (user_id, access_level, r_date, reg_key, data) VALUES (%d, %s, %s, %s, %s)', absint($user['user_id']), serialize($user['level']), date('Y-m-d h:i:s'), $user['reg_key'], serialize(array($user['data'])));
		$res = $wpdb->query($sql);
		$id = $wpdb->insert_id;
		return $sql.$id;
	}

	public static function retrieve_user_key($reg_key) {
		global $wpdb;
		$reg_key = esc_attr($reg_key);
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_members WHERE reg_key = %s', $reg_key);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function update_user($user) {
		global $wpdb;
		$user_id = absint($user['user_id']);
		$membership = $user['level'];
		if (is_array($membership)) {
			$membership = array_unique($membership);
		}
		else {
			$membership = array($membership);
		}
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_members SET access_level = %s, data=%s WHERE user_id = %d', serialize($membership), (!empty($user['data']) ? serialize($user['data']) : ''), $user_id);
		$res = $wpdb->query($sql);
		$member = new ID_Member($user_id);
		$old_membership = $member->get_membership();
		$old_array = array();
		foreach ($old_membership as $old_level) {
			$old_array[] = $old_level->level_id;
		}

		$diff = array_diff($old_array, $membership);
		foreach ($diff as $unset) {
			$member->unset_level($unset);
		}

		foreach ($membership as $level) {
			$member->set_level($level);
		}
	}

	public static function expire_level($user_id, $membership) {
		global $wpdb;
		if (is_array($membership)) {
			$membership = array_unique($membership);
		}
		$levels = serialize($membership);
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_members SET access_level = %s WHERE user_id = %d', $levels, $user_id);
		$res = $wpdb->query($sql);

		$member = new ID_Member($user_id);
		$old_membership = $member->get_membership();
		$old_array = array();
		foreach ($old_membership as $old_level) {
			$old_array[] = $old_level->level_id;
		}

		$diff = array_diff($old_array, $membership);
		foreach ($diff as $unset) {
			$member->unset_level($unset);
		}

		foreach ($membership as $level) {
			$member->set_level($level);
		}
	}

	public static function delete_reg_key($user_id) {
		global $wpdb;
		$sql = 'UPDATE '.$wpdb->prefix.'memberdeck_members SET reg_key = "" WHERE user_id = '.absint($user_id);
		$res = $wpdb->query($sql);
	}

	public static function user_levels($user_id) {
		global $wpdb;
		//$user = get_userdata($user_id);
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_members WHERE user_id=%d', $user_id);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function get_user_levels() {
		// non multisite
		if (is_multisite()) {
			require (ABSPATH . WPINC . '/pluggable.php');
		}
		global $current_user;
		get_currentuserinfo();
		$md_user_levels = null;
		if (!empty($current_user)) {
			$user_id = $current_user->ID;
			$md_user = ID_Member::user_levels($user_id);
			if (!empty($md_user)) {
				$md_user_levels = unserialize($md_user->access_level);
			}
		}
		return $md_user_levels;
	}

	public static function get_allowed_users() {
		global $wpdb;
		$prefix = md_user_prefix();
		// first lets join the wp users and memberdeck users tables and put them in an array
		$getusers = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_members LEFT JOIN '.$prefix.'users ON ('.$wpdb->prefix.'memberdeck_members.user_id='.$prefix.'users.ID)';
		//$getusers = 'SELECT * FROM '.$wpdb->prefix.'users';
		$users = $wpdb->get_results($getusers);
	    if (is_multisite()) {
	     	$allowed_users = array();
	      	$blog_id = get_current_blog_id();
	      	foreach ($users as $user) {
	        	$user_id = $user->ID;
	        	$blog_access = get_blogs_of_user($user_id);
	        	$add_user = false;
	        	foreach ($blog_access as $access) {
		          	if ($blog_id == $access->userblog_id) {
		           		$add_user = true;
		          	}
	        	}
		        if ($add_user) {
		        	$allowed_users[] = $user;
		        }
	      	}
	      	$users = $allowed_users;
	    }
	    return $users;
	}

	public static function get_allowed_users_new($product_id = null) {
		global $wpdb;
		$prefix = md_user_prefix();
		if (!empty($product_id)) {
			$misc = ' WHERE level_id = '.$product_id;
		}
		else {
			$misc = null;
		}
		$orders = ID_Member_Order::get_orders(null, null, $misc);
		if (!empty($orders)) {
			$users = array();
			$used_users = array();
			foreach ($orders as $order) {
				$user_id = $order->user_id;
				if (!in_array($user_id, $used_users)) {
					$used_users[$user_id] = $order;
				}
				else {
					$user = $used_users[$user_id];
				}
				$users[] = $order;
			}
		}
		if (is_multisite() && !empty($users)) {
			$allowed_users = array();
			$blog_id = get_current_blog_id();
			foreach ($users as $user) {
				$user_id = $user->ID;
				$blog_access = get_blogs_of_user($user_id);
				$add_user = false;
				foreach ($blog_access as $access) {
					if ($blog_id == $access->userblog_id) {
						$add_user = true;
					}
				}
				if ($add_user) {
					$allowed_users[] = $user;
				}
			}
			$users = $allowed_users;
		}
		return (isset($users) ? $users : null);
	}

	public static function get_like_users($like) {
		global $wpdb;
		$prefix = md_user_prefix();
		// first lets join the wp users and memberdeck users tables and put them in an array
		$getusers = 'SELECT * FROM '.$prefix.'users LEFT JOIN '.$prefix.'memberdeck_members ON ('.$prefix.'users.ID='.$wpdb->prefix.'memberdeck_members.user_id) WHERE user_login LIKE "%'.$like.'%" OR user_nicename LIKE "%'.$like.'%" OR user_email LIKE "%'.$like.'%" OR user_url LIKE "%'.$like.'%" OR display_name LIKE "%'.$like.'%"';
		//$getusers = 'SELECT * FROM '.$wpdb->prefix.'users';
		$users = $wpdb->get_results($getusers);
		return $users;
	}

	public static function get_level_users($level_id) {
		global $wpdb;
		$users = ID_Member::get_allowed_users();
		$return = array();
		foreach ($users as $user) {
			$user_levels = unserialize($user->access_level);
			if (is_array($user_levels)) {
				if (in_array($level_id, $user_levels)) {
					$return[] = $user;
				}
			}
		}
		return $return;
	}

	public static function get_subscription_data($sub_id) {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_members WHERE data LIKE "%'.$sub_id.'%"';
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function get_customer_data($cust_id) {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_members WHERE data LIKE "%'.$cust_id.'%"';
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function get_customer_id($user_id) {
		$customer_id = get_user_meta($user_id, 'stripe_customer_id', true);
		if (empty($customer_id)) {
			global $wpdb;
			$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_members WHERE user_id = %d', $user_id);
			$res = $wpdb->get_row($sql);
			if (isset($res->data)) {
				$data = unserialize($res->data);
				foreach ($data as $item) {
					foreach ($item as $k=>$v) {
						if ($k == 'customer_id') {
							$customer_id = $v;
							break 2;
						}
					}	
				}
			}
		}
		return $customer_id;
	}

	public static function add_credits($user_id, $credit_count) {
		global $wpdb;
		$member = new ID_Member();
		$match = $member->match_user($user_id);
		if (!empty($match)) {
			$user_credits = $match->credits;
			$new_count = absint($user_credits) + absint($credit_count);
			$id = $match->id;
			$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_members SET credits = %d WHERE id = %d', $new_count, $id);
			$res = $wpdb->query($sql);
		}
	}

	public static function export_members($product_id, $is_project = false) {
		global $wpdb;
		global $crowdfunding;
		$levels = ID_Member_Level::get_levels();
		$level_array = array();
		$product_ids = array();
		foreach ($levels as $level) {
			$level_array[$level->id] = $level;
		}
		// if the argument is project_id instead of product_id, then get all the product_id's
		if ($is_project) {
			$mdid_assignments = get_assignments_by_project($product_id);
			foreach ($mdid_assignments as $assignment) {
				$product_id = $assignment->level_id;
				array_push($product_ids, $product_id);
				// $members_product = self::get_allowed_users_new($product_id);
				// $members = array_merge($members, $members_product);
			}
		}
		else {
			array_push($product_ids, $product_id);
		}
		
		$user_records = array();
		$url = '';
		$filenames = array();
		for ($i=0 ; $i < count($product_ids) ; $i++) {
			$members = self::get_allowed_users_new($product_ids[$i]);
			if (!empty($members)) {
				foreach ($members as $member) {
					// get users and prep data
					$user = array();
					$user_id = $member->user_id;
	
					// now WP data
					$user_data = get_userdata($user_id);
					if (!empty($user_data)) {
						$username = $user_data->user_login;
						$email = $user_data->user_email;
					}
					else {
						$username = '';
						$email = '';
					}
	
					$user_meta = get_user_meta($user_id);
					if (!empty($user_meta)) {
						$fname = $user_meta['first_name'][0];
						$lname = $user_meta['last_name'][0];
					}
					else {
						$fname = '';
						$lname = '';
					}
					foreach ($member as $k=>$v) {
						if (is_string($v)) {
							if ($k == 'level_id') {
								$user['product_name'] = $level_array[$v]->level_name;
							}
							else {
								$user[$k] = $v;
							}
						}
					}
				
					$user['user_id'] = $user_id;
					$user['username'] = $username;
					$user['email'] = $email;
					$user['first_name'] = $fname;
					$user['last_name'] = $lname;
	
					$user['address'] = '';
					$user['address_two'] = '';
					$user['city'] = '';
					$user['state'] = '';
					$user['zip'] = '';
					$user['country'] = '';
					//$user['data'] = $data;
					$crm_settings = get_option('crm_settings');
					if (!empty($crm_settings)) {
						$shipping_info = $crm_settings['shipping_info'];
						if (isset($shipping_info) && $shipping_info == '1') {
							$shipping_info = get_user_meta($user_id, 'md_shipping_info', true);
							if (is_array($shipping_info)) {
								$user['address'] = $shipping_info['address'];
								$user['address_two'] = $shipping_info['address_two'];
								$user['city'] = $shipping_info['city'];
								$user['state'] = $shipping_info['state'];
								$user['zip'] = $shipping_info['zip'];
								$user['country'] = $shipping_info['country'];
							}
						}
					}
					/*if ($crowdfunding) {
						$id_order = mdid_transaction_check($member->transaction_id);
						if (!empty($id_order)) {
							foreach ($id_order as $k=>$v) {
								if (empty($user[$k])) {
									$user[$k] = $v;
								}
							}
						}
					}*/
					$user_records[] = $user;
				}
			}
			if (!empty($user_records)) {
				// now we should have data to export
				$filename = __('IDC Customer Export', 'memberdeck').'-'.date('Y-m-d h-i-s').' (pl'.$product_ids[$i].')';
				$uploads = wp_upload_dir();
				$filepath = trailingslashit($uploads['basedir']).$filename;
				$baseurl = trailingslashit($uploads['baseurl']).$filename;

				$file = fopen($filepath.'.csv', 'w');
				$keys = array_keys($user_records[0]);
				fputcsv($file, $keys);
				foreach ($user_records as $record) {
					fputcsv($file, $record);
				}
				fclose($file);
				//$url = $baseurl.'.csv';				
				array_push($filenames, $filename);
				$user_records = array();	//Emptying the array for next CSV file (if any)
				//return $url;
			}
		}
		// If there are any files to be downloaded
		$filenames_count = count($filenames);
		if ($filenames_count > 0) {
			// If levels/product_ids is greater than 1, then make a zip of the files created and send to download
			if ($filenames_count > 1) {
				$zipfilename = __('IDC Customer Export', 'memberdeck').date('Y-m-d h:i:s');
				$zipfilepath = trailingslashit($uploads['basedir']).$zipfilename;
				$zip = new ZipArchive();
				$zip->open($zipfilepath.'.zip', ZipArchive::CREATE);
				foreach ($filenames as $filename) {
					$filepath = trailingslashit($uploads['basedir']).$filename;
					$zip->addFile($filepath.'.csv', $filename.'.csv');
				}			
				$zip->close();
				header('Content-Type: application/zip');
				header('Content-Disposition: attachment; filename='.$zipfilename.'.zip');
				header('Content-Length: '.filesize($zipfilepath.'.zip'));
				header('Pragma: no-cache');
				readfile(trailingslashit($uploads['baseurl']).rawurlencode($zipfilename).'.zip');
				//readfile(trailingslashit($uploads['baseurl']).rawurlencode($filename).'.txt');
				foreach ($filenames as $filename) {
					$filepath = trailingslashit($uploads['basedir']).$filename;
					ID_Member::delete_export($filepath);
				}
				exit;
			}
			else {
				//File is only 1, so don't zip, just simply send to download
				header('Content-Encoding: UTF-8');
				header('Content-Type: application/csv; charset=utf-8');
				header('Content-Disposition: attachment; filename='.$filenames[0].'.csv');
				header('Pragma: no-cache');
				readfile(trailingslashit($uploads['baseurl']).rawurlencode($filenames[0]).'.csv');
				ID_Member::delete_export($filepath);
				exit;
			}
		}
	}

	public static function delete_export($filepath) {
		unlink($filepath.".csv");
	}

	public static function customer_id() {
		$customer_id = null;
		if (is_user_logged_in()) {
			if (is_multisite()) {
				require (ABSPATH . WPINC . '/pluggable.php');
			}
			global $current_user;
			get_currentuserinfo();
			$user_id = $current_user->ID;
			$customer_id = get_user_meta($user_id, 'stripe_customer_id', true);
			if (empty($customer_id)) {
				if (isset($user_id)) {
					$member = new ID_Member();
					$match = $member->match_user($user_id);
					if (isset($match->data)) {
						$data = unserialize($match->data);
						if (is_array($data)) {
							foreach ($data as $item) {
								if (is_array($item)) {
									foreach ($item as $k=>$v) {
										if ($k == 'customer_id') {
											$customer_id = $v;
											break 2;
										}
									}
								}	
							}
						}
					}
				}
			}
		}
		return $customer_id;
	}

	public static function customer_id_ajax($user_id) {
		$customer_id = get_user_meta($user_id, 'stripe_customer_id', true);
		if (empty($customer_id)) {
			$member = new ID_Member();
			$match = $member->match_user($user_id);
			if (isset($match->data)) {
				$data = unserialize($match->data);
				if (is_array($data)) {
					foreach ($data as $item) {
						if (is_array($item)) {
							foreach ($item as $k=>$v) {
								if ($k == 'customer_id') {
									$customer_id = $v;
									break 2;
								}
							}
						}	
					}
				}
			}
		}
		return (isset($customer_id) ? $customer_id : '');
	}

	public static function balanced_customer_id() {
		$balanced_customer_id = null;
		if (is_user_logged_in()) {
			if (is_multisite()) {
				require (ABSPATH . WPINC . '/pluggable.php');			
			}
			global $current_user;
			get_currentuserinfo();
			$user_id = $current_user->ID;
			if (isset($user_id)) {
				$balanced_customer_id = get_user_meta($user_id, 'balanced_customer_id', true);
			}
		}
		return $balanced_customer_id;
	}

	public static function balanced_customer_id_ajax($user_id) {
		$balanced_customer_id = get_user_meta($user_id, 'balanced_customer_id', true);
		return $balanced_customer_id;
	}

	public static function fd_customer_id() {
		$fd_card_details = null;
		if (is_user_logged_in()) {
			if (is_multisite()) {
				require (ABSPATH . WPINC . '/pluggable.php');			
			}
			global $current_user;
			get_currentuserinfo();
			$user_id = $current_user->ID;
			if (isset($user_id)) {
				$fd_card_details = get_user_meta($user_id, 'fd_card_details', true);
			}
		}
		return $fd_card_details;
	}

	public static function fd_customer_id_ajax($user_id) {
		$fd_customer_id = get_user_meta($user_id, 'fd_card_details', true);
		return $fd_customer_id;
	}

	public static function authnet_customer_id() {
		$authnet_customer_id = null;
		if (is_user_logged_in()) {
			if (is_multisite()) {
				require (ABSPATH . WPINC . '/pluggable.php');			
			}
			global $current_user;
			get_currentuserinfo();
			$user_id = $current_user->ID;
			if (isset($user_id)) {
				$authnet_customer_id = get_user_meta($user_id, 'authorizenet_profile_id', true);
			}
		}
		return $authnet_customer_id;
	}

	public static function authnet_customer_id_ajax($user_id) {
		$authnet_customer_id = get_user_meta($user_id, 'authorizenet_profile_id', true);
		return $authnet_customer_id;
	}

	public static function md_credits() {
		$md_credits = 0;
		if (is_user_logged_in()) {
			if (is_multisite()) {
				require (ABSPATH . WPINC . '/pluggable.php');			
			}
			global $current_user;
			get_currentuserinfo();
			$user_id = $current_user->ID;
			if (isset($user_id)) {
				$member = new ID_Member($user_id);
				$md_credits = $member->get_user_credits();
			}
		}
		return $md_credits;
	}
}
?>