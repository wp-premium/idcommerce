<?php
class MD_Keys {

	protected $license;
	var $avail;
	var $in_use;

	function __construct(
		$license = null,
		$avail = -1,
		$in_use = 0
		) 
	{
		$this->license = $license;
		$this->avail = $avail;
		$this->in_use = $in_use;
	}

	function is_licensed($level_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_levels WHERE id = %d', $level_id);
		$res = $wpdb->get_row($sql);
		if (isset($res->license_count) && ($res->license_count > 0 || $res->license_count == -1)) {
			return $res->license_count;
		}
		else {
			return null;
		}
	}

	function generate_license($user_id) {
		if (!empty($user_id)) {
			$user = get_userdata($user_id);
			$email = $user->user_email;
			$this->license = sha1(mt_rand(10000,99999).time().$email);
			return $this->license;
		}
	}

	function store_license($user_id, $dl_id) {
		global $wpdb;
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_keys (license, avail, in_use) VALUES (%s, %d, %d)', $this->license, $this->avail, $this->in_use);
		$res = $wpdb->query($sql);
		$insert_id = $wpdb->insert_id;
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_key_assoc (user_id, download_id, assoc) VALUES (%d, %d, %s)', $user_id, $dl_id, $insert_id);
		$res_assoc = $wpdb->query($sql);
		if (!empty($res_assoc->insert_id)) {
			return $res_assoc->insert_id;
		}
		else {
			return null;
		}
	}

	function activate_license() {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_keys SET in_use = %d WHERE license = %s', $this->in_use + 1, $this->license);
		$res = $wpdb->query($sql);
	}

	function downgrade_license() {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_keys SET in_use = %d WHERE license = %s', $this->in_use - 1, $this->license);
		$res = $wpdb->query($sql);
	}

	function check_license() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_keys WHERE license = %s', $this->license);
		$res = $wpdb->get_row($sql);
		$check = 0;
		if (isset($res)) {
			$this->avail = $res->avail;
			$this->in_use = $res->in_use;
			if ($this->avail > $this->in_use) {
				$check = 1;
			}
		}
		return $check;
	}

	function validate_license() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_keys WHERE license = %s', $this->license);
		$res = $wpdb->get_row($sql);
		$valid = false;
		$download_id = null;
		if (!empty($res)) {
			// now see if expired
			$assoc_sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_key_assoc WHERE assoc = %d', $res->id);
			$assoc_res = $wpdb->get_row($assoc_sql);
			if (!empty($assoc_res)) {
				$user_id = $assoc_res->user_id;
				$download_id = $assoc_res->download_id;
				$download = new ID_Member_Download($download_id);
				$the_download = $download->get_download();
				if (!empty($the_download)) {
					$download_levels = $the_download->download_levels;
					if (!empty($download_levels)) {
						$download_array = unserialize($download_levels);
						if (is_array($download_array)) {
							$access_levels = ID_Member::user_levels($user_id);
							$md_user_levels = array();
							if (!empty($access_levels)) {
								$user_levels = $access_levels->access_level;
								if (isset($user_levels)) {
									$md_user_levels = unserialize($user_levels);
								}	
							}
							foreach ($download_array as $array_id) {
								if (in_array($array_id, $md_user_levels)) {
									$valid = true;
									break;
								}
							}
						}
					}
				}
			}
		}
		$response = array(
			'valid' => $valid,
			'download_id' => $download_id);
		return $response;
	}

	public static function set_licenses($user_id, $level_id) {
		$key = new MD_Keys();
		$license_count = $key->is_licensed($level_id);
		if ($license_count > 0) {
			$downloads = ID_Member_Download::get_downloads();
			foreach ($downloads as $download) {
				$dl_id = $download->id;
				if (!empty($download->download_levels)) {
					$levels = unserialize($download->download_levels);
					if (is_array($levels) && in_array($level_id, $levels)) {
						if ($download->licensed == 1) {
							$license = $key->generate_license($user_id);
							if (isset($license)) {
								$new_license = new MD_Keys($license, $license_count);
								$save_license = $new_license->store_license($user_id, $dl_id);
							}
						}
					}
				}
			}
		}
	}

	public static function get_license($user_id, $dl_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_key_assoc WHERE user_id = %d AND download_id = %d', $user_id, $dl_id);
		$res = $wpdb->get_row($sql);
		if (!empty($res)) {
			$assoc = $res->assoc;
			$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_keys WHERE id = %d', $assoc);
			$res = $wpdb->get_row($sql);
			return $res->license;
		}
	}
}
?>