<div class="memberdeck">
	<form action="" method="POST" id="payment-form" data-currency-code="<?php echo $pp_currency; ?>" data-product="<?php echo (isset($product_id) ? $product_id : ''); ?>" data-type="<?php echo (isset($type) ? $type : ''); ?>" <?php echo (isset($type) && $type == 'recurring' ? 'data-recurring="'.$recurring.'"' : ''); ?> data-free="<?php echo ($level_price == 0 ? 'free' : 'premium'); ?>" data-txn-type="<?php echo (isset($txn_type) ? $txn_type : 'capture'); ?>" data-renewable="<?php echo (isset($renewable) ? $renewable : 0); ?>" data-limit-term="<?php echo (isset($type) && $type == 'recurring' ? $limit_term : 0); ?>" data-term-limit="<?php echo(isset($limit_term) && $limit_term ? $term_length : ''); ?>" data-scpk="<?php echo (isset($sc_pubkey) ? apply_filters('idc_sc_pubkey', $sc_pubkey) : ''); ?>" data-claimedpp="<?php echo (isset($claimed_paypal) ? apply_filters('idc_claimed_paypal', $claimed_paypal) : ''); ?>" <?php echo ($es == 1 || $eb == 1 ? 'style="display: none;"' : ''); ?> data-pay-by-credits="<?php echo ((isset($paybycrd) && $paybycrd == 1) ? '1' : '') ?>">
		<h3 class="checkout-header"><?php echo (isset($level_name) ? $level_name : ''); ?> <?php _e('Checkout', 'memberdeck'); ?></h3>
		<?php if (!is_user_logged_in()) { ?>
			<span class="login-help"><a href="#" class="reveal-login"><?php _e('Already have an account?', 'memberdeck'); ?></a></span>
			<div id="logged-input" class="no">
				<div class="form-row third">
					<label for="first-name"><?php _e('First Name', 'memberdeck'); ?></label>
					<input type="text" size="20" class="first-name required" name="first-name"/>
				</div>
				<div class="form-row twothird">
					<label for="last-name"><?php _e('Last Name', 'memberdeck'); ?></label>
					<input type="text" size="20" class="last-name required" name="last-name"/>
				</div>
				<div class="form-row">
					<label for="email"><?php _e('Email Address', 'memberdeck'); ?></label>
					<input type="email" pattern="[^ @]*@[^ @]*" size="20" class="email required" name="email"/>
				</div>
				<?php if (!$guest_checkout) { ?>
					<div class="form-row">
						<label for="pw"><?php _e('Password', 'memberdeck'); ?></label>
						<input type="password" size="20" class="pw required" name="pw"/>
					</div>
					<div class="form-row">
						<label for="cpw"><?php _e('Re-enter Password', 'memberdeck'); ?></label>
						<input type="password" size="20" class="cpw required" name="cpw"/>
					</div>
				<?php }	else { ?>
					<a href="#" class="reveal-account"><?php _e('Create an account', 'memberdeck'); ?></a>
					<div id="create_account" style="display: none">
						<div class="form-row">
							<label for="pw"><?php _e('Password', 'memberdeck'); ?></label>
							<input type="password" size="20" class="pw required" name="pw"/>
						</div>
						<div class="form-row">
							<label for="cpw"><?php _e('Re-enter Password', 'memberdeck'); ?></label>
							<input type="password" size="20" class="cpw required" name="cpw"/>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php }
		else { ?>
		<div id="logged-input" class="yes">
			<div class="form-row third" style="display: none;">
				<label for="first-name"><?php _e('First Name', 'memberdeck'); ?></label>
				<input type="text" size="20" class="first-name required" name="first-name" value="<?php echo (isset($fname) ? $fname : ''); ?>"/>
			</div>
			<div class="form-row twothird" style="display: none;">
				<label for="last-name"><?php _e('Last Name', 'memberdeck'); ?></label>
				<input type="text" size="20" class="last-name required" name="last-name" value="<?php echo (isset($lname) ? $lname : ''); ?>"/>
			</div>
			<div class="form-row" style="display: none;">
				<label for="email"><?php _e('Email Address', 'memberdeck'); ?></label>
				<input type="email" pattern="[^ @]*@[^ @]*" size="20" class="email required" name="email" value="<?php echo (isset($email) ? $email : ''); ?>"/>
			</div>
		</div>
		<?php } ?>
		<div id="extra_fields" class="form-row">
		<?php echo do_action('md_purchase_extrafields'); ?>
		</div>
		<?php if ($level_price !== '' && $level_price > 0) { ?>
		<div class="payment-type-selector">
			<?php if ($epp == 1) { ?>
			<a id="pay-with-paypal" class="pay_selector" href="#">
				<span><?php _e('Pay with Paypal', 'memberdeck'); ?></span>
			</a>
			<?php } ?>
			<?php if (isset($eppadap) && $eppadap == 1) { ?>
			<a id="pay-with-ppadaptive" class="pay_selector" href="#">
				<span><?php _e('Pay with PayPal', 'memberdeck'); ?></span>
			</a>
			<?php } ?>
			<?php if ($es == 1) { ?>
			<a id="pay-with-stripe" class="pay_selector" href="#">
				<span><?php _e('Pay with Credit Card', 'memberdeck'); ?></span>
			</a>
			<?php } ?>
			<?php if ($eb == 1) { ?>
			<a id="pay-with-balanced" class="pay_selector" href="#">
				<span><?php _e('Pay with Credit Card', 'memberdeck'); ?></span>
			</a>
			<?php } ?>
			<?php if (isset($efd) && $efd == 1) { ?>
			<a id="pay-with-fd" class="pay_selector" href="#">
				<span><?php _e('Pay with Credit Card', 'memberdeck'); ?></span>
			</a>
			<?php } ?>
			<?php if (isset($eauthnet) && $eauthnet == 1) { ?>
			<a id="pay-with-authorize" class="pay_selector" href="#">
				<span><?php _e('Pay with Credit Card', 'memberdeck'); ?></span>
			</a>
			<?php } ?>
			<?php if (isset($mc) && $mc == 1) { ?>
			<a id="pay-with-mc" class="pay_selector" href="#">
				<span><?php _e('Offline Payment', 'memberdeck'); ?></span>
			</a>
			<?php } ?>
			<?php if (isset($paybycrd) && $paybycrd == 1) { ?>
			<a id="pay-with-credits" class="pay_selector" href="#">
				<span><?php _e('Pay with Credits', 'memberdeck'); ?></span>
			</a>
			<?php } ?>
			<?php if (isset($ecb) && $ecb == 1) { ?>
			<a id="pay-with-coinbase" class="pay_selector" href="#">
				<span><?php _e('Pay with Bitcoin', 'memberdeck'); ?></span>
			</a>
			<?php } ?>
		</div>
		<?php } ?>
		<div id="stripe-input" data-idset="<?php echo (isset($instant_checkout) && $instant_checkout == true ? true : false); ?>" data-symbol="<?php echo (isset($stripe_symbol) ? $stripe_symbol : ''); ?>" data-customer-id="<?php echo ((isset($customer_id) && !empty($customer_id)) ? $customer_id : '') ?>" style="display:none;">
			<div class="form-row">
				<label><?php _e('Card Number', 'memberdeck'); ?> <span class="cards"><img src="https://ignitiondeck.com/id/wp-content/themes/id2/images/creditcards-full2.png" alt="<?php _e('Credit Cards Accepted', 'memberdeck'); ?>" /></span></label>
				<input type="text" size="20" autocomplete="off" class="card-number required"/>
			</div>
			<div class="form-row half">
				<label><?php _e('CVC', 'memberdeck'); ?></label>
				<input type="text" size="4" autocomplete="off" class="card-cvc required"/>
			</div>
			<div class="form-row half date">
				<label><?php _e('Expiration (MM/YYYY)', 'memberdeck'); ?></label>
				<input type="text" size="2" class="card-expiry-month"/><span> / </span><input type="text" size="4" class="card-expiry-year required"/>
			</div>
		</div>
		<?php if ($level_price == 0) {?>
		<div id="finaldescFree" class="finaldesc"><p><?php _e('This is a free product.', 'memberdeck'); ?><br/><?php _e('Click continue to add it to your account', 'memberdeck'); ?>.</p></div>
		<?php } ?>
		<?php if ($receipt_settings['show_terms'] == 1) { ?>
		<div class="idc-terms-checkbox">
			<div class="form-row checklist">
				<input type="checkbox" class="terms-checkbox-input required"/>
				<label><?php _e('By checking this box, I agree to the', 'memberdeck'); ?> <span class="link-terms-conditions"><a href="#"><?php echo $terms_content->post_title; ?></a></span> &amp; <span class="link-privacy-policy"><a href="#"><?php echo $privacy_content->post_title; ?></a></span></label>
				<input type="hidden" id="idc-hdn-error-terms-privacy" value="<?php echo $terms_content->post_title; ?> &amp; <?php echo $privacy_content->post_title; ?>" />
			</div>
		</div>
		<?php } ?>
		<div id="finaldescPayPal" class="finaldesc" style="display:none; word-wrap: none;"><p><?php _e('You will be redirected to PayPal to complete your payment of', 'memberdeck'); ?> <span class="currency-symbol"><?php echo $pp_symbol; ?></span><?php echo (isset($level_price) ? apply_filters('idc_price_format', $level_price) : ''); ?>. <?php (!is_user_logged_in() ? __('Once complete, check your email for registration information', 'memberdeck').'.' : ''); ?></p></div>
		<div id="finaldescStripe" class="finaldesc" style="display:none;"><?php _e('Your card will be billed', 'memberdeck'); ?> <span class="currency-symbol"><?php echo $pp_symbol; ?></span><?php echo (isset($level_price) ? apply_filters('idc_price_format', $level_price) : ''); ?> <?php echo (isset($type) && $type == 'recurring' && isset($limit_term) && $limit_term == '1' ? __('in ', 'memberdeck').$term_length.' ' : ''); ?><?php echo (isset($type) && $type == 'recurring' ? $recurring : ''); ?> <?php echo (isset($type) && $type == 'recurring' && isset($limit_term) && $limit_term == '1' ? __('installments', 'memberdeck') : ''); ?> <?php echo (isset($customer_id) ? __('using the card on file', 'memberdeck') : ''); ?> <?php _e('and will appear on your statement as', 'memberdeck'); ?>: <em><?php echo (isset($coname) ? $coname : ''); ?></em>.</div>
		<div id="finaldescCredits" class="finaldesc" style="display:none; word-wrap: none;"><p><?php _e('This product costs', 'memberdeck'); ?> <?php echo apply_filters('idc_price_format', $credit_value).' '.apply_filters('idc_credits_label', __('credits', 'memberdeck'), ($credit_value > 0 ? true : false)); ?>.
			<br /> <?php _e('Your current account credits are', 'memberdeck'); ?> <?php echo apply_filters('idc_price_format', $user_data->credits); ?>. <?php _e('After the purchase your remaining credits will be') ?> <?php echo apply_filters('idc_price_format', ($user_data->credits - $credit_value)); ?>.</p></div>
		<div id="finaldescCoinbase" class="finaldesc" style="display:none; word-wrap: none;">
			<p><?php _e('You will be directed to Coinbase to authenticate and complete your payment of', 'memberdeck'); ?> <?php echo (isset($level_price) ? apply_filters('idc_price_format', $level_price) : ''); ?> <span class="currency-symbol"><?php echo $cb_currency; ?></span>.</p>
		</div>
		<div><?php echo apply_filters('md_purchase_footer', ''); ?></div>
		<span class="payment-errors"></span>
		<input type="hidden" name="reg-price" value="<?php echo (isset($return->level_price) ? $return->level_price : ''); ?>"/>
		<input type="hidden" name="pwyw-price" value="<?php echo (isset($pwyw_price) && $pwyw_price > 0 ? $pwyw_price : ''); ?>"/>
		<button type="submit" id="id-main-submit" class="submit-button"><?php _e('Submit Payment', 'memberdeck'); ?></button>
	</form>
	<div class="md-requiredlogin login login-form" style="display: none;">
		<h3 class="checkout-header"><?php _e('Login', 'memberdeck'); ?></h3>
		<span class="login-help"><a href="#" class="hide-login"><?php _e('Need to register?', 'memberdeck'); ?></a></span>
		<?php
		$args = array('redirect' => $url);
		echo wp_login_form($args);
		?>
	</div>
	<div class="disclaimer">
		<?php echo (isset($id_disclaimer) ? '<p>'.$id_disclaimer.'</p>' : ''); ?>
	</div>

	<?php if ($receipt_settings['show_terms'] == 1) { ?>
	<div class="idc-terms-conditions idc_lightbox mfp-hide">
		<div class="idc_lightbox_wrapper">
			<?php echo wpautop($terms_content->post_content); ?>
		</div>
	</div>
	<div class="idc-privacy-policy idc_lightbox mfp-hide">
		<div class="idc_lightbox_wrapper">
			<?php echo wpautop($privacy_content->post_content); ?>
		</div>
	</div>
	<?php } ?>
</div>
<!-- 
    The easiest way to indicate that the form requires JavaScript is to show
    the form with JavaScript (otherwise it will not render). You can add a
    helpful message in a noscript to indicate that users should enable JS.
-->
<script>
if (window.Stripe) jQuery("#payment-form").show();
</script>
<noscript><p><?php _e('JavaScript is required for the registration form', 'memberdeck'); ?>.</p></noscript>
<div id="ppload"></div>
<?php if ($ecb == 1) { ?>
<div id="coinbaseload" data-button-loaded="no" style="display:none;">
	<input type="hidden" name="id_coinbase_button_code" id="id_coinbase_button_code" value="" />
</div>
<?php } ?>
<?php if ($eb == 1) {
	echo '<script>balanced.init("'.$burl.'"); jQuery("#payment-form").show()</script>';
} ?>
<?php if ($eppadap == 1) {
	// For lightbox
	echo '<script src="https://www.paypalobjects.com/js/external/dg.js"></script>';
	// For mini browser
	/*echo '<script src="https://www.paypalobjects.com/js/external/apdg.js"></script>';*/
}
?>