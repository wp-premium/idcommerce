<h2><?php _e('Become a Creator', 'memberdeck'); ?></h2>
<div class="form-row inline">
	<input type="checkbox" size="20" class="enable_creator" name="enable_creator" value="1" <?php echo (isset($enable_creator) && $enable_creator ? 'checked="checked"' : ''); ?>/> 
	<label for="enable_creator"><?php _e('Check to enable creator functionality', 'memberdeck'); ?></label>
</div>