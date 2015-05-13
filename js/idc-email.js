jQuery(document).ready(function() {
	var selName = jQuery('select[name="template_select"]').children('option:selected').attr('name');
	jQuery(document).trigger('idc_template_select', selName);
	jQuery('.email_text').hide();
	jQuery('div.' + selName).show();
	jQuery('#restore_default').attr('name', 'restore_default_' + selName);
	jQuery('select[name="template_select"]').change(function() {
		selName = jQuery(this).children('option:selected').attr('name');
		jQuery(document).trigger('idc_template_select', selName);
		jQuery('#restore_default').attr('name', 'restore_default_' + selName);
		//console.log(selName);
		jQuery('.email_text').hide();
		jQuery('div.' + selName).show();
	});
});