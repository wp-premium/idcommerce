<div class="wrap memberdeck">
	<div class="icon32" id="icon-md"></div><h2 class="title"><?php _e('Email Settings', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div class="md-settings-container">
	<div class="postbox-container" style="width:65%; margin-right: 3%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Template Settings', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form method="POST" action="" id="gateway-settings" name="gateway-settings">
							<div class="form-row">
								<label for="template_select"><?php _e('Select Template', 'memberdeck'); ?></label>
								<select name="template_select" id="template_select">
									<option name="registration_email"><?php _e('Registration Email (Paypal)', 'memberdeck'); ?></option>
									<option name="welcome_email"><?php _e('Welcome Email', 'memberdeck'); ?></option>
									<option name="purchase_receipt"><?php _e('Purchase Receipt', 'memberdeck'); ?></option>
									<option name="preorder_receipt"><?php _e('Pre-Order Receipt', 'memberdeck'); ?></option>
									<?php do_action('idc_email_template_option'); ?>
								</select>
							</div>
							<span><?php _e('Leave empty to use default template', 'memberdeck'); ?></span>
								<div class="form-row registration_email email_text" style="display: none"><?php wp_editor((isset($registration_email) ? $registration_email : ''), "registration_email_text"); ?></div>
								<div class="form-row welcome_email email_text" style="display: none"><?php wp_editor((isset($welcome_email) ? $welcome_email : ''), "welcome_email_text"); ?></div>
								<div class="form-row purchase_receipt email_text" style="display: none"><?php wp_editor((isset($purchase_receipt) ? $purchase_receipt : ''), "purchase_receipt_text"); ?></div>
								<div class="form-row preorder_receipt email_text" style="display: none"><?php wp_editor((isset($preorder_receipt) ? $preorder_receipt : ''), "preorder_receipt_text"); ?></div>
								<?php do_action('idc_email_template'); ?>
							<div class="form-row">
								<button name="edit_template" id="edit_template" class="button"><?php _e('Save Template', 'memberdeck'); ?></button> <button name="restore_default" id="restore_default" class="button"><?php _e('Restore Default', 'memberdeck'); ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Begin Sidebar -->
	<div class="postbox-container" style="width:32%;">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox info">
					<h3 class="hndle"><span><?php _e('Merge Tags', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<p><?php _e('Note: Some tags may not be available for certain template types', 'memberdeck'); ?>.</p>
						<?php do_action('idc_email_help_before'); ?>
						<h4><?php _e('Company Information', 'memberdeck'); ?></h4>
						<p><em><?php _e('Company Name', 'memberdeck'); ?></em>: {{COMPANY_NAME}}</p>
						<p><em><?php _e('Company Email', 'memberdeck'); ?></em>: {{COMPANY_EMAIL}}</p>
						<p><em><?php _e('Site Name', 'memberdeck'); ?></em>: {{SITE_NAME}}</p>
						<h4><?php _e('User Information', 'memberdeck'); ?></h4>
						<p><em><?php _e('Name', 'memberdeck'); ?></em>: {{NAME}}</p>
						<p><em><?php _e('Email Address', 'memberdeck'); ?></em>: {{EMAIL}}</p>
						<h4><?php _e('Order Information', 'memberdeck'); ?></h4>
						<p><em><?php _e('Product Name', 'memberdeck'); ?></em>: {{PRODUCT_NAME}}</p>
						<p><em><?php _e('Order Amount', 'memberdeck'); ?></em>: {{AMOUNT}}</p>
						<p><em><?php _e('Transaction ID', 'memberdeck'); ?></em>: {{TXN_ID}}</p>
						<p><em><?php _e('Order/Registration Date', 'memberdeck'); ?></em>: {{DATE}}</p>
						<h4><?php _e('Project Information', 'memberdeck'); ?></h4>
						<p><em><?php _e('Project Name', 'memberdeck'); ?></em>: {{PROJECT_NAME}}</p>
						<p><em><?php _e('End Date', 'memberdeck'); ?></em>: {{END_DATE}}</p>
						<?php do_action('idc_email_help_after'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Sidebar -->
</div>
</div>