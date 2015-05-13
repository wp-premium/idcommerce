<div class="memberdeck">
	<form action="" method="POST" id="payment-form" name="reg-form" data-regkey="<?php echo (isset($reg_key) ? $reg_key : ''); ?>">
		<div id="logged-input" class="no">
			<div class="form-row third">
				<label><?php _e('First Name', 'memberdeck'); ?></label>
				<input type="text" size="20" class="first-name required" name="first-name" value="<?php echo (isset($user_firstname) ? $user_firstname : ''); ?>"/>
			</div>
			<div class="form-row twothird">
				<label><?php _e('Last Name', 'memberdeck'); ?></label>
				<input type="text" size="20" class="last-name required" name="last-name" value="<?php echo (isset($user_lastname) ? $user_lastname : ''); ?>"/>
			</div>
			<div class="form-row">
				<label><?php _e('Email Address', 'memberdeck'); ?></label>
				<input type="email" size="20" class="email required" name="email" value="<?php echo (isset($email) ? $email : ''); ?>"/>
			</div>
			<div class="form-row">
				<label><?php _e('Password', 'memberdeck'); ?></label>
				<input type="password" size="20" class="pw required" name="pw"/>
			</div>
			<div class="form-row">
				<label><?php _e('Re-enter Password', 'memberdeck'); ?></label>
				<input type="password" size="20" class="cpw required" name="cpw"/>
			</div>
			<?php echo do_action('md_register_extrafields'); ?>
		</div>
		<span class="payment-errors"></span>
		<button type="submit" id="id-reg-submit" class="submit-button"><?php _e('Complete Registration', 'memberdeck'); ?></button>
		<?php do_action('idc_below_register_form'); ?>
	</form>
</div>