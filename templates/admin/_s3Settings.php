<div class="wrap memberdeck">
	<div class="icon32" id="icon-md"></div><h2 class="title"><?php _e('Amazon S3 Configuration', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div class="md-settings-container">
	<div class="postbox-container" style="width:65%; margin-right: 3%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('S3 Credentials', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form method="POST" action="" id="gateway-settings" name="gateway-settings">
							<div class="columns" style="">
								<div class="form-input">
									<label for="access_key"><?php _e('Access Key ID', 'memberdeck'); ?></label>
									<input type="text" name="access_key" id="access_key" value="<?php echo (isset($access_key) ? $access_key : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="secret_key"><?php _e('Secret Access Key', 'memberdeck'); ?></label>
									<input type="text" name="secret_key" id="secret_key" value="<?php echo (isset($secret_key) ? $secret_key : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="bucket"><?php _e('Bucket Name', 'memberdeck'); ?></label>
									<input type="text" name="bucket" id="bucket" value="<?php echo (isset($bucket) ? $bucket : ''); ?>"/>
								</div>
							</div>
							<div class="submit">
								<input type="submit" name="s3_submit" id="s3_submit" class="button button-primary" value="<?php _e('Save S3 Settings', 'memberdeck'); ?>" />
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
					<h3 class="hndle"><span><?php _e('Amazon S3 Requirements', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<p><?php _e('When using Amazon to host downloads, it is important that all downloads are in the same bucket, and that when adding a download URL, you use only the file name.', 'memberdeck'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Sidebar -->
</div>
</div>