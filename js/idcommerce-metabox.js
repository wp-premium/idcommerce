jQuery(document).ready(function() {
	var choice = jQuery("input[name='protect-choice']:checked").val();
	//console.log(choice);
	if (choice == 'no' || choice == undefined || choice == 0) {
		jQuery("#level-check").hide();
	}
	else {
		jQuery("#level-check").show();
	}
	jQuery("input[name='protect-choice']").click(function() {
		var choice = jQuery("input[name='protect-choice']:checked").val();
		console.log(choice);
		if (choice == 'no' || choice == undefined || choice == 0) {
			jQuery("#level-check").hide();
		}
		else {
			jQuery("#level-check").show();
		}
	});
	jQuery.ajax({
		url: md_ajaxurl,
		type: 'POST',
		data: {action: 'idmember_get_levels'},
		success: function(res) {
			//console.log(res);
			json = JSON.parse(res);
			jQuery.each(json, function() {
				jQuery("#md-level").append('<option value="' + this.id + '">' + this.level_name + '</option>');
			});
			var mdLevel = jQuery("#md-level").val();
			if (mdLevel > 0) {
				jQuery(".md-product").text(mdLevel);
				jQuery('#md-level').change(function() {
					mdLevel = jQuery("#md-level").val();
					jQuery(".md-product").text(mdLevel);
				});
			}
		}
	});
});