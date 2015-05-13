<div class="wrap memberdeck">
	<div class="icon32" id="icon-options-general"></div><h2 class="title"><?php _e('Enterprise Settings', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<br style="clear: both;"/>
	<div class="md-settings-container">
		<div class="postbox-container" style="width:60%; margin-right: 5%">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<?php if ($eppadap) { ?>
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('Paypal Adaptive Fee Settings', 'memberdeck'); ?></span></h3>
						<div class="inside">
							<form method="POST" action="" id="idc_enterprise_settings" name="idc_enterprise_settings">
								<div class="form-select">
									<label for="fee_type"><?php _e('Fee Type', 'memberdeck'); ?></label>
									<div>
										<select name="fee_type" id="fee_type">
											<option value="flat" <?php echo (empty($enterprise_settings['fee_type']) || $enterprise_settings['fee_type'] == 'flat' ? 'selected="selected"' : ''); ?>><?php _e('Flat Fee (in cents)', 'memberdeck'); ?></option>
											<option value="percentage" <?php echo (isset($enterprise_settings['fee_type']) && $enterprise_settings['fee_type'] == 'percentage' ? 'selected="selected"' : ''); ?>><?php _e('Percentage', 'memberdeck'); ?></option>
										</select>
									</div>
								</div>
								<div class="form-input">
									<label for="enterprise_fee"><?php _e('Fee Amount (numeric characters only)', 'memberdeck'); ?></label>
									<div>
										<input type="text" name="enterprise_fee" id="enterprise_fee" value="<?php echo (isset($enterprise_settings['enterprise_fee']) ? $enterprise_settings['enterprise_fee'] : ''); ?>"/>
									</div>
								</div>
								<div class="submit">
									<input type="submit" name="enterprise_submit" id="enterprise_submit" class="button"/>
								</div>
							</form>
						</div>
					</div>
					<?php } ?>
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('User Management', 'memberdeck'); ?></span></h3>
						<div class="inside">
							<form method="POST" action="" id="enterprise_users" name="enterprise_users">
								<div class="form-input">
									<p><?php _e('Use this option to assign IDC products to a specific user. If the newly assigned user has not already connected to your site via their dashboard payment settings, they will need to do so prior to going live', 'memberdeck'); ?></p>
									<label for="assign_user"><?php _e('Assign Product to User', 'memberdeck'); ?></label><br/>
									<select id="assign_user" name="assign_user">
										<option value=""><?php _e('Select User', 'memberdeck'); ?></option>
										<option value="remove"><?php _e('No User: Remove Assignment', 'memberdeck'); ?></option>
									</select>
									<select id="assign_product" name="assign_product">
										<option value=""><?php _e('Select Product', 'memberdeck'); ?></option>
									</select>
								</div>
								<div class="submit">
									<input type="submit" name="sc_assign" id="sc_assign" class="button" value="<?php _e('Save Assignment', 'memberdeck'); ?>"/>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>