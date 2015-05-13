<div class="wrap memberdeck">
	<div class="icon32" id="icon-options-general"></div><h2 class="title"><?php _e('Stripe Connect', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<br style="clear: both;"/>
	<div class="postbox-container" style="width:60%; margin-right: 5%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Application Settings', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form method="POST" action="" id="idsc_settings" name="idsc_settings">
							<div class="form-input">
								<p>
									<label for="client_id"><?php _e('Production Client ID', 'memberdeck'); ?></label><br/>
									<input type="text" name="client_id" id="client_id" value="<?php echo (isset($client_id) ? $client_id : ''); ?>"/>
								</p>
							</div>
							<div class="form-input">
								<p>
									<label for="dev_client_id"><?php _e('Development Client ID', 'memberdeck'); ?></label><br/>
									<input type="text" name="dev_client_id" id="dev_client_id" value="<?php echo (isset($dev_client_id) ? $dev_client_id : ''); ?>"/>
								</p>
							</div>
							<div class="form-select">
								<p>
									<label for="fee_type"><?php _e('Fee Type', 'memberdeck'); ?></label><br/>
									<select name="fee_type" id="fee_type">
										<option value="flat" <?php echo (isset($fee_type) && $fee_type == 'flat' ? 'selected="selected"' : ''); ?>><?php _e('Flat Fee (in cents)', 'memberdeck'); ?></option>
										<option value="percentage" <?php echo (isset($fee_type) && $fee_type == 'percentage' ? 'selected="selected"' : ''); ?>><?php _e('Percentage', 'memberdeck'); ?></option>
									</select>
								</p>
							</div>
							<div class="form-input">
								<p>
									<label for="app_fee"><?php _e('Fee Amount (numeric characters only)', 'memberdeck'); ?></label><br/>
									<input type="text" name="app_fee" id="app_fee" value="<?php echo (isset($app_fee) ? $app_fee : ''); ?>"/>
								</p>
							</div>
							<div class="form-input">
								<p>
									<label for="button-style"><?php _e('Button Style', 'memberdeck'); ?></label><br/>
									<select id="button-style" name="button-style">
										<option value="stripe-connect" <?php echo (empty($button_style) || $button_style == 'stripe-connect' ? 'selected="selected"' : ''); ?>><?php _e('Blue on Light', 'memberdeck'); ?></option>
										<option value="stripe-connect dark" <?php echo (isset($button_style) && $button_style == 'stripe-connect dark' ? 'selected="selected"' : ''); ?>><?php _e('Blue on Dark', 'memberdeck'); ?></option>
										<option value="stripe-connect light-blue" <?php echo (isset($button_style) && $button_style == 'stripe-connect light-blue' ? 'selected="selected"' : ''); ?>><?php _e('Light on Light', 'memberdeck'); ?></option>
										<option value="stripe-connect light-blue dark" <?php echo (isset($button_style) && $button_style == 'stripe-connect light-blue dark' ? 'selected="selected"' : ''); ?>><?php _e('Light on Dark', 'memberdeck'); ?></option>
									</select><br/>
									<span id="button-display">
										<a class="stripe-connect"><span><?php _e('Connect with Stripe', 'memberdeck'); ?></span></a>
									</span>
								</p>
							</div>
							<div class="form-check">
								<p>
									<input type="checkbox" name="dev_mode" id="dev_mode" <?php echo (isset($dev_mode) && $dev_mode == 1 ? 'checked="checked"' : ''); ?>/> <label for="dev_mode"><?php _e('Enable Development Mode', 'memberdeck'); ?></label>
								</p>
							</div>
							<div class="submit">
								<input type="submit" name="sc_submit" id="submit" class="button button-primary"/>
							</div>
						</form>
					</div>
				</div>
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('User Management', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form method="POST" action="" id="idsc_users" name="idsc_users">
							<div class="form-input">
								<p><?php _e('Use this option to revoke or clear credentials of Stripe connected users.', 'memberdeck'); ?></p>
								<label for="clear_creds"><?php _e('Revoke Credentials', 'memberdeck'); ?></label><br/>
								<select id="clear_creds" name="clear_creds">
									<option value=""><?php _e('Select User', 'memberdeck'); ?></option>
								</select>
							</div>
							<div class="submit">
								<input type="submit" name="sc_revoke" id="sc_revoke" class="button" value="<?php _e('Revoke Credentials', 'memberdeck'); ?>"/>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Begin Sidebar -->
	<div class="postbox-container" style="width:35%;">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Using Stripe Connect', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<p><?php _e('Stripe Connect enables site owners to process transactions via Stripe Connect user accounts, and if desired, to charge a fee for doing so.', 'memberdeck'); ?></p>
						<p><?php _e('In order to use <a href="https://stripe.com/connect" target="_blank">Stripe Connect</a>, you will need a <a href="http://stripe.com" target="_blank">Stripe account</a> with an application created via the dashboard.', 'memberdeck'); ?></p>	
						<p><?php _e('When creating your Stripe Connect application, ensure that your URL\'s display as follows: ', 'memberdeck'); ?></p>
						<p><strong><?php _e('http://yourdomain.com/[dashboard-link]?payment_settings=1&ipn_handler=sc_return', 'memberdeck'); ?></strong></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Sidebar -->
</div>