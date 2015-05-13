<?php
global $permalink_structure;
if (empty($permalink_structure)) {
	$prefix = '&amp;';
}
else {
	$prefix = '?';
}
if (is_user_logged_in() && !isset($current_user)) {
	global $current_user;
	get_currentuserinfo();
}
if (isset($current_user)) {
	$orders = ID_Member_Order::get_orders_by_user($current_user->ID);
	$orders = array_reverse($orders);
}
$durl = md_get_durl();
?>
<?php if (!class_exists('Helix')) { ?>
<ul class="dashboardmenu">
	<li><a href="<?php echo $durl; ?>"><?php _e('Dashboard', 'memberdeck'); ?></a></li>
	<li <?php echo (isset($_GET['edit-profile']) ? 'class="active"' : ''); ?>><a href="<?php echo (isset($current_user) ? the_permalink().$prefix.'edit-profile='.$current_user->ID : ''); ?>"><?php echo (isset($current_user) ? __('Account', 'memberdeck') : ''); ?></a></li>
	<!-- <li class="help"><a href="#"><i class="icon-question-sign"></i></a></li> -->
	<?php if (!empty($orders)) { ?>
	<li <?php echo (isset($_GET['idc_orders']) ? 'class="active"' : ''); ?>><a href="<?php echo the_permalink().$prefix.'idc_orders=1'; ?>"><?php _e('Orders', 'memberdeck'); ?></a></li>
	<?php } ?>
	<?php do_action('md_profile_extratabs'); ?>
</ul>
<?php } ?>