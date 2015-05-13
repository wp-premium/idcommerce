<div class="md-requiredlogin login">
	<h3><?php _e('This content is restricted to members only', 'memberdeck'); ?>.</h3>
	<p><?php _e('Please login or register for access', 'memberdeck'); ?>.</p>
	<?php if (isset($_GET['login_failure']) && $_GET['login_failure'] == 1) {
		echo '<p class="error">Login failed</p>';
	} ?>
	<?php if (!is_user_logged_in()) { ?>
		<?php
		$durl = md_get_durl();
		$args = array('redirect' => $durl,
			'echo' => false);
		echo wp_login_form($args); ?>
	<?php } 
	do_action('idc_below_login_form');
	?>
	<p><a class="lostpassword" href="<?php echo site_url(); ?>/wp-login.php?action=lostpassword"><?php _e('Lost Password', 'memberdeck'); ?></a></p>
</div>