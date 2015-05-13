<?php
class ID_Member_Metaboxes {

	function __construct() {
		add_action('add_meta_boxes', array(&$this, 'generate_metabox'));
		add_action( 'save_post', array(&$this, 'save_metabox'));
	}

	function generate_metabox($post) {
		$post_types = get_post_types();
		//$function = ID_Member_Level::generate_metabox();
		$types = array('post', 'page', 'link', 'attachment');
		foreach($post_types as $type) {
			add_meta_box('protect_content', __("Protect Content", "memberdeck"), array(&$this, 'render_protect_metabox'), $type);
			add_meta_box('list_shortcode', __("MemberDeck Shortcodes", "memberdeck"), array(&$this, 'render_shortcode_metabox'), $type, 'side');
		}
	}

	function render_protect_metabox($post) {
		// Use nonce for verification
  		wp_nonce_field('memberdeck', 'memberdeck_metabox');
  		$class = new ID_Member_Level();
  		$levels = $class->get_levels();
  		$options = get_post_meta($post->ID, 'memberdeck_protected_posts', true);
  		if (!empty($options)) {
  			$array = unserialize($options);
  			$yes = 'checked="checked"';
  		}
  		else {
  			$no = 'checked="checked"';
  		}
  		include_once IDC_PATH.'/templates/admin/_metaboxContent.php';
	}

	function render_shortcode_metabox($post) {
		include_once IDC_PATH.'/templates/admin/_shortcodeList.php';
	}

	function save_metabox($post_id) {
		// First we need to check if the current user is authorized to do this action. 
		if (isset($_POST)) {
			if ( isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
		    	if ( !current_user_can( 'edit_pages' ) ) {
		    		return;
		    	}
		  	} else {
		   		 if ( !current_user_can( 'edit_posts' ) ) {
		        	return;
		    	}
		  	}
		  	// Secondly we need to check if the user intended to change this value.
		  	if ( ! isset( $_POST['memberdeck_metabox'] ) || ! wp_verify_nonce( $_POST['memberdeck_metabox'], 'memberdeck' ) ) {
		      	return;
		  	}
		  	$post_id = $_POST['post_ID'];
		  	if (isset($_POST['protect-choice'])) {
		  		$choice = $_POST['protect-choice'];
		  	}
		  	else {
		  		$choice = 'no';
		  	}
		  	if ($choice == 'yes') {
		  		$protected = array();
		  		if (isset($_POST['protect-level'])) {
		  			foreach ($_POST['protect-level'] as $protect_level) {
			  			$protected[] = $protect_level;
			  		}
		  		}
		  		$serialize = serialize($protected);
		  		update_post_meta($post_id, 'memberdeck_protected_posts', $serialize);
		  	}
		  	else {
		  		delete_post_meta($post_id, 'memberdeck_protected_posts');
		  		return;
		  	}
		}
	}
}
?>