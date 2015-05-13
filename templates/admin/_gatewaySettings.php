<div class="wrap memberdeck">
	<div class="icon32" id="icon-md"></div><h2 class="title"><?php _e('Payment Gateways', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div class="md-settings-container">
	<div class="postbox-container" style="width:65%; margin-right: 3%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Default Gateway Settings', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form method="POST" action="" id="gateway-settings" name="gateway-settings">
							<div class="form-input" style="text-align: center; border: 1px solid #eee; background: #fefeff; padding: 5px;">
								<input type="checkbox" name="test" id="test" value="1" <?php echo (isset($test) && $test == 1 ? 'checked="checked"' : ''); ?>/>
								<label for="test"><?php _e('Enable Test Mode', 'memberdeck'); ?></label>
								<span style="margin-left: 20px;">
									<input type="checkbox" name="https" id="https" value="1" <?php echo (isset($https) && $https == 1 ? 'checked="checked"' : ''); ?>/>
									<label for="https"><?php _e('Enable HTTPS', 'memberdeck'); ?></label>
								</span>
								<span style="margin-left: 20px;">
									<input type="checkbox" name="manual_checkout" id="manual_checkout" value="1" <?php echo (isset($manual_checkout) && $manual_checkout == 1 ? 'checked="checked"' : ''); ?>/>
									<label for="manual_checkout"><?php _e('Enable Offline Purchases', 'memberdeck'); ?></label>
								</span>
							</div>
							<div class="columns" style="width: 29%; padding-right: 2.5%; margin-right: 2.5%; border-right: 1px solid #C8D3DC;">
								<p class="pp-standard-field">
									<img src="<?php echo plugins_url('/images/PayPal-Logo.png', dirname(dirname(__FILE__))); ?>">
								</p>
								<div class="form-input pp-standard-field pp-adaptive-field">
									<label for="pp-currency"><?php _e('Paypal Currency', 'memberdeck'); ?></label>
									<select id="pp-currency" name="pp-currency" data-selected="<?php echo (isset($pp_currency) ? $pp_currency : 'USD'); ?>">
									</select>
									<input type="hidden" name="pp-symbol" value="<?php echo (isset($pp_symbol) ? $pp_symbol : '$'); ?>"/>
								</div>
								<div class="form-input pp-standard-field pp-adaptive-field live-field">
									<label for="pp-email"><?php _e('Paypal Email', 'memberdeck'); ?></label>
									<input type="text" name="pp-email" id="pp-email" value="<?php echo (isset($pp_email) ? $pp_email : ''); ?>"/>
								</div>
								<div class="form-input pp-standard-field pp-adaptive-field test-field">
									<label for="test-email"><?php _e('Paypal Test Email', 'memberdeck'); ?></label>
									<input type="text" name="test-email" id="test-email" value="<?php echo (isset($test_email) ? $test_email : ''); ?>"/>
								</div>
								<div class="form-input pp-standard-field pp-adaptive-field test-field">
									<label for="paypal-test-redirect"><?php _e('Paypal Test Return URL', 'memberdeck'); ?></label>
									<input type="text" name="paypal-test-redirect" id="paypal-test-redirect" value="<?php echo (isset($paypal_test_redirect) ? $paypal_test_redirect : ''); ?>"/>
								</div>
								<div class="form-input pp-standard-field pp-adaptive-field live-field">
									<label for="paypal-redirect"><?php _e('Paypal Return URL', 'memberdeck'); ?></label>
									<input type="text" name="paypal-redirect" id="paypal-redirect" value="<?php echo (isset($paypal_redirect) ? $paypal_redirect : ''); ?>"/>
								</div>

								<!--<div class="form-input">
									<label for="ppada_currency"><?php _e('Paypal Currency', 'memberdeck'); ?></label>
									<select id="ppada_currency" name="ppada_currency" data-selected="<?php echo (isset($ppada_currency) ? $ppada_currency : 'USD'); ?>">
									</select>
									<input type="hidden" name="pp-symbol" value="<?php echo (isset($pp_symbol) ? $pp_symbol : '$'); ?>"/>
								</div>-->
								<div class="form-input pp-adaptive-field live-field">
									<label for="ppadap_api_username"><?php _e('API Username', 'memberdeck'); ?></label>
									<input type="text" name="ppadap_api_username" id="ppadap_api_username" value="<?php echo (isset($ppadap_api_username) ? $ppadap_api_username : ''); ?>"/>
								</div>
								<div class="form-input pp-adaptive-field live-field">
									<label for="ppadap_api_password"><?php _e('API Password', 'memberdeck'); ?></label>
									<input type="text" name="ppadap_api_password" id="ppadap_api_password" value="<?php echo (isset($ppadap_api_password) ? $ppadap_api_password : ''); ?>"/>
								</div>
								<div class="form-input pp-adaptive-field live-field">
									<label for="ppadap_api_signature"><?php _e('API Signature', 'memberdeck'); ?></label>
									<input type="text" name="ppadap_api_signature" id="ppadap_api_signature" value="<?php echo (isset($ppadap_api_signature) ? $ppadap_api_signature : ''); ?>"/>
								</div>
								<div class="form-input pp-adaptive-field live-field">
									<label for="ppadap_app_id"><?php _e('App ID', 'memberdeck'); ?></label>
									<input type="text" name="ppadap_app_id" id="ppadap_app_id" value="<?php echo (isset($ppadap_app_id) ? $ppadap_app_id : ''); ?>"/>
								</div>
								<!-- Test Settings fields -->
								<div class="form-input pp-adaptive-field test-field">
									<label for="ppadap_api_username_test"><?php _e('Test API Username', 'memberdeck'); ?></label>
									<input type="text" name="ppadap_api_username_test" id="ppadap_api_username_test" value="<?php echo (isset($ppadap_api_username_test) ? $ppadap_api_username_test : ''); ?>"/>
								</div>
								<div class="form-input pp-adaptive-field test-field">
									<label for="ppadap_api_password_test"><?php _e('Test API Password', 'memberdeck'); ?></label>
									<input type="text" name="ppadap_api_password_test" id="ppadap_api_password_test" value="<?php echo (isset($ppadap_api_password_test) ? $ppadap_api_password_test : ''); ?>"/>
								</div>
								<div class="form-input pp-adaptive-field test-field">
									<label for="ppadap_api_signature_test"><?php _e('Test API Signature', 'memberdeck'); ?></label>
									<input type="text" name="ppadap_api_signature_test" id="ppadap_api_signature_test" value="<?php echo (isset($ppadap_api_signature_test) ? $ppadap_api_signature_test : ''); ?>"/>
								</div>
								<div class="form-input pp-adaptive-field test-field">
									<label for="ppadap_app_id_test"><?php _e('Test App ID', 'memberdeck'); ?></label>
									<input type="text" name="ppadap_app_id_test" id="ppadap_app_id_test" value="<?php echo (isset($ppadap_app_id_test) ? $ppadap_app_id_test : ''); ?>"/>
								</div>
								<div class="form-input pp-adaptive-field test-field live-field">
									<label for="ppadap_max_preauth_period"><?php _e('Pre-Approval Period (in days)', 'memberdeck'); ?></label>
									<input type="number" name="ppadap_max_preauth_period" id="ppadap_max_preauth_period" value="<?php echo (isset($ppadap_max_preauth_period) ? $ppadap_max_preauth_period : ''); ?>"/>
								</div>
								<br />
								<div class="form-input inline">
									<input type="checkbox" class="enable_paypal" name="epp" id="epp" value="1" <?php echo (isset($epp) && $epp ? 'checked="checked"' : ''); ?>/>
									<label for="epp"><?php _e('Enable Paypal', 'memberdeck'); ?></label>
								</div>
								<div class="form-input inline">
									<input type="checkbox" class="enable_paypal" name="eppadap" id="eppadap" value="1" <?php echo (isset($eppadap) && $eppadap ? 'checked="checked"' : ''); ?>/>
									<label for="eppadap"><?php _e('Enable PayPal Adaptive', 'memberdeck'); ?></label>
								</div>
								<?php if (function_exists('is_id_pro') && is_id_pro()) { ?>
								<div class="form-input inline">
									<input type="checkbox" name="epp_fes" id="epp_fes" value="1" <?php echo (isset($epp_fes) && $epp_fes == 1 ? 'checked="checked"' : ''); ?>/>
									<label for="epp_fes"><?php _e('Enable for Creators', 'memberdeck'); ?></label>
								</div>
								<?php } ?>
							</div>
							<div class="columns" style="width: 29%; padding-right: 2.5%; margin-right: 2.5%; border-right: 1px solid #C8D3DC;">
								<p>
									<img src="<?php echo plugins_url('/images/Stripe-Logo.png', dirname(dirname(__FILE__))); ?>">
								</p>
								<div class="form-input">
									<label for="stripe_currency"><?php _e('Stripe Currency', 'memberdeck'); ?></label>
									<select id="stripe_currency" name="stripe_currency">
										<option value="0"><?php _e('Choose Currency', 'memberdeck'); ?></option>
										<option value="USD" <?php echo (isset($stripe_currency) && $stripe_currency == 'USD' ? 'selected="selected"' : ''); ?>><?php _e('USD', 'memberdeck'); ?></option>
										<option value="AUD" <?php echo (isset($stripe_currency) && $stripe_currency == 'AUD' ? 'selected="selected"' : ''); ?>><?php _e('AUD', 'memberdeck'); ?></option>
										<option value="CAD" <?php echo (isset($stripe_currency) && $stripe_currency == 'CAD' ? 'selected="selected"' : ''); ?>><?php _e('CAD', 'memberdeck'); ?></option>
										<option value="EUR" <?php echo (isset($stripe_currency) && $stripe_currency == 'EUR' ? 'selected="selected"' : ''); ?>><?php _e('EUR', 'memberdeck'); ?></option>
										<option value="GBP" <?php echo (isset($stripe_currency) && $stripe_currency == 'GBP' ? 'selected="selected"' : ''); ?>><?php _e('GBP', 'memberdeck'); ?></option>
									</select>
								</div>
								<div class="form-input">
									<label for="pk"><?php _e('Stripe Publishable Key', 'memberdeck'); ?></label>
									<input type="text" name="pk" id="pk" value="<?php echo (isset($pk) ? $pk : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="sk"><?php _e('Stripe Secret Key', 'memberdeck'); ?></label>
									<input type="text" name="sk" id="sk" value="<?php echo (isset($sk) ? $sk : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="tpk"><?php _e('Stripe Publishable Key (Test)', 'memberdeck'); ?></label>
									<input type="text" name="tpk" id="tpk" value="<?php echo (isset($tpk) ? $tpk : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="tsk"><?php _e('Stripe Secret Key (Test)', 'memberdeck'); ?></label>
									<input type="text" name="tsk" id="tsk" value="<?php echo (isset($tsk) ? $tsk : ''); ?>"/>
								</div>
								<br/>
								<div class="form-input inline">
									<input type="checkbox" name="es" id="es" value="1" <?php echo (isset($es) && $es == 1 ? 'checked="checked"' : ''); ?> <?php echo ((isset($eauthnet) && $eauthnet == 1) || (isset($eb) && $eb == 1) ? 'disabled="disabled"' : ''); ?>/>
									<label for="es"><?php _e('Enable Stripe', 'memberdeck'); ?></label>
								</div>
								<?php if (function_exists('is_id_pro') && is_id_pro()) { ?>
								<div class="form-input inline">
									<input type="checkbox" name="esc" id="esc" value="1" <?php echo (isset($esc) && $esc == 1 ? 'checked="checked"' : ''); ?> <?php echo ((isset($eauthnet) && $eauthnet == 1) || (isset($eb) && $eb == 1) ? 'disabled="disabled"' : ''); ?>/>
									<label for="esc"><?php _e('Enable Stripe Connect', 'memberdeck'); ?></label>
								</div>
								<?php } ?>
							</div>
							<div class="columns" style="width: 29%;">
								<p>
									<img src="<?php echo plugins_url('/images/balanced_logo.png', dirname(dirname(__FILE__))); ?>">
								</p>
								<div class="form-input">
									<label for="bk"><?php _e('Balanced API Key', 'memberdeck'); ?></label>
									<input type="text" name="bk" id="bk" value="<?php echo (isset($bk) ? $bk : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="burl"><?php _e('Balanced Marketplace URL', 'memberdeck'); ?></label>
									<input type="text" name="burl" id="burl" value="<?php echo (isset($burl) ? $burl : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="btk"><?php _e('Balanced Test Key', 'memberdeck'); ?></label>
									<input type="text" name="btk" id="btk" value="<?php echo (isset($btk) ? $btk : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="bturl"><?php _e('Balanced Test URL', 'memberdeck'); ?></label>
									<input type="text" name="bturl" id="bturl" value="<?php echo (isset($bturl) ? $bturl : ''); ?>"/>
								</div>
								<br/>
								<div class="form-input inline">
									<input type="checkbox" name="eb" id="eb" value="1" <?php echo (isset($eb) && $eb == 1 ? 'checked="checked"' : ''); ?> <?php echo ((isset($eauthnet) && $eauthnet == 1) || (isset($es) && $es == 1) ? 'disabled="disabled"' : ''); ?>/>
									<label for="eb"><?php _e('Enable Balanced', 'memberdeck'); ?></label>
								</div>
								<?php if (function_exists('is_id_pro') && is_id_pro()) { ?>
									<div class="form-input inline">
										<input type="checkbox" name="ebm" id="ebm" value="1" <?php echo (isset($ebm) && $ebm == 1 ? 'checked="checked"' : ''); ?> <?php echo ((isset($eauthnet) && $eauthnet == 1) || (isset($es) && $es == 1) ? 'disabled="disabled"' : ''); ?>/>
										<label for="ebm"><?php _e('Enable Marketplaces', 'memberdeck'); ?></label>
									</div>
								<?php } ?>
							</div>
							<?php if (isset($first_data) && $first_data) { ?>
							<div class="columns clear" style="width: 29%; padding-right: 2.5%; margin-right: 2.5%; margin-top: 40px; border-right: 1px solid #C8D3DC;">
								<p>
									<img src="<?php echo plugins_url('/images/firstdata-logo.png', dirname(dirname(__FILE__))); ?>" style="width: 100px;">
								</p>
								<div class="form-input">
									<label for="gateway_id"><?php _e('Gateway ID', 'memberdeck'); ?></label>
									<input type="text" name="gateway_id" id="gateway_id" value="<?php echo (isset($gateway_id) ? $gateway_id : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="fd_pw"><?php _e('Password', 'memberdeck'); ?></label>
									<input type="text" name="fd_pw" id="fd_pw" value="<?php echo (isset($fd_pw) ? $fd_pw : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="key_id"><?php _e('Key Id', 'memberdeck'); ?></label>
									<input type="text" name="key_id" id="key_id" value="<?php echo (isset($key_id) ? $key_id : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="hmac"><?php _e('HMAC Key', 'memberdeck'); ?></label>
									<input type="text" name="hmac" id="hmac" value="<?php echo (isset($hmac) ? $hmac : ''); ?>"/>
								</div>
								<div class="form-input inline">
									<input type="checkbox" name="efd" id="efd" value="1" <?php echo (isset($efd) && $efd == 1 ? 'checked="checked"' : ''); ?>/>
									<label for="efd"><?php _e('Enable First Data', 'memberdeck'); ?></label>
								</div>
							</div>
							<div class="columns" style="width: 29%; padding-right: 2.5%; margin-right: 2.5%; margin-top: 40px; border-right: 1px solid #C8D3DC;">
								<p>
									<img src="<?php echo plugins_url('/images/coinbase.png', dirname(dirname(__FILE__))); ?>" style="width: 100px;">
								</p>
								<div class="form-input">
									<label for="cb_currency"><?php _e('Coinbase Currency', 'memberdeck'); ?></label>
									<?php //print_r($cb_currencies); ?>
									<select id="cb_currency" name="cb_currency">
										<option value="0"><?php _e('Choose Currency', 'memberdeck'); ?></option>
										<option value="BTC" <?php echo ($settings['cb_currency'] == 'BTC' ? 'selected="selected"' : ''); ?>><?php _e('Bitcoin (BTC)', 'memberdeck'); ?></option>
										<?php foreach ($cb_currencies as $currency) {
											echo '<option value="'.strtoupper($currency->iso).'" '.(!empty($settings['cb_currency']) && $settings['cb_currency'] == $currency->iso ? 'selected="selected"' : '').'>'.$currency->name.'</option>';
										} ?>
									</select>
								</div>
								<div class="form-input">
									<label for="client_secret"><?php _e('Coinbase API Key', 'memberdeck'); ?></label>
									<input type="text" name="coinbase_api_key" id="coinbase_api_key" value="<?php echo (isset($cb_api_key) ? $cb_api_key : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="client_secret"><?php _e('Coinbase API Secret', 'memberdeck'); ?></label>
									<input type="text" name="coinbase_api_secret" id="coinbase_api_secret" value="<?php echo (isset($cb_api_secret) ? $cb_api_secret : ''); ?>"/>
								</div>
								<div class="form-input inline">
									<input type="checkbox" name="ecb" id="ecb" value="1" <?php echo (isset($ecb) && $ecb == 1 ? 'checked="checked"' : ''); ?>/>
									<label for="efd"><?php _e('Enable Coinbase', 'memberdeck'); ?></label>
								</div>
							</div>
							<div class="columns clear" style="width: 29%; margin-top: 49px;">
								<p>
									<img src="<?php echo plugins_url('/images/authorize.net.png', dirname(dirname(__FILE__))); ?>" style="width: 100px;">
								</p>
								<div class="form-input">
									<label for="auth_login_id"><?php _e('API Login ID', 'memberdeck'); ?></label>
									<input type="text" name="auth_login_id" id="auth_login_id" value="<?php echo (isset($auth_login_id) ? $auth_login_id : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="auth_transaction_key"><?php _e('Transaction Key', 'memberdeck'); ?></label>
									<input type="text" name="auth_transaction_key" id="auth_transaction_key" value="<?php echo (isset($auth_transaction_key) ? $auth_transaction_key : ''); ?>"/>
								</div>
								<div class="form-input inline">
									<input type="checkbox" name="eauthnet" id="eauthnet" value="1" <?php echo (isset($eauthnet) && $eauthnet == 1 ? 'checked="checked"' : ''); ?> <?php echo ((isset($eb) && $eb == 1) || (isset($es) && $es == 1) ? 'disabled="disabled"' : ''); ?>/>
									<label for="eauthnet"><?php _e('Enable Authorize.Net', 'memberdeck'); ?></label>
								</div>
							</div>
							
							<?php } ?>
							<div class="submit">
								<input type="submit" name="gateway-submit" id="gateway-submit" class="button button-primary" value="<?php _e('Save Gateway Settings', 'memberdeck'); ?>" />
							</div>
						</form>
						<div id="charge-screen">
							<h3><?php _e('Process Pre-Authorizations', 'memberdeck'); ?></h3>
							<div id="charge-confirm"></div>
							<p><span class="alert"><?php _e('Warning:</span> This will process all pending authorizations related to the selected IDC product', 'memberdeck'); ?>.</p>
							<p><strong><?php _e('Customers will only be charged once', 'memberdeck'); ?>.</strong></p>
							<div id="projects">
								<select id="level-list" name="level-list">
								</select>
							</div>
							<div>
								<input type="submit" name="btnProcessPreauth" id="btnProcessPreauth" projid="btnProcessPreauth" value="<?php _e('Process Authorizations', 'memberdeck'); ?>" class="button" />
							</div>
						</div>
						<?php do_action('idc_gateway_settings_after'); ?>
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
					<h3 class="hndle"><span><?php _e('Gateway Installation Requirements', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<strong><?php _e('Offline Purchases', 'memberdeck'); ?>:</strong>
						<p><?php _e('Enable this to allow customers to complete checkout without entering payment information.', 'memberdeck'); ?></p>
						<strong><?php _e('Active Gateways', 'memberdeck'); ?>:</strong>
						<p><?php _e('Only one credit card gateway (Stripe, Balanced, First Data, Authorize.Net) may be active at one time.', 'memberdeck'); ?></p>
						<strong><?php _e('Currencies', 'memberdeck'); ?>:</strong>
						<p><?php _e('All Balanced Payments transactions will be processed in USD. Stripe transactions may be settled in the currencies listed.', 'memberdeck'); ?></p>
						<strong><?php _e('Recurring Payments', 'memberdeck'); ?>:</strong>
						<p><?php _e('Balanced does not yet support recurring payments. To use subscriptions, please enable Stripe, Paypal, Authorize.Net or Coinbase.', 'memberdeck'); ?></p>
						<strong><?php _e('Stripe Webhook URL', 'memberdeck'); ?>:</strong>
						<p><?php _e('In order to receive notifications of Stripe subscription payments, you\'ll need to create a production webhook URL with the following format', 'memberdeck'); ?>:</p>
						<p><strong>http://yourdomain.com/?memberdeck_notify=stripe</strong></p>
						<strong><?php _e('Dispute Notifications', 'memberdeck'); ?></strong>
						<p><?php _e('In order to properly handle Paypal dispute notifications, you must set your Paypal IPN URL to', 'memberdeck'); ?>:</p>
						<p><strong>http://yoursite.com/?memberdeck_notify=pp</strong></p>
						<p><strong><?php _e('Test Cards', 'memberdeck'); ?></strong></p>
						<p><a href="https://docs.balancedpayments.com/current/overview.html?language=bash#test-credit-card-numbers" target="_blank"><?php _e('Balanced Test Cards', 'memberdeck'); ?></a></p>
						<p><a href="https://stripe.com/docs/testing" target="_blank"><?php _e('Stripe Test Cards', 'memberdeck'); ?></a></p>
						<?php if (function_exists('is_id_pro') && is_id_pro()) { ?>
						<strong><?php _e('Stripe Connect Settings', 'memberdeck'); ?></strong>
						<p><?php _e('Your redirect URL must end in <em>?ipn_handler=sc_return</em>. For example, http://mydomain.com/?ipn_handler=sc_return', 'memberdeck'); ?></p>
						<?php } ?>
						<strong><?php _e('Coinbase Settings', 'memberdeck'); ?></strong>
						<p><?php _e('Coinbase does not offer a test mode, therefore all test transactions will require live transfer of Bitcoin. Coinbase to Coinbase transfers are free, and transactions are automatically converted to Bitcoin based on the currency setting saved here.', 'memberdeck'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Sidebar -->
</div>
</div>