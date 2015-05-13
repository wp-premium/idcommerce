jQuery(document).ready(function() {
	get_connected_users();
	get_assignment_list();
	button_style();
	jQuery("#button-style").change(function() {
		button_style();
	});
	function button_style() {
		var button = jQuery("#button-style").val();
		jQuery("#button-display a").removeClass();
		jQuery("#button-display a").addClass(button);
	}
	function get_connected_users() {
		jQuery('.sc_user').remove();
		jQuery.ajax({
			type: 'POST',
			url: md_ajaxurl,
			data: {action: 'idsc_get_users'},
			success: function(res) {
				//console.log(res);
				if (res) {
					var json = JSON.parse(res);
					jQuery.each(json, function() {
						jQuery('select[name="clear_creds"]').append('<option value="' + this.user_id + '" class="sc_user">' + this.display + '</option>');
					});
				}
			}
		});
	}
	function get_assignment_list() {
		jQuery('.assigned_user').remove();
		jQuery('.assigned_product').remove();
		jQuery.ajax({
			type: 'POST',
			url: md_ajaxurl,
			data: {action: 'idsc_assignment_list'},
			success: function(res) {
				//console.log(res);
				if (res) {
					var json = JSON.parse(res);
					var users = json.users;
					var products = json.products;
					jQuery.each(users, function() {
						//console.log(this);
						jQuery('select[name="assign_user"]').append('<option value="' + this.ID + '" class="assigned_user">' + this.ID + ' - ' + this.data.display_name + ', ' + this.data.user_email + '</option>');
					});
					jQuery.each(products, function() {
						//console.log(this);
						jQuery('select[name="assign_product"]').append('<option value="' + this.id + '" class="assigned_product">' + this.id + ' - ' + this.level_name + '</option>');
					});
				}
			}
		});
	}
	jQuery('input[name="sc_revoke"]').click(function(e) {
		e.preventDefault();
		var user = jQuery('select[name="clear_creds"]').val();
		jQuery.ajax({
			type: 'POST',
			url: md_ajaxurl,
			data: {action: 'idsc_revoke_creds', user_id: user},
			success: function(res) {
				//console.log(res);
				get_connected_users();
			}
		});
	});
	jQuery('input[name="sc_assign"]').click(function(e) {
		e.preventDefault();
		var user = jQuery('select[name="assign_user"]').val();
		var product = jQuery('select[name="assign_product"]').val();
		jQuery.ajax({
			type: 'POST',
			url: md_ajaxurl,
			data: {action: 'idc_ide_assign_user', user_id: user, Product: product},
			success: function(res) {
				//console.log(res);
				if (res) {
					var buttonText = jQuery('input[name="sc_assign"]').val();
					//console.log(buttonText);
					jQuery('input[name="sc_assign"]').val('Saved!');
					setTimeout(function() {
						jQuery('input[name="sc_assign"]').val(buttonText);
					}, 1000);
				}
				get_assignment_list();
			}
		});
	});
});