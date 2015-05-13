<h2><?php _e('Shipping Address', 'memberdeck'); ?></h2>
<p style="margin-left: 0;">	<?php _e('Please provide your up-to-date shipping address.  Your First and Last name will be used as well.', 'memberdeck'); ?>
</p>
<div class="form-row">
	<label for="address"><?php _e('Address Line 1', 'memberdeck'); ?></label>
	<input type="text" size="20" class="address" name="address" value="<?php echo (isset($shipping_info['address']) ? $shipping_info['address'] : ''); ?>"/>
</div>
<div class="form-row">
	<label for="address_two"><?php _e('Address Line 2', 'memberdeck'); ?></label>
	<input type="text" size="20" class="address_two" name="address_two" value="<?php echo (isset($shipping_info['address_two']) ? $shipping_info['address_two'] : ''); ?>"/>
</div>
<div class="form-row half">
	<label for="city"><?php _e('City', 'memberdeck'); ?></label>
	<input type="text" size="20" class="city" name="city" value="<?php echo (isset($shipping_info['city']) ? $shipping_info['city'] : ''); ?>"/>
</div>
<div class="form-row half">
	<label for="state"><?php _e('State', 'memberdeck'); ?></label>
	<input type="text" size="20" class="state" name="state" value="<?php echo (isset($shipping_info['state']) ? $shipping_info['state'] : ''); ?>"/>
</div>
<div class="form-row half">
	<label for="zip"><?php _e('Postal Code', 'memberdeck'); ?></label>
	<input type="text" size="20" class="zip" name="zip" value="<?php echo (isset($shipping_info['zip']) ? $shipping_info['zip']: ''); ?>"/>
</div>
<div class="form-row half">
	<label for="country"><?php _e('Country', 'memberdeck'); ?></label>
	<input type="text" size="20" class="country" name="country" value="<?php echo (isset($shipping_info['country']) ? $shipping_info['country'] : ''); ?>"/>
</div>