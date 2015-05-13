<div class="wrap memberdeck">
	<div class="icon32" id="icon-options-general"></div><h2 class="title"><?php _e('Crowdfunding Settings', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div class="postbox-container" style="width:100%;">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Crowdfunding Settings', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form method="POST" action="" id="idc_cf_settings" name="idc_cf_settings">
							<div class="form-input">
								<label for="global-currency"><?php _e('Global Currency', 'memberdeck'); ?></label><br />
								<select id="global-currency" name="global_currency" data-selected="<?php echo $global_currency; ?>">
								</select>
							</div>
							<?php if (function_exists('is_id_pro') && is_id_pro()) { ?>
							<div class="form-input">
								<label for="project_fund_type"><?php _e('Funding type available for creator submissions.', 'memberdeck'); ?></label><br/>
								<select id="project_fund_type" name="project_fund_type">
									<option value="capture" <?php echo (isset($fund_type) && $fund_type == 'capture' ? 'selected="selected"' : ''); ?>><?php _e('Immediately Deliver Funds', 'memberdeck'); ?></option>
									<option value="preauth" <?php echo (isset($fund_type) && $fund_type == 'preauth' ? 'selected="selected"' : ''); ?>><?php _e('100% Threshold', 'memberdeck'); ?></option>
									<option value="both" <?php echo (isset($fund_type) && $fund_type == 'both' ? 'selected="selected"' : ''); ?>><?php _e('Immediately Deliver Funds + 100%', 'memberdeck'); ?></option>
									<option value="c_sub" <?php echo (isset($fund_type) && $fund_type == 'c_sub' ? 'selected="selected"' : ''); ?>><?php _e('Immediately Deliver Funds + Subscription', 'memberdeck'); ?></option>
									<option value="all" <?php echo (isset($fund_type) && $fund_type == 'all' ? 'selected="selected"' : ''); ?>><?php _e('All Options', 'memberdeck'); ?></option>
								</select>
							</div>
							<p><em><?php _e('Note: 100% funding requires Stripe, Balanced, or First Data be active. Subscriptions require Stripe.', 'memberdeck'); ?></em></p>
							<?php } ?>
							<div class="form-submit">
								<input type="submit" value="<?php _e('Save', 'memberdeck'); ?>" id="save_idc_cf_settings" name="save_idc_cf_settings" class="button button-primary button-large" />
							</div>
						</form>
					</div>
				</div>
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Product/Level Connections', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form method="POST" action="" id="idmember-settings" name="idmember-settings">
							<div class="form-input">
								<label for="edit-level"><?php _e('Pair Products to Crowdfunding Levels', 'memberdeck'); ?></label><br/>
								<select id="edit-level" name="edit-level">
									<option><?php _e('Choose Product', 'memberdeck'); ?></option>
								</select>
							</div>
							<div>
								<p><a href="#" id="master-select-all"><?php _e('Select All', 'memberdeck'); ?></a> &nbsp; <a href="#" id="master-clear-all" class="" style="color:#bc0b0b;"><?php _e('Clear All', 'memberdeck'); ?></a></p>
							</div>
							<div>
								<?php foreach ($projects as $project) {
									$this_project = new ID_Project($project->id);
									$the_project = $this_project->the_project();
									$level_count = $this_project->level_count();
									$post_id = $this_project->get_project_postid();
									$post = get_post($post_id);
									if (empty($post)) {
										continue;
									}
									$cCode = $this_project->currency_code();
									//$active = get_post_meta($post_id, 'mdid_project_activate', true);
								?>
								<?php if ($crowdfunding) { ?>
									<div class="mdid-project-grid" data-projectid="<?php echo $project->id; ?>">
										<ul>
											<li style="display: inline; list-style: none;"><strong class="project-title"><?php echo strip_tags(stripslashes(get_the_title($post_id))); ?></strong></li>
											<li><a href="#" class="select-all"><?php _e('Select All', 'memberdeck'); ?></a> &nbsp; <a href="#" class="clear-all" class="" style="color:#bc0b0b;"><?php _e('Clear', 'memberdeck'); ?></a></li>
											<li>
												<ul>
													<?php for ($i = 1; $i <= $level_count; $i++) { 
														if ($i == 1) {
															$level_title = stripslashes(strip_tags(html_entity_decode($the_project->ign_product_title)));
															$level_price = $the_project->product_price;
														}
														else {
															$level_title = stripslashes(strip_tags(html_entity_decode(get_post_meta($post_id, 'ign_product_level_'.$i.'_title', true))));
															$level_price = get_post_meta($post_id, 'ign_product_level_'.$i.'_price', true);
														}
														$is_level_available = is_level_available($project->id, $i);
														$owner = mdid_get_owner($project->id, $i);
													?>
													<li><input type="checkbox" id="select-<?php echo $project->id; ?>-<?php echo $i; ?>" class="level-select select-<?php echo $project->id; ?>" data-level="<?php echo $i; ?>" data-owner="<?php echo $owner; ?>" <?php echo ($is_level_available ? '' : 'disabled="disabled"'); ?>/><label for="select-<?php echo $project->id; ?>-<?php echo $i; ?>"><?php echo $level_title.' '.$cCode.$level_price; ?></label></li>
													<?php } ?>
												</ul>
											</li>
										</ul>
									</div>
								<?php } ?>
								<?php } ?>
								<br style="clear: both;">
								<button id="save-assignments" class="button button-primary button-large"><?php _e('Save Assignments', 'memberdeck'); ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>