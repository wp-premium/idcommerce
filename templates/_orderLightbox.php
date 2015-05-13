<?php
$general = get_option('md_receipt_settings');
if (!is_array($general) && !empty($general)) {
	$general = unserialize($general);
}
?>
<div class="ignitiondeck idc_lightbox idc-social-sharing-box idc_lightbox_attach mfp-hide">
    <?php do_action('idc_order_lightbox_before', $last_order); ?>
	<div class="print-order">
			<table width="500" border="0" class="table" cellpadding="0" cellspacing="0">
                <tr>
                    <td><?php echo $general['coname']; ?></td>
                    <td class="right"><span class="order"><?php _e('Order', 'memberdeck'); ?></span> #100<?php echo $last_order->id; ?></td>
                </tr>
                <tr>
                    <td class="detailtitle"><?php echo $general['coname']; ?></td>
                    <td class="right dates"><?php echo date('m/d/Y', strtotime($last_order->order_date)); ?></td>
                </tr>
                <tr>
                    <td class="detailtitle"><?php _e('Company detail line 2:', 'memberdeck'); ?></td>
                	<td class="right dates"><?php echo home_url(); ?></td>
                </tr>
                <tr>
                    <td class="detailtitle"><?php echo $general['coemail']; ?></td>
                    <td class="right dates"></td>
                </tr>
            </table>
			<table width="100%" border="0" class="table">
                <tr>
                    <td class="customername"><?php echo $current_user->user_firstname.' '.$current_user->user_lastname; ?></td>
                    <td class="right dates"></td>
                </tr>
            </table>
    </div>
    <div class="idc-order-info">
			<table width="100%" border="0" class="table nonprint">
                <tr class="orderheader">
                    <td><h2><?php _e('Thank you!', 'memberdeck'); ?> </h2></td>
                    <td class="right orderinfo"><span class="order"><?php _e('Order', 'memberdeck'); ?></span> #100<?php echo $last_order->id; ?></td>
                </tr>
                <tr>
                    <td class="detailtitle"><?php _e('Your order details:', 'memberdeck'); ?></td>
                    <td class="right dates"><?php echo date('m/d/Y', strtotime($last_order->order_date)); ?></td>
                </tr>
            </table>
       <div class="order-details-grid">
			<table width="100%" border="0" class="table">
				<thead>
				<tr class="rowbg">
					<th class="left"><?php _e('Product', 'memberdeck'); ?></th>
                   	<th></th>
					<th class="right"><?php _e('Amount', 'memberdeck'); ?></th>
				</tr>
				</thead>
				<tbody>
				<tr class="details">
					<td class="title"><?php echo apply_filters('idc_order_level_title', (isset($order_level_key) ? $levels[$order_level_key]->level_name : $level->level_name), $last_order); ?><!--<span class="authorname">By Author</span>--></td>
                    <td></td>
					<td class="right"><?php echo apply_filters('idc_order_price', $price, $last_order->id); ?></td>
				</tr>
                <tr class="total_price">
					<td></td>
                    <td class="totalprice"><?php _e('TOTAL', 'memberdeck'); ?>:</td>
					<td class="totalprice">
						<span class="currency"><b><?php echo apply_filters('idc_order_price', $price, $last_order->id); ?></b></span>
                    </td>
				</tr>
				</tbody>
			</table>
        </div>
         <div class="print-details">
           <table width="100%" border="0" class="table">
              <tr class="print">
                <td class="email"><?php _e('Email confirmation will be sent to your Inbox soon', 'memberdeck'); ?>. </td>
                <td class="right"><a href="javascript:window.print()" class="receipt">Print receipt</a></td>
              </tr>
          </table>
		</div>
	</div>
    <div class="social-sharing-options-wrapper">
        <?php do_action('idc_order_sharing_before', $last_order, $levels); ?>
        <h2><?php _e('Tell your friends about it', 'memberdeck'); ?>!</h2>
        <div class="friendlink">
            <?php if (!empty($thumbnail)) { ?>
            <div class="thumb"><img src="<?php echo $thumbnail; ?>" /></div>
            <?php } ?>
            <div class="text"><?php _e('I just purchased', 'memberdeck'); ?> <?php echo apply_filters('idc_order_level_title', (isset($order_level_key) ? $levels[$order_level_key]->level_name : $level->level_name), $last_order); ?>.<br />
                <a href="<?php echo apply_filters('idc_order_level_url', home_url(), $last_order); ?>"><?php echo apply_filters('idc_order_level_url', home_url(), $last_order); ?></a>
            </div>
        </div>
        <div class="social-sharing-options-message">
        </div>
       <?php do_action('idc_order_sharing_after', $last_order, $levels); ?>
    </div>
    <?php do_action('idc_order_lightbox_after', $last_order); ?>
</div>