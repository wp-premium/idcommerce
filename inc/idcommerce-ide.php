<?php

add_action('md_profile_extrafields', 'idc_creator_account', 11);

function idc_creator_account() {
	// this function displays the enable creator checkbox when opt-in is required
	if (md_ide_creator_permissions()) {
		if (is_multisite()) {
			require (ABSPATH . WPINC . '/pluggable.php');
		}
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;

		if (isset($_POST['enable_creator'])) {
			$enable_creator = absint($_POST['enable_creator']);
			update_user_meta($user_id, 'enable_creator', $enable_creator);
		}
		
		if (md_ide_opt_in_required()){
			$user_projects = get_user_meta($user_id, 'ide_user_projects', true);
			if (empty($user_projects)) {
				$enable_creator = get_user_meta($user_id, 'enable_creator', true);
				if (empty($enable_creator) || !$enable_creator) {
					include_once(IDC_PATH.'templates/_enableCreator.php');		
				}
			}
		}
	} 
}

add_action ('md_profile_extratabs', 'md_creator_projects', 2);

function md_creator_projects() {
	
	if (current_user_can('create_edit_projects')) {
		$show_psettings = false;
		$settings = get_option('memberdeck_gateways');
		if (!empty($settings)) {
			if (is_array($settings)) {
				$epp_fes = $settings['epp_fes'];
				$esc = $settings['esc'];
				$ebm = $settings['ebm'];
				if ($epp_fes || $esc || $ebm) {
					$show_psettings = true;
				}
			}
		}
		if (is_multisite()) {
			require (ABSPATH . WPINC . '/pluggable.php');
		}
		global $current_user;
		global $permalink_structure;
		if (empty($permalink_structure)) {
			$prefix = '&';
		}
		else {
			$prefix = '?';
		}
		get_currentuserinfo();
		$user_id = $current_user->ID;
		$enable_creator = get_user_meta($user_id, 'enable_creator', true);
		$creator_args = array(
			'post_type' => 'ignition_product',
			'post_author' => $user_id,
			'posts_per_page' => -1,
			'post_status' => array('draft', 'pending', 'publish')
		);
		$user_projects = apply_filters('id_creator_projects', get_posts(apply_filters('id_creator_args', $creator_args)));
		if (!empty($user_projects)) {
			
			$tab = __('My Projects', 'memberdeck');
			$url = md_get_durl().$prefix.'creator_projects=1';
		}
		else {
			$tab = __('Create Project', 'memberdeck');
			$url = md_get_durl().$prefix.'create_project=1';
		}
		// this needs to be tidied up based on latest roles
		if (md_ide_opt_in_required()){
			if ($enable_creator || !empty($user_projects)) {
				if ($show_psettings) {
					echo '<li '.(isset($_GET['payment_settings']) ? 'class="active"' : '').'><a href="'.md_get_durl().$prefix.'payment_settings=1">'.__('Payment Account', 'memberdeck').'</a></li>';
				}
				echo '<li '.(isset($_GET['creator_projects']) || isset($_GET['create_project']) || isset($_GET['project_files']) ? 'class="active"' : '').'><a href="'.$url.'">'.$tab.'</a></li>';
			}
		} else {
			if ($show_psettings) {
				echo '<li '.(isset($_GET['payment_settings']) ? 'class="active"' : '').'><a href="'.md_get_durl().$prefix.'payment_settings=1">'.__('Payment Account', 'memberdeck').'</a></li>';
			}
			echo '<li '.(isset($_GET['creator_projects']) || isset($_GET['create_project']) || isset($_GET['project_files']) ? 'class="active"' : '').'><a href="'.$url.'">'.$tab.'</a></li>';
		}
	}
}


// Treat the old enable_creator value as a new separate opt-in check
function md_ide_opt_in_required(){
	$general = get_option('md_receipt_settings');
	$admin_enable = 0;
	if (!empty($general)) {
		if (!is_array($general)) {
			$general = unserialize($general);
		}
		$admin_enable = $general['enable_creator'];
	}
	return $admin_enable;
}

// A new function that returns true if the current user is allowed to create projects
function md_ide_creator_permissions(){
	$enable = false;
	if ( is_user_logged_in() ) {
		$general = get_option('md_receipt_settings');
		if (!empty($general)) {
			if (!is_array($general)) {
				$general = unserialize($general);
			}
			$admin_enable = $general['creator_permissions'];
			if (current_user_can('manage_options') || $admin_enable == 3) {
				$enable = true;
			} 
			elseif ($admin_enable == 2) {
				//Only owners of the right levels can create projects, so we check to see if there's a level match
				$levelsowned = ID_Member::get_user_levels();
				$levelspermitted = idmember_get_cperms(1);
				if (!empty($levelsowned)) {
					foreach ($levelsowned as $cur){
						if (in_array($cur, $levelspermitted)){
							$enable = true;
							break;
						}
					}
				}
			}
		}
	}
	return $enable;
}


add_action('init', 'md_ide_check_creator_profile');

function md_ide_check_creator_profile() {
	if (isset($_GET['creator_projects']) && $_GET['creator_projects'] == 1 && current_user_can('create_edit_projects')) {
		add_filter('the_content', 'md_ide_creator_projects');
	}
}

function md_ide_creator_projects($content) {
	ob_start();
	global $current_user;
	global $permalink_structure;
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	else {
		$prefix = '?';
	}
	get_currentuserinfo();
	$user_id = $current_user->ID;
	echo '<div class="memberdeck">';
	include_once IDC_PATH.'templates/_mdProfileTabs.php';
	echo '<ul class="md-box-wrapper full-width cf"><li class="md-box full"><div class="md-profile author-'.$user_id.'" data-author="'.$user_id.'">';
	//echo '<h3>'.__('My Projects', 'memberdeck').': </h3>';
	echo '<ul>';
	$creator_args = array(
		'post_type' => 'ignition_product',
		'author' => $user_id,
		'posts_per_page' => -1,
		'post_status' => array('draft', 'pending', 'publish')
	);
	$user_projects = apply_filters('id_creator_projects', get_posts(apply_filters('id_creator_args', $creator_args)));
	if (!empty($user_projects)) {
		foreach ($user_projects as $post) {
			$post_id = $post->ID;
			$project_id = get_post_meta($post_id, 'ign_project_id', true);
			if (!empty($project_id)) {
				$status = $post->post_status;
				if (strtoupper($status) !== 'TRASH') {
					$project = new ID_Project($project_id);
					$the_project = $project->the_project();
					$thumb = ID_Project::get_project_thumbnail($post_id);
					$permalink = get_permalink($post_id);
					if (strtoupper($status) == 'DRAFT') {
						$permalink = $permalink.'&preview=true';
					}
					include IDC_PATH.'templates/_myProjects.php';
				}
			}
		}
	}
	echo '</ul><button class="create_project button-medium" onclick="location.href=\''.md_get_durl().$prefix.'create_project=1\'">'.__('Create Project', 'memberdeck').'</button></div></li></ul>';
	echo '</div>';
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

add_filter('ide_fund_options', 'md_ide_subscription_options');

function md_ide_subscription_options($array) {
	$settings = get_option('memberdeck_gateways');
	if (!empty($settings)) {
		if (is_array($settings)) {
			if ($settings['epp'] || $settings['esc']) {
				$fund_type = get_option('idc_cf_fund_type');
				if (!empty($fund_type) && ($fund_type == 'all' || $fund_type == 'c_sub')) {
					if (is_multisite()) {
						require (ABSPATH . WPINC . '/pluggable.php');
					}
					// // If all options to be added, add the options if not already added
					// if ($fund_type == 'all') {
					// 	$preauth_present = false; $capture_present = false;
					// 	for ($i=0 ; $i < count($array) ; $i++) {
					// 		if ($array[$i]['value'] == 'preauth') {
					// 			$preauth_present = true;
					// 		}
					// 		if ($array[$i]['value'] == 'capture') {
					// 			$capture_present = true;
					// 		}
					// 	}
					// 	if (!$capture_present) {
					// 		$array[] = array('value' => 'capture', 'title' => 'Immediately Deliver Funds');
					// 	}
					// 	if (!$preauth_present) {
					// 		$array[] = array('value' => 'preauth', 'title' => '100% Threshold');
					// 	}
					// }
					global $current_user;
					get_currentuserinfo();
					$user_id = $current_user->ID;
					if ($settings['esc']) {
						$md_sc_params = get_sc_params($user_id);
					}
					if (empty($md_sc_params) && $settings['esc']) {
						$array[] = array('value' => 'recurring-weekly', 'title' => 'Subscription - Weekly (Requires Completed Payment Settings)', 'misc' => 'disabled');
						$array[] = array('value' => 'recurring-monthly', 'title' => 'Subscription - Monthly (Requires Completed Payment Settings)', 'misc' => 'disabled');
						$array[] = array('value' => 'recurring-annually', 'title' => 'Subscription - Annual (Requires Completed Payment Settings)', 'misc' => 'disabled');
					}
					else {
						$array[] = array('value' => 'recurring-weekly', 'title' => 'Subscription - Weekly');
						$array[] = array('value' => 'recurring-monthly', 'title' => 'Subscription - Monthly');
						$array[] = array('value' => 'recurring-annually', 'title' => 'Subscription - Annual');
					}
					if ($fund_type == 'c_sub') {
						// Removing 100% Threshold option if "immediate + subscription" is selected
						for ($i=0 ; $i < count($array) ; $i++) {
							if (isset($array[$i]['value']) && $array[$i]['value'] == 'preauth') {
								$remove_index = $i;
							}
						}
						if (isset($remove_index)) {
							unset($array[$remove_index]);
						}
					}
				} else if (!empty($fund_type) && $fund_type == 'preauth') {
					// Removing 'Capture' if preauth (100% Threshold) is selected
					for ($i=0 ; $i < count($array) ; $i++) {
						if (isset($array[$i]['value']) && $array[$i]['value'] == 'capture') {
							$remove_index = $i;
						}
					}
					if (isset($remove_index)) {
						unset($array[$remove_index]);
					}
				} else if (!empty($fund_type) && $fund_type == 'capture') {
					// Removing 'Preauth' if capture (Immediately Deliver Funds) is selected
					for ($i=0 ; $i < count($array) ; $i++) {
						if (isset($array[$i]['value']) && $array[$i]['value'] == 'preauth') {
							$remove_index = $i;
						}
					}
					if (isset($remove_index)) {
						unset($array[$remove_index]);
					}
				}
			}
		}
	}
	return $array;
}

add_action('init', 'md_ide_check_payment_settings');

function md_ide_check_payment_settings() {
	if (isset($_GET['payment_settings']) && $_GET['payment_settings'] == 1 && is_user_logged_in()) {
		add_filter('the_content', 'md_ide_payment_settings');
	}
}

function md_ide_payment_settings($content) {
	ob_start();
	global $current_user;
	get_currentuserinfo();
	$user_id = $current_user->ID;
	$content = null;
	$settings = get_option('memberdeck_gateways');
	$crm_settings = get_option('crm_settings');		//Getting CRM settings for mailchimp (show/hide)

	if (!empty($settings)) {
		if (is_array($settings)) {
			$epp_fes = $settings['epp_fes'];
			$esc = $settings['esc'];
			$ebm = $settings['ebm'];
		}
	}

	// Checking if mailchimp settings are enabled in Admin and setting a flog then
	if (!empty($crm_settings)) {
		$enable_mailchimp = $crm_settings['enable_mailchimp'];
	}
	else {
		$enable_mailchimp = 0;
	}

	$paypal_email = get_user_meta($user_id, 'md_paypal_email', true); // deprecated
	$payment_settings = get_user_meta($user_id, 'md_payment_settings', true);
	// For ease of use, assigning the mail settings to another $var
	$mail_settings = (isset($payment_settings['mail_settings']) ? $payment_settings['mail_settings'] : array());

	if (isset($_POST['creator_settings_submit'])) {
		if (empty($payment_settings)) {
			$payment_settings = array();
		}
		do_action('md_payment_settings_post', $_POST, $current_user);
		if (isset($_POST['paypal_email'])) {
			$paypal_email = sanitize_text_field($_POST['paypal_email']);
			$payment_settings['paypal_email'] = $paypal_email;
			update_user_meta($user_id, 'md_paypal_email', $paypal_email); // deprecated
		}
		$payment_settings = apply_filters('md_payment_settings', $payment_settings);

		// Storing mail settings
		$mail_settings = (isset($_POST['mail_settings']) ? $_POST['mail_settings'] : $mail_settings);
		if (!empty($mail_settings)) {
			foreach ($mail_settings as $k=>$v) {
				$mail_settings[$k] = sanitize_text_field($v);
			}
		}
		// Attaching the mail settings to the $payment_settings variable
		$payment_settings['mail_settings'] = $mail_settings;

		update_user_meta($user_id, 'md_payment_settings', $payment_settings);
	}
	$form = array();
	if (isset($epp_fes) && $epp_fes) {
		$form[] = array(
			'label' => __('Paypal Email', 'memberdeck'),
			'value' => (isset($paypal_email) ? $paypal_email : ''),
			'name' => 'paypal_email',
			'type' => 'email',
			'class' => 'required',
			'wclass' => 'form-row'
			);
	}
	$payment_form = new MD_Form($form);
	$output = $payment_form->build_form();
	//echo '<div class="memberdeck">';
	include_once IDC_PATH.'templates/_creatorSettings.php';
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

add_action('init', 'md_ide_add_downloads');

function md_ide_add_downloads() {
	if (isset($_GET['project_files'])) {
		if (is_user_logged_in() && current_user_can('edit_post', absint($_GET['project_files']))) {
			add_filter('the_content', 'md_ide_project_files');
			if (isset($_GET['remove_file'])) {
				$post_id = absint($_GET['remove_file']);
				$post = get_post($post_id);
				if (!empty($post)) {
					$download = get_post_meta($post_id, 'idc_download_id', true);
					$delete = wp_delete_post($post_id, true);
					if ($delete && !empty($download)) {
						ID_Member_Download::delete_download($download);
						add_action('md_ide_before_file_upload', function() {
							echo '<div class="ignitiondeck"><p class="notification green">'.__('File Deleted', 'memberdeck').'</p></div>';
						});
					}
					else {
						add_action('md_ide_before_file_upload', function() {
							echo '<div class="ignitiondeck"><p class="notification red">'.__('Error Deleting File', 'memberdeck').'</p></div>';
						});
					}
				}
				else {
					add_action('md_ide_before_file_upload', function() {
						echo '<div class="ignitiondeck"><p class="notification red">'.__('File Does Not Exist', 'memberdeck').'</p></div>';
					});
				}
			}
		}
		if (isset($_POST['ide_fes_file_upload_submit'])) {
			if (!isset($_POST['ide_fes_verify_upload_nonce']) || !wp_verify_nonce($_POST['ide_fes_verify_upload_nonce'], 'ide_fes_verify_upload_nonce')) {
				return;
			}
			if (get_transient('ide_fes_verify_upload') == $_POST['ide_fes_verify_upload']) {
				return;
			}
			$post_id = absint($_GET['project_files']);
			$project_id = get_post_meta($post_id, 'ign_project_id', true);
			if ($project_id > 0 && !empty($_FILES)) {
				$name = sanitize_text_field($_POST['ide_fes_file_name']);
				$level_attachment = absint($_POST['ide_fes_file_upload_level']);
				$wp_upload_dir = wp_upload_dir();
				if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$file = wp_handle_upload($_FILES['ide_fes_file_upload'], array('test_form' => false));
				$filetype = wp_check_filetype(basename($file['file']), null);
				$title = preg_replace('/\.[^.]+$/', '', basename($file['file']));
				$attachment = array(
			    	'guid' => $wp_upload_dir['url'] . '/' . basename( $file['file'] ), 
			    	'post_mime_type' => $filetype['type'],
			    	'post_title' => $name,
			    	'post_content' => '',
			    	'post_status' => 'inherit'
			  	);
			  	$insert = wp_insert_attachment($attachment, $file['file'], $post_id);
			  	// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once (ABSPATH . 'wp-admin/includes/media.php');
				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $insert, $file['file'] );
				wp_update_attachment_metadata( $insert, $attach_data );
				$owner = mdid_get_owner($project_id, $level_attachment);
				if ($owner > 0) {
					// download args
						$download_name = $name;
						$version = null;
						$hidden = 1;
						$enable_s3 = 0;
						$enable_occ = 0;
						$occ_level = null;
						$id_project = $project_id;
						$position = 'c';
						$licensed = 0;
						$levels = array($owner);
						$dlink = $file['url'];
					//
					$download = new ID_Member_Download(null, $download_name, $version, $hidden, $enable_s3, $enable_occ, $occ_level, $id_project, $position, $licensed, $levels, $dlink);
					$add_dl = $download->add_download();
					if ($add_dl > 0) {
						update_post_meta($insert, 'idc_download_id', $add_dl);
						update_post_meta($insert, 'idc_dl_project_info', array('project_id' => $project_id, 'level_id' => $level_attachment));
						set_transient('ide_fes_verify_upload', $_POST['ide_fes_verify_upload'], 0);
						add_action('md_ide_before_file_upload', function() {
							echo '<div class="ignitiondeck"><p class="notification green">'.__('File Uploaded Successfully', 'memberdeck').'</p></div>';
						});
					}
					else {
						add_action('md_ide_before_file_upload', function() {
							echo '<div class="ignitiondeck"><p class="notification red">'.__('Error Processing Upload. Please check filetype and size.', 'memberdeck').'</p></div>';
						});
					}
				}
			}
		}
	}
}

function md_ide_project_files($content) {
	global $permalink_structure;
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	else {
		$prefix = '?';
	}
	$post_id = absint($_GET['project_files']);
	$project_id = get_post_meta($post_id, 'ign_project_id', true);
	if ($project_id > 0) {
		$project = new ID_Project($project_id);
		$level_count = $project->level_count();
		$levels = array();
		for ($i = 1; $i <= $level_count; $i++) {
			if ($i == 1) {
				$level_title = get_post_meta($post_id, 'ign_product_title', true);
			}
			else {
				$level_title = get_post_meta($post_id, 'ign_product_level_'.$i.'_title', true);
			}
			$levels[] = array('value' => $i, 'title' => $level_title);
		}
		$child_args = array(
			'numberposts' => -1,
			'post_parent' => $post_id,
			'post_type' => 'attachment'
		);
		$children = get_children($child_args);
		$form_vars = array(
			array(
				'before' => wp_nonce_field('ide_fes_verify_upload_nonce', 'ide_fes_verify_upload_nonce', true, false),
				'label' => __('File Name', 'memberdeck'),
				'value' => '',
				'name' => 'ide_fes_file_name',
				'id' => 'ide_fes_file_name',
				'type' => 'text',
				'wclass' => 'form-row text',
				'class' => 'required'
			),
			array(
				'label' => __('File', 'memberdeck'),
				'value' => '',
				'name' => 'ide_fes_file_upload',
				'id' => 'ide_fes_file_upload',
				'type' => 'file',
				'wclass' => 'form-row file',
				'class' => 'required'
			),
			array(
				'label' => __('Project Level', 'memberdeck'),
				'value' => '',
				'name' => 'ide_fes_file_upload_level',
				'id' => 'ide_fes_file_upload_level',
				'type' => 'select',
				'options' => $levels,
				'wclass' => 'form-row select idc_dropdown'
			),
			array(
				'before' => '<input type="hidden" name="ide_fes_verify_upload" value="'.md5(rand(10000, 50000)).'" />',
				'value' => __('Upload File', 'memberdeck'),
				'name' => 'ide_fes_file_upload_submit',
				'type' => 'submit',
			)
		);
		$form = new ID_Form($form_vars);
		do_action('md_ide_before_file_upload');
		$content = '<div class="memberdeck"><div class="md-box-wrapper full-width cf"><div class="md-profile"><div class="md-box half">';
		$content .= '<h3 class="big center">'.__('Manage Project Rewards', 'memberdeck').'</h3>';
		if (!empty($children)) {
			$content .= '<table class="attachments">';
			$content .= '<tr>';
			$content .=	'<th>'.__('Level', 'memberdeck').'</th>';
			$content .=	'<th>'.__('Attached File', 'memberdeck').'</th>';
			$content .=	'<th></th>';
			$content .=	'</tr>';
			foreach ($children as $child) {
				$meta = get_post_meta($child->ID, 'idc_dl_project_info', true);
				if (!empty($meta)) {
					if ($meta['level_id'] == 1) {
						$level_title = get_post_meta($post_id, 'ign_product_title', true);
					}
					else {
						$level_title = get_post_meta($post_id, 'ign_product_level_'.$meta['level_id'].'_title', true);
					}
					$content .=	'<tr>';
					$content .= '<td> '.(isset($level_title) ? $level_title : '').'</td><td> '.$child->post_name.'</td><td> <a title="'.__('Delete Reward', 'memberdeck').'" href="'.md_get_durl().$prefix.'project_files='.$post_id.'&remove_file='.$child->ID.'"><i class="fa fa-remove"></i></a></td>';
					$content .= '</tr>';
				}
			}
			$content .= '</table>';
		}
		$content .= '</div>';
		$content .= '<div class="md-box half"><form name="ide_fes_file_upload_form" action="'.md_get_durl().$prefix.'project_files='.$post_id.'" method="POST" enctype="multipart/form-data">';
		$content .= '<h3 class="big center">'.__('Add Project Rewards', 'memberdeck').'</h3>';
		$content .= $form->build_form();
		$content .= '</form></div>';
		$content .= '</div></div></div>';
	}
	return $content;
}

add_action('ide_before_fes_display', 'md_ide_fes_tabs');
add_action('md_ide_before_file_upload', 'md_ide_fes_tabs');
add_action('wp', function() {
	require (ABSPATH . WPINC . '/pluggable.php');
	if (is_user_logged_in()) {
		add_action('ide_before_backer_profile', 'md_ide_fes_tabs');
		add_action('ide_before_creator_profile', 'md_ide_fes_tabs');
	}
});

function md_ide_fes_tabs($content) {
	ob_start();
	echo '<div class="memberdeck">';
	include_once IDC_PATH.'templates/_mdProfileTabs.php';
	echo '</div>';
	$buffer = ob_get_contents();
	ob_end_clean();
	echo $buffer;
	return;
}

add_action('ide_fes_create', 'mdid_fes_associations', 5, 6);
add_action('ide_fes_update', 'mdid_fes_associations', 5, 6);

function mdid_fes_associations($user_id, $project_id, $post_id, $proj_args, $levels, $auth) {
	/*
	Steps:
	Detect which gateways are enabled so we know how to use auth
	Enable CF for that project
	Create MD Level
	Associate MD Level to ID Project Levels
	Associate user to MD level
	*/
	global $wpdb, $global_currency;

	// enable project for cf
	//update_post_meta($post_id, 'mdid_project_activate', 'yes');
	$res = get_assignments_by_project($project_id);
	if (isset($res)) {
		$count = count($res);
	}
	else {
		$count = 1;
	}
	$new_levels = count($levels) - $count;
	$recurring_array = array('recurring-weekly', 'recurring-monthly', 'recurring-annually');
	
	$i = 0;
	$project_title = get_the_title($post_id);
	foreach ($levels as $level) {
		if ($i + 1 <= (count($levels) - $new_levels)) {
			$level_id = $res[$i]->level_id;
			$level = new ID_Member_Level();
			$level_data = $level->get_level($level_id);
			$args = array();
			// now we see what has changed
			$update = false;
			$old_name = $level_data->level_name;
			$new_name = $project_title.': '.$levels[$i]['title'];
			if ($old_name !== $new_name) {
				$update = true;
			}

			$old_price = $level_data->level_price;
			$new_price = $levels[$i]['price'];

			if ((float) $old_price !== (float) $new_price) {
				$update = true;
			}

			$old_auth = $level_data->txn_type;
			$new_auth = $auth[$i];

			$old_recurring_type = $level_data->recurring_type;

			if (in_array($new_auth, $recurring_array)) {
				// this is a recurring product
				$new_level_type = 'recurring';
				$new_recurring_type = str_replace('recurring-', '', $new_auth);
				$new_auth = 'capture';
			}
			else {
				$new_level_type = 'lifetime';
				$new_recurring_type = 'none';
			}

			if ($old_auth !== $new_auth) {
				$update = true;
			}

			if ($old_recurring_type !== $new_recurring_type) {

				$update = true;
			}
			if ($update) {
				// main data
				$args['level_id'] = $level_data->id;
				$args['level_name'] = $new_name;
				$args['level_price'] = $new_price;
				
				$args['txn_type'] = $new_auth;
				$args['recurring_type'] = $new_recurring_type;
				$args['level_type'] = $new_level_type;
				// defaults
				$args['plan'] = $level_data->plan;
				$args['license_count'] = 0;
				$args['limit_term'] = 0;
				$args['term_length'] = '';
				$args['enable_renewals'] = 0;
				$args['renewal_price'] = '';
				$args['enable_multiples'] = 1;
				if (!empty($global_currency) && $global_currency == "credits") {
					$args['credit_value'] = $new_price;
				} else {
					$args['credit_value'] = 0;
				}
				$args['product_type'] = 'purchase';
				$level->update_level($args);
			}
			do_action('md_ide_update_level', $args, $level_id);
		}
		else {
			// these are new levels
			$title = $levels[$i]['title'];
			$price = $levels[$i]['price'];

			$level = new ID_Member_Level();

			$args = array();
			$args['product_type'] = 'purchase';//($levels == 0 ? 'donation' : 'purchase');
			$args['level_name'] = $project_title.': '.$title;
			$args['level_price'] = $price;
			// Based on Global currency, if it's set to Virtual Currency, then set credit value to $price
			if (!empty($global_currency) && $global_currency == "credits") {
				$args['credit_value'] = $price;
			} else {
				$args['credit_value'] = 0;
			}
			// For product funding type
			$auth_current = $auth[$i];
			if (in_array($auth_current, $recurring_array)) {
				// this is a recurring product
				$level_type = 'recurring';
				$recurring_type = str_replace('recurring-', '', $auth_current);
				$auth_current = 'capture';
			}
			else {
				$level_type = 'lifetime';
				$recurring_type = null;
			}
			$args['txn_type'] = $auth_current;
			$args['level_type'] = $level_type;
			$args['recurring_type'] = $recurring_type;
			if ($level_type == 'recurring') {
				$args['plan'] = $title;
			}
			$args['license_count'] = 0;
			$args['limit_term'] = 0;
			$args['term_length'] = '';
			$args['enable_renewals'] = 0;
			$args['renewal_price'] = '';
			$args['enable_multiples'] = 1;
			$args['product_type'] = 'purchase';
			// create level
			$new_level = $level->add_level($args);
			$level_id = $new_level['level_id'];
			// check existence of assignments
			$level_check = get_assignments_by_level($level_id);
			if (empty($level_check)) {
				// assign cf levels
				$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'mdid_project_levels (levels) VALUES (%s)', serialize(array($i+1)));
				$res = $wpdb->query($sql);
				$assignment_id = $wpdb->insert_id;
				$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'mdid_assignments (level_id, project_id, assignment_id) VALUES (%d, %d, %d)', $level_id, $project_id, $assignment_id);
				$res = $wpdb->query($sql);
				// attach user to this project/level
				$claim_level = update_option('md_level_'.$level_id.'_owner', apply_filters('md_level_owner', $user_id));
			}
			do_action('md_ide_add_level', $args, $level_id);
		}
		$i++;
	}
}

add_action('md_ide_add_level', 'md_ide_add_level_actions', 10, 2);

function md_ide_add_level_actions($args, $level_id) {
	global $stripe_api_version;
	if ($args['level_type'] == 'recurring') {
		$gateways = get_option('memberdeck_gateways', true);
		if (!empty($gateways)) {
			if (is_array($gateways)) {
				if ($gateways['esc']) {
					// create plan
					$sk = stripe_sk($level_id);
					if (!class_exists('Stripe')) {
						require_once (IDC_PATH.'lib/Stripe.php');
					}
					Stripe::setApiKey($sk);
					Stripe::setApiVersion($stripe_api_version);
					if ($args['recurring_type'] == 'weekly') {
						$interval = 'week';
					}
					else if ($args['recurring_type'] == 'monthly') {
						$interval = 'month';
					}
					else if ($args['recurring_type'] == 'annually') {
						$interval = 'year';
					}
					try {
						$plan = Stripe_Plan::create(array(
							'amount' => $args['level_price'] * 100,
							'interval' => $interval,
							'name' => $args['level_name'],
							'currency' => $gateways['stripe_currency'],
							'id' => str_replace(' ','-',strtolower($args['level_name'])))
						);
					}
					catch (Exception $e) {
						// something broke
						print_r($e);
					}
				}
			}
		}
	}
}

add_filter('idc_sendtomc_key', 'ide_mckey_settings', 10, 2);

function ide_mckey_settings($key, $order_id) {
	$order = new ID_Member_Order($order_id);
	$the_order = $order->get_order();
	if (!empty($the_order)) {
		$level_id = $the_order->level_id;
		if ($level_id > 0) {
			$user_id = get_option('md_level_'.$level_id.'_owner');
			if ($user_id > 0) {
				$payment_settings = get_user_meta($user_id, 'md_payment_settings', true);
				$mail_settings = (isset($payment_settings['mail_settings']) ? $payment_settings['mail_settings'] : null);
				if (!empty($mail_settings)) {
					$key = $mail_settings['mailchimp_key'];
				}
			}
		}
	}
	return $key;
}

add_filter('idc_sendtomc_list', 'ide_mclist_settings', 10, 2);

function ide_mclist_settings($list, $order_id) {
	$order = new ID_Member_Order($order_id);
	$the_order = $order->get_order();
	if (!empty($the_order)) {
		$level_id = $the_order->level_id;
		if ($level_id > 0) {
			$user_id = get_option('md_level_'.$level_id.'_owner');
			if ($user_id > 0) {
				$payment_settings = get_user_meta($user_id, 'md_payment_settings', true);
				$mail_settings = (isset($payment_settings['mail_settings']) ? $payment_settings['mail_settings'] : null);
				if (!empty($mail_settings)) {
					$list = $mail_settings['mailchimp_list'];
				}
			}
		}
	}
	return $list;
}

add_action('ide_fes_notify', 'md_ide_notify_admin', 5, 6);

function md_ide_notify_admin($user_id, $project_id, $post_id, $proj_args, $levels, $project_fund_type) {
	global $global_currency;
	$user = get_userdata($user_id);
	$user_login = $user->user_login;

	$settings = get_option('md_receipt_settings');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		$coname = $settings['coname'];
		$coemail = $settings['coemail'];
	}
	else {
		$coname = '';
		$coemail = '';
	}

	if (isset($project_id) && $project_id > 0) {
		$project = new ID_Project($project_id);
		$the_project = $project->the_project();
		$description = get_post_meta($post_id, 'ign_project_description', true);
		$edit_link = admin_url().'/post.php?post='.$post_id.'&action=edit';
		/* 
		** Mail Function
		*/

		// Sending email to customer on the completion of order
		$subject = __('New Project Submission', 'memberdeck');
		$headers = __('From', 'memberdeck').': '.$coname.' <'.$coemail.'>' . "\n";
		$headers .= __('Reply-To', 'memberdeck').': '.$coemail."\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\n";
		$message = '<html><body>';
		$message .= '<div style="padding:10px;background-color:#f2f2f2;">
						<div style="padding:10px;border:1px solid #eee;background-color:#fff;">
						<h2>'.__('Project Submission Notification', 'memberdeck').'</h2>

							<div style="margin:10px 0;">
	  
	  							'.__('You have a new project submission from user ', 'memberdeck').' '.$user_login.__(' with the following attributes', 'memberdeck').':<br /><br />
							</div>';
		$message .= '		<div style="border: 1px solid #333333; width: 500px;">
								<table width="500" border="0" cellspacing="0" cellpadding="5">
	      							<tr bgcolor="#333333" style="color: white">
				                        <td width="100">'.__('Title', 'memberdeck').'</td>
				                        <td width="275">'.__('Description', 'memberdeck').'</td>
				                        <td width="125">'.__('Goal', 'memberdeck').'</td>
				                    </tr>
			                         <tr>
			                           <td width="200">'.get_the_title($post_id).'</td>
			                           <td width="275">'.$description.'</td>
			                           <td width="125">'.$global_currency.number_format($the_project->goal, 2, '.', ',').'</td>
			                      	</tr>
								</table>
							</div>';
		$message .= '		<div style="margin:10px 0;"><a href="'.$edit_link.'">'.__('Use this link', 'memberdeck').'</a>'.__(' to moderate the project', 'memberdeck').'<br /><br />
							</div>';
		$message .= '		<table rules="all" style="border-color:#666;width:80%;margin:20px auto;" cellpadding="10">

	    					<!--table rows-->

							</table>

			               ---------------------------------<br />
			               '.$coname.'<br />
			               <a href="mailto:'.$coemail.'">'.$coemail.'</a>
			           

			            </div>
			        </div>';
		$message .= '</body></html>';
		$send = md_send_mail($coemail, $headers, $subject, $message);
	}
}

add_action('ide_fes_notify', 'md_ide_notify_creator', 5, 6);

function md_ide_notify_creator($user_id, $project_id, $post_id, $proj_args, $levels, $project_fund_type) {
	global $permalink_structure, $global_currency;
	$user = get_userdata($user_id);
	$email = $user->user_email;
	$settings = get_option('md_receipt_settings');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		$coname = $settings['coname'];
		$coemail = $settings['coemail'];
	}
	else {
		$coname = '';
		$coemail = '';
	}
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	else {
		$prefix = '?';
	}
	if (isset($project_id) && $project_id > 0) {
		$project = new ID_Project($project_id);
		$the_project = $project->the_project();
		$description = get_post_meta($post_id, 'ign_project_description', true);
		$durl = md_get_durl();
		$edit_link = $durl.$prefix.'edit_project='.$post_id;
		/* 
		** Mail Function
		*/

		// Sending email to customer on the completion of order
		$subject = __('Project Submission Confirmation', 'memberdeck');
		$headers = __('From', 'memberdeck').': '.$coname.' <'.$coemail.'>' . "\n";
		$headers .= __('Reply-To', 'memberdeck').': '.$coemail."\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\n";
		$message = '<html><body>';
		$message .= '<div style="padding:10px;background-color:#f2f2f2;">
						<div style="padding:10px;border:1px solid #eee;background-color:#fff;">
						<h2>'.__('Project Submission Confirmation', 'memberdeck').'</h2>

							<div style="margin:10px 0;">
	  
	  							'.__('Congratulations. The following project has been submitted for approval', 'memberdeck').':<br /><br />
							</div>';
		$message .= '		<div style="border: 1px solid #333333; width: 500px;">
								<table width="500" border="0" cellspacing="0" cellpadding="5">
	      							<tr bgcolor="#333333" style="color: white">
				                        <td width="100">'.__('Title', 'memberdeck').'</td>
				                        <td width="275">'.__('Description', 'memberdeck').'</td>
				                        <td width="125">'.__('Goal', 'memberdeck').'</td>
				                    </tr>
			                         <tr>
			                           <td width="200">'.get_the_title($post_id).'</td>
			                           <td width="275">'.$description.'</td>
			                           <td width="125">'.$global_currency.number_format($the_project->goal, 2, '.', ',').'</td>
			                      	</tr>
								</table>
							</div>';
		$message .= '		<div style="margin:10px 0;">'.__('You will be notified when the review process has been completed. In the interim, you may use ', 'memberdeck').'<a href="'.$edit_link.'">'.__('this link', 'memberdeck').'</a>'.__(' to continue editing the project', 'memberdeck').'<br /><br />
							</div>';
		$message .= '		<table rules="all" style="border-color:#666;width:80%;margin:20px auto;" cellpadding="10">

	    					<!--table rows-->

							</table>

			               ---------------------------------<br />
			               '.$coname.'<br />
			               <a href="mailto:'.$coemail.'">'.$coemail.'</a>
			           

			            </div>
			        </div>';
		$message .= '</body></html>';
		$send = md_send_mail($email, $headers, $subject, $message);
	}
}

add_action('idc_email_template_option', 'idc_success_notification_options');

function idc_success_notification_options() {
	echo '<option name="success_notification">'.__('Project Success Notification (Backer)', 'memberdeck').'</option>';
	echo '<option name="success_notification_admin">'.__('Project Success Notification (Admin/Creator)', 'memberdeck').'</option>';
	echo '<option name="update_notification">'.__('Project Update Notification', 'memberdeck').'</option>';
	return;
}

add_action('idc_email_template', 'idc_success_notification_text');

function idc_success_notification_text() {
	$success_notification = stripslashes(get_option('success_notification'));
	$success_notification_admin = stripslashes(get_option('success_notification_admin'));
	echo '<div class="form-row success_notification email_text" style="display: none">';
	wp_editor((isset($success_notification) ? $success_notification : ''), "success_notification_text");
	echo '</div>';
	echo '<div class="form-row success_notification_admin email_text" style="display: none">';
	wp_editor((isset($success_notification_admin) ? $success_notification_admin : ''), "success_notification_admin_text");
	echo '</div>';
	return;
}

add_action('idc_email_template', 'idc_update_notification_text');

function idc_update_notification_text() {
	$update_notification = stripslashes(get_option('update_notification'));
	echo '<div class="form-row update_notification email_text" style="display: none">';
	wp_editor((isset($update_notification) ? $update_notification : ''), "update_notification_text");
	echo '</div>';
	return;
}

add_action('idfu_update_create', 'idc_ide_update_notification', 10, 2);

function idc_ide_update_notification($post_id, $project_id) {
	$text = get_option('update_notification');
	if (empty($text)) {
		$text = get_option('update_notification_default');
	}
	if (!empty($text)) {
		// get project info
		$project = new ID_Project($project_id);
		$project_post_id = $project->get_project_postid();
		$project_title = get_the_title($project_post_id);
		// get update info
		$update = get_post($post_id);
		if (!empty($update)) {
			$update_title = $update->post_title;
		}
		// company info
		$settings = get_option('md_receipt_settings');
		if (!empty($settings)) {
			if (!is_array($settings)) {
				$settings = unserialize($settings);
			}
			$coname = $settings['coname'];
			$coemail = $settings['coemail'];
		}
		else {
			$coname = '';
			$coemail = get_option('admin_email', null);
		}
		// filter merge tags
		$merge_swap = array(
			array(
				'tag' => '{{PROJECT_NAME}}',
				'swap' => $project_title
				),
			array(
				'tag' => '{{COMPANY_NAME}}',
				'swap' => $coname
				),
			array(
				'tag' => '{{COMPANY_EMAIL}}',
				'swap' => $coemail
				),
			array(
				'tag' => '{{UPDATE_TITLE}}',
				'swap' => $update->post_title
				),
			array(
				'tag' => '{{UPDATE_CONTENT}}',
				'swap' => $update->post_content
				),
			);
		foreach ($merge_swap as $swap) {
			$text = str_replace($swap['tag'], $swap['swap'], $text);
		}
		// get all orders
		$idcf_orders = ID_Order::get_orders_by_project($project_id);
		if (!empty($idcf_orders)) {
			foreach ($idcf_orders as $idcf_order) {
				$email = $idcf_order->email;
				$pay_id = $idcf_order->id;
				$mdid_order = mdid_payid_check($pay_id);
				if (!empty($mdid_order)) {
					$idc_order = $mdid_order->order_id;
					$order = new ID_Member_Order($idc_order);
					$the_order = $order->get_order();
					if (!empty($the_order)) {
						$user_id = $the_order->user_id;
						$user = get_user_by('id', $user_id);
						if (!empty($user)) {
							$fname = $user->user_firstname;
							$lname = $user->user_lastname;
							$text = str_replace('{{NAME}}', $fname.' '.$lname, $text);
						}
						$message = '<html><body>';
						$message .= wpautop($text);
						$message .= '</body></html>';
						$subject = $update_title;
						$mail = new ID_Member_Email($email, $subject, $message, (isset($user_id) ? $user_id : ''));
						$send_mail = $mail->send_mail();
					}
				}
				
			}
		}
	}
}

add_action('idc_email_help_after', 'idc_update_merge_tags');

function idc_update_merge_tags() {
	$content = '<h4>'.__('Update Information', 'memberdeck').'</h4>';
	$content .= '<p><em>'.__('Update Title', 'memberdeck').'</em>: {{UPDATE_TITLE}}</p>';
	$content .= '<p><em>'.__('Update Content', 'memberdeck').'</em>: {{UPDATE_CONTENT}}</p>';
	echo $content;
}

function idc_ide_assign_user() {
	$claim_product = 0;
	if (isset($_POST['user_id'])) {
		if ($_POST['user_id'] == 'remove') {
			$user_id = 'remove';
		}
		else {
			$user_id = absint($_POST['user_id']);
		}
	}
	if (isset($_POST['Product'])) {
		$product = absint($_POST['Product']);
	}
	if (!empty($user_id) && !empty($product)) {
		$claim = get_option('md_level_'.$product.'_owner', 0);
		if ($user_id == 'remove') {
			delete_option('md_level_'.$product.'_owner');
		}
		else if ($claim == $user_id) {
			$claim_product = true;
		}
		else {
			$claim_product = update_option('md_level_'.$product.'_owner', $user_id);
		}
	}
	echo json_encode($claim_product);
	exit;
}

add_action('wp_ajax_idc_ide_assign_user', 'idc_ide_assign_user');
add_action('wp_ajax_nopriv_idc_ide_assign_user', 'idc_ide_assign_user');
?>