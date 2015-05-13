<?php if (isset($tag->term_id)) { ?>
<tr class="form-field">
	<th scope="row" valign="top"><label for="name">Protect Category</label></th>
	<td><input name="protect-choice" id="protect_yes" type="radio" value="1" <?php echo (isset($protect) && $protect == 1 ? 'checked="checked"' : ''); ?>> Yes
		<input name="protect-choice" id="protect_no" type="radio" value="0" <?php echo (isset($protect) && $protect == 0 ? 'checked="checked"' : ''); ?>> No
	</td>
</tr>
<?php }
else { ?>
	<input name="protect" id="protect_yes" type="radio" value="1" <?php echo (isset($protect) && $protect == 1 ? 'checked="checked"' : ''); ?>> Yes
	<input name="protect" id="protect_no" type="radio" value="0" <?php echo (isset($protect) && $protect == 0 ? 'checked="checked"' : ''); ?>> No
<?php } ?>
<?php if (isset($levels)) {?>
<tr class="form-field">
	<th scope="row" valign="top"><label><?php _e('Choose levels that can access this content', 'memberdeck'); ?></label></th>
	<td id="level-check">
		<?php
			foreach ($levels as $level) {
				if (isset($array) && in_array($level->id, $array)) {
					$checked = 'checked="checked"';
				}
				else {
					$checked = null;
				}
				echo '<input type="checkbox" name="protect-level[]" value="'.$level->id.'" '.(isset($checked) ? $checked : '').'/>&nbsp<span>'.$level->level_name.'</span>';
				echo '<br/>';
			}
		?>
	</td>
</tr>
<?php } ?>