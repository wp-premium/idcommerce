<?php

/*
// TEMP: Enable update check on every request. Normally you don't need this! This is for testing only!
// NOTE: The 
//	if (empty($checked_data->checked))
//		return $checked_data; 
// lines will need to be commented in the check_for_plugin_update function as well.
*/
/*set_site_transient('update_plugins', null);

// TEMP: Show which variables are being requested when query plugin API
add_filter('plugins_api_result', 'aaa_result', 10, 3);
function aaa_result($res, $action, $args) {
	print_r($res);
	return $res;
}
// NOTE: All variables and functions will need to be prefixed properly to allow multiple plugins to be updated
*/

$api_url = 'https://ignitiondeck.com/id/pluginserv/';
$idc_plugin_slug = basename(dirname(__FILE__));

// Take over the update check
add_filter('pre_set_site_transient_update_plugins', 'check_for_idc_update', 20);

function check_for_idc_update($checked_data) {
	global $api_url, $idc_plugin_slug, $wp_version;
	$api_key = '';
	$general = get_option('md_receipt_settings');
	if (!empty($general)) {
		if (!is_array($general)) {
			$general = unserialize($general);
		}
		$api_key = $general['license_key'];
	}
	//Comment out these two lines during testing.
	if (empty($checked_data->checked)) {
		return $checked_data;
	}
	$args = array(
		'slug' => $idc_plugin_slug,
		'version' => $checked_data->checked[$idc_plugin_slug .'/'. $idc_plugin_slug .'.php'],
	);
	$request_string = array(
			'body' => array(
				'action' => 'basic_check', 
				'request' => serialize($args),
				'api-key' => $api_key
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);

	// Start checking for an update
	$raw_response = wp_remote_post($api_url, $request_string);

	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)) {
		$response = unserialize($raw_response['body']);
	}

	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$idc_plugin_slug .'/'. $idc_plugin_slug .'.php'] = $response;

	return $checked_data;
}


// Take over the Plugin info screen
add_filter('plugins_api', 'idc_api_call', 10, 3);

function idc_api_call($def, $action, $args) {
	global $idc_plugin_slug, $api_url, $wp_version, $api_key;

	if (!isset($args->slug) || ($args->slug != $idc_plugin_slug))
		return false;

	// Get the current version
	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[$idc_plugin_slug .'/'. $idc_plugin_slug .'.php'];
	$args->version = $current_version;

	$request_string = array(
			'body' => array(
				'action' => $action, 
				'request' => serialize($args),
				'api-key' => $api_key
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);

	$request = wp_remote_post($api_url, $request_string);

	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);

		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}

	return $res;
}

function idc_validate_key($key) {
	$ch = curl_init('http://ignitiondeck.com/id/?action=md_validate_license&key='.$key);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (!$response) {
    	echo 'Curl error: '.curl_error($ch);
    }
    curl_close($ch);
    $data = json_decode($response);
    $valid = $data->valid;
    if (isset($data->download_id)) {
    	$download = $data->download_id;
    }
    else {
    	$download = null;
    }
    return array('response' => $valid, 'download' => $download);
}
add_action('schedule_twicedaily_idc_cron', 'schedule_twicedaily_idc_cron');

function schedule_twicedaily_idc_cron() {
	$valid = 0;
	$validate = md_validate_key($license_key);
	if (isset($validate['response'])) {
		if ($validate['response']) {
			if (isset($validate['download'])) {
				if ($validate['download'] == '29') {
					$valid = 1;
				}
			}
		}
	}
	update_option('is_idc_licensed', $valid);
}
?>