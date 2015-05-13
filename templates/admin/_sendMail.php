<div class="wrap">
	<div class="icon32" id="icon-md"></div><h2><?php _e('Messaging', 'memberdeck'); ?></h2>
	<div class="md-settings-container">
		<div class="postbox-container" style="width:95%; margin-right: 2%">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('Send Message to User Group', 'memberdeck'); ?></span></h3>
						<div class="inside">
							<form action="" method="POST" id="mail_form" name="mail_form">
								<div class="form-input">
									<label for="subject">Subject</label><br/>
									<input type="text" name="subject" id="subject" value=""/>
								</div>
								<div class="form-input">
									<label for="message">Message</message><br/>
									<?php echo wp_editor('', 'message', array('media_buttons' => true, 'textarea_name' => 'message')); ?>
									<!--<textarea name="message" id="message"></textarea>-->
								</div>
								<button type="submit" id="send_mail" name="send_mail" class="submit-button button button-primary"><?php _e('Send Mail', 'memberdeck'); ?></button> <button type="button" id="back" class="back-button button" onclick="document.location.href='<?php echo $back_url; ?>'"><?php _e('Back', 'memberdeck'); ?></button> 
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>