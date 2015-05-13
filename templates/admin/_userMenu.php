<div class="wrap memberdeck">
	<div class="icon32" id="icon-md"></div><h2 class="title"><?php _e('Members', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div style="clear: both;"><br/></div>
	<form id="members-filter" action="" method="get">
		<p class="search-box">
			<label class="screen-reader-text" for="post-search-input"><?php _e('Search Members', 'memberdeck'); ?>:</label>
			<input type="search" id="post-search-input" name="s" value="">
			<input type="submit" name="" id="search-submit" class="button" value="<?php echo (isset($level_filter) ? __('Search Level', 'memberdeck') : __('Search Members', 'memberdeck')); ?>">
			<input type="hidden" name="level" value="<?php echo (isset($level_filter) ? $level_filter : ''); ?>"/>
		</p>
		<input type="hidden" name="page" value="<?php echo (isset($section) ? $section : ''); ?>"/>
		<div id="user-list">
			<div class="tablenav top">
				<div id="stat-list">
					<div class="user-stat"><a class="<?php echo (isset($_GET['level']) ? '' : 'active'); ?>" href="admin.php?page=<?php echo (isset($section) ? $section : ''); ?>"><div class="user-title" style="display: inline-block;"><?php _e('All Members', 'memberdeck'); ?></div><div class="user-number" style="display: inline-block;"><?php echo $total_users; ?></div></a></div>
				<?php foreach ($levels as $level) {?> 
					<div class="user-stat"><?php echo '<a class="'.(isset($_GET['level']) && $_GET['level'] == $level->id ? 'active' : '').'" href="admin.php?'.(isset($query_string) ? $query_string : '').'&amp;level='.urlencode($level->id).'"><div class="user-title" style="display: inline-block;">'.stripslashes($level->level_name).'</div><div class="user-number" style="display: inline-block;">'.$level->count.'</div></a>'; ?></div>
				<?php } ?>
				</div>
				<div class="tablenav-pages"><span class="displaying-num"><?php echo $pages; ?> <?php _e('items', 'memberdeck'); ?></span>
					<?php if (isset($page) && $page > 1) { ?>
					<span class="pagination-links"><a class="first-page" title="<?php _e('Go to the first page', 'memberdeck'); ?>" href="admin.php?<?php echo (isset($query_first) ? $query_first : ''); ?>">«</a>
					<a class="prev-page" title="<?php _e('Go to the previous page', 'memberdeck'); ?>" href="admin.php?<?php echo (isset($query_next) ? $query_prev : ''); ?>">‹</a>
					<?php } ?>
					<span class="paging-input"><input class="current-page" title="<?php _e('Current page', 'memberdeck'); ?>" type="text" name="p" value="<?php echo $page; ?>" size="1"> of <span class="total-pages"><?php echo $pages; ?></span></span>
					<?php if (isset($page) && $page < $pages) { ?>
					<a class="next-page" title="Go to the next page" href="admin.php?<?php echo (isset($query_next) ? $query_next : ''); ?>">›</a>
					<a class="last-page" title="Go to the last page" href="admin.php?<?php echo (isset($query_last) ? $query_last : ''); ?>">»</a></span>
					<?php } ?>
				</div>
			</div>
			<div class="mail_option">
				<button type="button" class="button button-large" onclick="location.href='<?php echo $mail_url; ?>'">Send Message to Group</button>
			</div>
			<table id="memberdeck-users" class="wp-list-table widefat fixed pages" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column">
							&nbsp;<!--<input id="cb-select-all-1" type="checkbox"/>-->
						</th>
						<th scope="col" id="name" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Name', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="edate" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('User Email', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="levels" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Current Products', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="credits" class="manage-column sortable desc">
							<a href="#">
								<span><?php echo apply_filters('idc_credits_label_replace', __('Credits', 'memberdeck'), true); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="rdate" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Registration Date', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th scope="col" class="manage-column column-cb check-column">
							&nbsp;<!--<input id="cb-select-all-1" type="checkbox"/>-->
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Name', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('User Email', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Current Levels', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="credits" class="manage-column sortable desc">
							<a href="#">
								<span><?php echo apply_filters('idc_credits_label_replace', __('Credits', 'memberdeck'), true); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Registration Date', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
					</tr>
				</tfoot>
				<tbody id="the-list">
					<?php
						// start the loop
						for ($i = $start; $i <= $count; $i++) {
							$name = array();
							$lid = array();
							foreach ($levels as $level) {
								if (isset($users[$i]->access_level)) {
									$access_levels = unserialize($users[$i]->access_level);
									if (is_array($access_levels) && in_array($level->id, $access_levels)) {
										$name[] = stripslashes($level->level_name);
										$lid[] = $level->id;
									}
								}
							}
							if ($i % 2 == 0) {
								$alt = 'alternate';
							}
							else {
								$alt = '';
							}
							if (isset($users[$i]) && !isset($level_filter)) {
								echo '<tr id="user-'.$users[$i]->ID.'" class="user-'.$users[$i]->ID.' hentry '.$alt.'">';
								echo '<th scope="row" class="check-column">&nbsp;<!--<input id="cb-select-'.($i+1).'" type="checkbox" name="post[]" value="'.($i+1).'"/>--></th>';
								echo '<td class="name-title"><a href="#">'.$users[$i]->user_login.'</a></td>';
								echo '<td class="user-email"><a href="#">'.(isset($users[$i]->user_email) ? $users[$i]->user_email : '').'</a></td>';
								echo '<td class="current-levels">';
								$j = 1;
								foreach ($name as $title) 
									{ 
										echo ($j > 1 ? ', '.$title : $title);
										$j++;
									}

								echo '</td>';
								echo '<td class="current-credits">'.(isset($users[$i]->credits) ? $users[$i]->credits : '0').'</td>';
								echo '<td class="reg-date">'.(isset($users[$i]->r_date) ? $users[$i]->r_date : '').'</td>';
								echo '</tr>';
								//$i++;
							}
							else if (isset($level_filter)) {
								foreach ($lid as $l) {
									if ($level_filter == $l) {

										echo '<tr id="user-'.$users[$i]->ID.'" class="user-'.$users[$i]->ID.' hentry '.$alt.'">';
										echo '<th scope="row" class="check-column">&nbsp;<!--<input id="cb-select-'.($i+1).'" type="checkbox" name="post[]" value="'.($i+1).'"/>--></th>';
										echo '<td class=name-title"><a href="#">'.$users[$i]->user_login.'</a></td>';
										echo '<td class="user-email"><a href="#">'.(isset($users[$i]->user_email) ? $users[$i]->user_email : '').'</a></td>';
										echo '<td class="current-levels">';
										$j = 1;
										foreach ($name as $title) 
											{ 
												echo ($j > 1 ? ', '.$title : $title);
												$j++;
											}

										echo '</td>';
										echo '<td class="current-credits">'.(isset($users[$i]->credits) ? $users[$i]->credits : '0').'</td>';
										echo '<td class="reg-date">'.(isset($users[$i]->r_date) ? $users[$i]->r_date : '').'</td>';
										echo '</tr>';
									}
								}
							}
						}
					?>
				</tbody>
			</table>
			<div class="tablenav bottom">
			</div>
		</div>
	</form>
	<div id="user-profile" class="postbox-container" style="width:95%; margin-right: 5%; display: none">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('User Profile', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<div class="memberdeck">
							<form id="user-profile">
								<div class="form-row quarter">
									<label for="nicename"><?php _e('Display Name', 'memberdeck'); ?></label>
									<input type="text" size="20" class="display_name" name="display_name" value=""/>
								</div>
								<div class="form-row quarter">
									<label for="first-name"><?php _e('First Name', 'memberdeck'); ?></label>
									<input type="text" size="20" class="first_name" name="first_name" value=""/>
								</div>
								<div class="form-row half">
									<label for="last-name"><?php _e('Last Name', 'memberdeck'); ?></label>
									<input type="text" size="20" class="last_name" name="last_name" value=""/>
								</div>
								<div class="form-row half">
									<label for="user_email"><?php _e('Email Address', 'memberdeck'); ?></label>
									<input type="email" size="20" class="user_email" name="user_email" value=""/>
								</div>
								<div class="form-row half">
									<label for="url"><?php _e('Website URL', 'memberdeck'); ?></label>
									<input type="url" size="20" class="user_url" name="user_url" value=""/>
								</div>
								<div class="form-row half">
									<label for="description"><?php _e('Bio', 'memberdeck'); ?></label>
									<textarea row="10" class="description" name="description"></textarea>
								</div>
								<div class="form-row half">
									<label for="twitter"><?php _e('Twitter URL', 'memberdeck'); ?></label>
									<input type="twitter" size="20" class="twitter" name="twitter" value=""/>
									<label for="facebook"><?php _e('Facebook URL', 'memberdeck'); ?></label>
									<input type="facebook" size="20" class="facebook" name="facebook" value=""/>
									<label for="google"><?php _e('Google URL', 'memberdeck'); ?></label>
									<input type="google" size="20" class="google" name="google" value=""/>
								</div>
								<div class="form-row">
									<label for="address"><?php _e('Address Line 1', 'memberdeck'); ?></label>
									<input type="text" size="20" class="address" name="address" value=""/>
								</div>
								<div class="form-row">
									<label for="address_two"><?php _e('Address Line 2', 'memberdeck'); ?></label>
									<input type="text" size="20" class="address_two" name="address_two" value=""/>
								</div>
								<div class="form-row half">
									<label for="city"><?php _e('City', 'memberdeck'); ?></label>
									<input type="text" size="20" class="city" name="city" value=""/>
								</div>
								<div class="form-row half">
									<label for="state"><?php _e('State', 'memberdeck'); ?></label>
									<input type="text" size="20" class="state" name="state" value=""/>
								</div>
								<div class="form-row half">
									<label for="zip"><?php _e('Postal Code', 'memberdeck'); ?></label>
									<input type="text" size="20" class="zip" name="zip" value=""/>
								</div>
								<div class="form-row half">
									<label for="country"><?php _e('Country', 'memberdeck'); ?></label>
									<input type="text" size="20" class="country" name="country" value=""/>
								</div>
								<?php do_action('idc_admin_user_profile_after'); ?>
								<div class="submit">
									<button class="button button-primary" id="confirm-edit-profile"><?php _e('Save', 'memberdeck'); ?></button>
									<button class="button" id="cancel-edit-profile"><?php _e('Cancel', 'memberdeck'); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="postbox hidden">
					<h3 class="hndle"><span><?php _e('Subscriptions', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<div class="memberdeck">
							<p style="margin-left: 0;"><?php _e('Manage a customer\'s active subscriptions', 'memberdeck'); ?></p>
							<div class="form-row">
								<p class="sub_response"></p>
								<span class="idc-dropdown">
									<select name="sub_list" class="idc-dropdown__select .idc-dropdown__select--white">
										<option value="0"><?php _e('Select Subscription', 'memberdeck'); ?></option>
									</select>
								</span>
								<button name="cancel_sub" class="hidden button-small button inline" disabled><?php _e('Cancel Subscription', 'memberdeck'); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="edit-user" style="display: none;">
		<form id="update-user">
			<h3><?php _e('Manage Products', 'memberdeck'); ?></h3>
			<p><?php _e('Check or uncheck to grant or remove access to each product. Expiration will be automatically determined based on product settings. Click expiration date to customize', 'memberdeck'); ?>.</p>
			<table class="wp-list-table widefat fixed pages" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"/></th>
						<th><?php _e('Level Name', 'memberdeck'); ?></th>
						<th><?php _e('Order Date', 'memberdeck'); ?></th>
						<th><?php _e('Expiration Date', 'memberdeck'); ?></th>
					</tr>
				</thead>
				<tbody class="form-input">
				</tbody>
			</table>
			<div class="submit">
				<button class="button button-primary" id="confirm-edit"><?php _e('Save', 'memberdeck'); ?></button>
				<button class="button" id="cancel-edit"><?php _e('Cancel', 'memberdeck'); ?></button>
			</div>
		</form>
	</div>
	<div id="user-credits" class="postbox-container" style="width:95%; margin-right: 5%; display: none">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Manage User Credits', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form id="manage-credits">
							<p><?php _e('Manually add or remove credits from user accounts.', 'memberdeck'); ?>.</p>
							<div class="form-row">
								<label for="current-credits"><?php _e('Current Credits', 'memberdeck'); ?></label>
								<input type="text" name="current-credits" id="current-credits" value="" />
							</div>
							<div class="submit">
								<button class="button button-primary" id="confirm-credits"><?php _e('Save', 'memberdeck'); ?></button>
								<button class="button" id="cancel-credits"><?php _e('Cancel', 'memberdeck'); ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>