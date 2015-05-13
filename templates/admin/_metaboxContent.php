<div class="wrap">
		<p>
			<label><?php _e('Protect this ', 'memberdeck'); ?>
				<?php if (isset($post)) {
					echo 'post';
				}
				else if (isset($taxonomy)) {
					echo $taxonomy;
				} ?>?
			</label>
		</p>
		<p>
			<input type="radio" name="protect-choice" value="yes" <?php echo (isset($yes) ? $yes : ''); ?>/><span> <?php _e('Yes', 'memberdeck'); ?></span><br/>
			<input type="radio" name="protect-choice" value="no" <?php echo (isset($no) ? $no : ''); ?>/><span> <?php _e('No', 'memberdeck'); ?></span>
		</p>
		<?php if (isset($levels)) {?>
		<p id="level-check">
			<label><?php _e('Choose levels that can access this content', 'memberdeck'); ?></label><br/>
			<?php
				foreach ($levels as $level) {
					if (isset($array) && in_array($level->id, $array)) {
						$checked = 'checked="checked"';
					}
					else {
						$checked = null;
					}
					echo '<input type="checkbox" name="protect-level[]" value="'.$level->id.'" '.(isset($checked) ? $checked : '').'/>&nbsp;';
					echo '<span>'.$level->level_name.'</span>';
					echo '<br/>';
				}
			?>
		</p>
		<?php } ?>
</div>