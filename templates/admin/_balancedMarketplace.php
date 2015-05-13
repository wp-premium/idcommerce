<div class="wrap memberdeck">
	<div class="icon32" id="icon-options-general"></div><h2 class="title"><?php _e('Balanced Marketplaces', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<br style="clear: both;"/>
	<div class="md-settings-container">
		<div class="postbox-container" style="width:60%; margin-right: 5%">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('Fee Settings', 'memberdeck'); ?></span></h3>
						<div class="inside">
							<form method="POST" action="" id="idsc_settings" name="idsc_settings">
								<div class="form-select">
									<label for="fee_type"><?php _e('Fee Type', 'memberdeck'); ?></label>
									<div>
										<select name="fee_type" id="fee_type">
											<option value="flat" <?php echo (isset($bm_settings['fee_type']) && $bm_settings['fee_type'] == 'flat' ? 'selected="selected"' : ''); ?>><?php _e('Flat Fee (in cents)', 'memberdeck'); ?></option>
											<option value="percentage" <?php echo (isset($bm_settings['fee_type']) && $bm_settings['fee_type'] == 'percentage' ? 'selected="selected"' : ''); ?>><?php _e('Percentage', 'memberdeck'); ?></option>
										</select>
									</div>
								</div>
								<div class="form-select">
									<label for="fee_payer"><?php _e('Fee Payer', 'memberdeck'); ?></label>
									<div>
										<select name="fee_payer" id="fee_payer">
											<option value="buyer" <?php echo (isset($bm_settings['fee_payer']) && $bm_settings['fee_payer'] == 'buyer' ? 'selected="selected"' : ''); ?>><?php _e('Buyer', 'memberdeck'); ?></option>
											<option value="seller" <?php echo (isset($bm_settings['fee_payer']) && $bm_settings['fee_payer'] == 'seller' ? 'selected="selected"' : ''); ?>><?php _e('Seller', 'memberdeck'); ?></option>
										</select>
									</div>
								</div>
								<div class="form-input">
									<label for="bm_fee"><?php _e('Fee Amount (numeric characters only)', 'memberdeck'); ?></label>
									<div>
										<input type="text" name="bm_fee" id="bm_fee" value="<?php echo (isset($bm_settings['bm_fee']) ? $bm_settings['bm_fee'] : ''); ?>"/>
									</div>
								</div>
								<div class="submit">
									<input type="submit" name="bm_submit" id="bm_submit" class="button"/>
								</div>
							</form>
						</div>
					</div>
					<div class="postbox bm_payouts">
						<h3 class="hndle"><span><?php _e('Payout Settings', 'memberdeck'); ?></span></h3>
						<div class="inside">
							<form method="POST" action="" id="idsc_settings" name="idsc_settings">
								<div id="projects">
									<select id="level-list" name="level-list">
										<option><?php _e('Select Product to Process', 'memberdeck'); ?></option>
									</select>
								</div>
								<div class="payout-info" style="display: none;">
									<p>
										<strong><?php _e('Payee Name', 'memberdeck'); ?>:</strong> <span class="payee-name"></span><br/>
										<strong><?php _e('Customer ID', 'memberdeck'); ?>:</strong> <span class="customer-id"></span><br/>
										<strong><?php _e('Total Orders', 'memberdeck'); ?>:</strong> <span class="total-orders"></span><br/>
										<strong><?php _e('Total Payout', 'memberdeck'); ?>:</strong> $<span class="total-payout" data-payout=""></span>
									</p>
								</div>
								<div class="submit">
									<input type="submit" name="bm_submit" id="submit" class="button"/>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Begin Sidebar -->
		<!--<div class="postbox-container" style="width:35%;">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('Using Balanced Marketplaces', 'memberdeck'); ?></span></h3>
						<div class="inside">
						</div>
					</div>
				</div>
			</div>
		</div>-->
		<!-- End Sidebar -->
	</div>
</div>