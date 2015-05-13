<?php
class ID_Member_Download {

	var $id;
	var $download_name;
	var $version;
	var $hidden;
	var $enable_s3;
	var $enable_occ;
	var $occ_level;
	var $id_project;
	var $position;
	var $licensed;
	var $levels;
	var $dlink;
	var $ilink;
	var $doclink;
	var $imagelink;
	var $button_text;
	
	function __construct(
	$id = null,
	$download_name = null,
	$version = null,
	$hidden = 0,
	$enable_s3 = 0,
	$enable_occ = 0,
	$occ_level = null,
	$id_project = null,
	$position = null,
	$licensed = 0,
	$levels = null,
	$dlink = null,
	$ilink = null,
	$doclink = null,
	$imagelink = null,
	$button_text = null
	) 
	{
		$this->id = $id;
		$this->download_name = $download_name;
		$this->version = $version;
		$this->hidden = $hidden;
		$this->enable_s3 = $enable_s3;
		$this->enable_occ = $enable_occ;
		$this->occ_level = $occ_level;
		$this->id_project = $id_project;
		$this->position = $position;
		$this->licensed = $licensed;
		$this->levels = $levels;
		$this->dlink = $dlink;
		$this->ilink = $ilink;
		$this->doclink = $doclink;
		$this->imagelink = $imagelink;
		/*if (empty($button_text)) {
			$button_text = __('Download', 'memberdeck');
		}*/
		$this->button_text = $button_text;
	}
	function add_download() {
		global $wpdb;
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'memberdeck_downloads (download_name, version, hidden, enable_s3, enable_occ, occ_level, id_project, position, licensed, download_levels, download_link, info_link, doc_link, image_link, button_text) VALUES (%s, %s, %d, %d, %d, %d, %d, %s, %d, %s, %s, %s, %s, %s, %s)', $this->download_name, $this->version, $this->hidden, $this->enable_s3, $this->enable_occ, $this->occ_level, $this->id_project, $this->position, $this->licensed, serialize($this->levels), $this->dlink, $this->ilink, $this->doclink, $this->imagelink, $this->button_text);
		$res = $wpdb->query($sql);
		$id = $wpdb->insert_id;
		return $id;
	}

	function update_download() {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'memberdeck_downloads SET download_name = %s, version = %s, hidden = %d, enable_s3 = %d, enable_occ = %d, occ_level = %d, id_project = %d, position = %s, licensed = %d, download_levels = %s, download_link = %s, info_link = %s, doc_link = %s, image_link = %s, button_text = %s WHERE ID = %d', $this->download_name, $this->version, $this->hidden, $this->enable_s3, $this->enable_occ, $this->occ_level, $this->id_project, $this->position, $this->licensed, serialize($this->levels), $this->dlink, $this->ilink, $this->doclink, $this->imagelink, $this->button_text, $this->id);
		$res = $wpdb->query($sql);
	}

	function get_download() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_downloads WHERE ID = %d', $this->id);
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function get_downloads() {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_downloads';
		$res = $wpdb->get_results($sql);
		return $res;
	}

	public static function delete_download($id) {
		global $wpdb;
		$sql = 'DELETE FROM '.$wpdb->prefix.'memberdeck_downloads WHERE id='.$id;
		$res = $wpdb->query($sql);
	}
}
?>