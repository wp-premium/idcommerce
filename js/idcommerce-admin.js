jQuery(document).ready(function() {
	jQuery("#level-delete").hide();
	jQuery("#credit-delete").hide();
	jQuery("#download-delete").hide();
	if (jQuery('#edit-level').val() == "Choose Level") {
		jQuery('.list-shortcode').hide();
	}
	var bodyClass = jQuery('body').attr('class');
	if (bodyClass.indexOf('idc') !== -1) {
		jQuery('body').addClass('md-admin');
	}
	jQuery('.md_help_link').click(function() {
		var service = jQuery(this).attr('id');
		var service_id = '#' + service + '-help';
		jQuery(service_id).slideToggle('fast', function() {
			// Animation complete.
		});
	});
	// Events for Enable Credits button
	if (jQuery('#enable_credits').length > 0) {
		if (jQuery('#enable_credits').is(":checked")) {
			jQuery('#credit-value').parent('div').show();
			jQuery('#credit-settings-help').parent('div').show();
		} else {
			jQuery('#credit-value').parent('div').hide();
			jQuery('#credit-settings-help').parent('div').hide();
		}
	}
	jQuery('#enable_credits').change(function(e) {
		if (jQuery('#enable_credits').is(":checked")) {
			jQuery('#credit-value').parent('div').show();
			jQuery('#credit-settings-help').parent('div').show();
		} else {
			jQuery('#credit-value').parent('div').hide();
			jQuery('#credit-settings-help').parent('div').hide();
		}
	});
	
	var showTerms = jQuery('input[name="show_terms"]').attr('checked');
	jQuery('input[name="show_terms"]').change(function() {
		jQuery('input[name="show_terms"]').attr('checked');
		jQuery('select[name="terms_page"], select[name="privacy_page"]').parent('div').toggle();
	});
	jQuery('select[name="export_product_choice"]').change(function() {
		allowExport();
	});
	// Checks for default product and click handlers
	if (jQuery('#enable_default_product').length > 0) {
		if (jQuery('#enable_default_product').is(":checked")) {
			jQuery('#default_product').parent('div').show();
		} else {
			jQuery('#default_product').parent('div').hide();
		}
	}
	jQuery('#enable_default_product').change(function(e) {
		if (jQuery(this).is(":checked")) {
			jQuery('#default_product').parent('div').show();
		} else {
			jQuery('#default_product').parent('div').hide();
		}
	});

	/*jQuery('input[name="export_customers"]').click(function(e) {
		e.preventDefault();
		export_customers();
	});*/
	if (jQuery('select[name="product-type"] option').length <= 1) {
		jQuery('select[name="product-type"]').parent('.form-input').hide();
	}
	function get_levels() {
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'idmember_get_levels'},
			success: function(res) {
				//console.log(res);
				levels = JSON.parse(res);
				//console.log(levels);
				jQuery.each(levels, function() {
					// options for credit dropdown
					jQuery("#credit-assign").append(jQuery("<option/>", {
						value: this.id,
						text: this.level_name
					}));
					// options for level dropdown
					jQuery("#edit-level").append(jQuery("<option/>", {
						value: this.id,
						text: this.level_name
					}));
					jQuery("#default_product").append(jQuery("<option>", {
						value: this.id,
						text: this.level_name,
						selected: ((jQuery("#default_product").data('selected') == this.id) ? true : false)
					}));
					// options for level dropdown
					jQuery("#occ_level").append(jQuery("<option/>", {
						value: this.id,
						text: this.level_name
					}));
					// checkboxes for dasbhoard settings
					jQuery("#assign-checkbox").append(jQuery("<input/>", {
						type: 'checkbox',
						name: 'lassign[]',
						class: 'lassign',
						id: 'assign-' + this.id,
						value: this.id
					}));
					jQuery("#assign-checkbox").append(jQuery("<label/>", {
						text: this.level_name,
						for: 'assign-' + this.id
					}));
					jQuery("#assign-checkbox").append("<br/>");
					jQuery("select[name='export_product_choice']").append(jQuery("<option/>", {
						value: this.id,
						text: this.level_name
					}));
					// checkboxes for creator permissions
					jQuery("#allowed-creator-levels").append(jQuery("<input/>", {
						type: 'checkbox',
						name: 'cassign[]',
						class: 'cassign',
						id: 'cperm-' + this.id,
						value: this.id
					}));
					jQuery("#allowed-creator-levels").append(jQuery("<label/>", {
						text: this.level_name,
						for: 'cperm-' + this.id
					}));
					jQuery("#allowed-creator-levels").append("<br/>");
					jQuery("select[name='export_product_choice']").append(jQuery("<option/>", {
						value: this.id,
						text: this.level_name
					}));
				});
				jQuery("#edit-level").change(function() {
					var leveledit = parseInt(jQuery("#edit-level").val());
					if (jQuery(this).val() == idc_localization_strings.choose_product) {
						jQuery('.list-shortcode').hide();
						jQuery('input[name="create_page"]').removeAttr('disabled').removeAttr('checked');
					}
					else {
						jQuery('.list-shortcode').show();
						jQuery('input[name="create_page"]').attr('disabled', 'disabled');
					}
					if (leveledit) {
						//console.log(leveledit);
						jQuery("#product-type").val(levels[leveledit].product_type)
						jQuery("#level-name").val(levels[leveledit].level_name);
						jQuery("#level-price").val(levels[leveledit].level_price);
						jQuery("#credit-value").val(levels[leveledit].credit_value);
						jQuery("#txn-type").val(levels[leveledit].txn_type);
						jQuery("#level-type").val(levels[leveledit].level_type);
						if (levels[leveledit].recurring_type.length > 0) {
							jQuery("#recurring-type").val(levels[leveledit].recurring_type);
						}
						else {
							jQuery('#recurring-type').val('monthly');
						}
						var limit_term = levels[leveledit].limit_term;
						if (limit_term == 1) {
							jQuery('#limit_term').attr('checked', 'checked');
						}
						else {
							jQuery('#limit_term').removeAttr('checked');
						}
						jQuery('#term_length').val(levels[leveledit].term_length);
						jQuery("#plan").val(levels[leveledit].plan);
						jQuery('#license-count').val(levels[leveledit].license_count);
						var enable_renewals = levels[leveledit].enable_renewals;
						if (enable_renewals == 1) {
							jQuery('#enable_renewals').attr('checked', 'checked');
							jQuery('#renewal_price').parents('.form-input').show();
						}
						else {
							jQuery('#enable_renewals').removeAttr('checked');
							jQuery('#renewal_price').parents('.form-input').hide();
						}
						var enable_multiples = levels[leveledit].enable_multiples;
						if (enable_multiples == 1) {
							jQuery('#enable_multiples').attr('checked', 'checked');
						}
						else {
							jQuery('#enable_multiples').removeAttr('checked');
						}
						jQuery('#renewal_price').val(levels[leveledit].renewal_price);
						jQuery("#level-submit").val('Update');
						jQuery("#level-delete").show();
						jQuery(".list-shortcode").text(idc_localization_strings.purchase_form_shortcode + ': [memberdeck_checkout product="' + leveledit + '"]');
					}
					else {
						jQuery('#product-type').val('purchase');
						jQuery("#level-name").val('');
						jQuery("#level-price").val('');
						jQuery("#credit-value").val(0);
						jQuery("#txn-type").val('capture');
						jQuery("#plan").val('');
						jQuery('#limit_term').removeAttr('checked');
						jQuery('#term_length').val('');
						jQuery('#license-count').val('');
						jQuery('#enable_renewals').removeAttr('checked');
						jQuery('#renewal_price').val('');
						jQuery('#enable_multiples').removeAttr('checked');
						jQuery("#level-submit").val('Create');
						jQuery("#level-delete").hide();
					}
					var type = jQuery("#level-type").val();
					if (type == 'recurring') {
						jQuery("#recurring-input").show();
					}
					else {
						jQuery("#recurring-input").hide();
					}
				});
				var type = jQuery("#level-type").val();
				jQuery("#level-type").change(function() {
					type = jQuery("#level-type").val();
					if (type == 'recurring') {
						jQuery("#recurring-input").show();
					}
					else {
						jQuery("#recurring-input").hide();
					}
				});
				jQuery('#enable_renewals').change(function() {
					if (jQuery('#enable_renewals').attr('checked') == 'checked') {
						jQuery('#renewal_price').parents('.form-input').show();
					}
					else {
						jQuery('#renewal_price').parents('.form-input').hide();
					}
				});
				//Now that we know all level data is loaded, move onto creator permissions for levels
				get_cperms();
			}
		});
	}
	
	//Get level-based creator permissions and check the boxes for them
	function get_cperms(){
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'idmember_get_cperms'},
			success: function(res) {
				//console.log(res);
				var cperms = JSON.parse(res);
				jQuery.each(cperms, function(k,v) {
					//console.log(v);
					//console.log(jQuery('.cassign[value="' + v + '"]').attr('checked', 'checked'));
				});
				jQuery('#creator-permissions').change(function() {
					var temp = parseInt(jQuery("#creator-permissions").val());
					if (temp == '2') {
						jQuery("#allowed-creator-levels").show();
					} 
					else {
						jQuery("#allowed-creator-levels").hide();
					}	
				});
			}
		});
	}
	
	
	
	
	
	function get_downloads() {
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'idmember_get_downloads'},
			success: function(res) {
				//console.log(res);
				var downloads = JSON.parse(res);
				//console.log(downloads);
				jQuery.each(downloads, function() {
					// options for level dropdown
					jQuery("#edit-download").append(jQuery("<option/>", {
						value: this.id,
						text: this.download_name
					}));
				});
				var occEnabled = jQuery(this).attr('checked');
				jQuery('input[name="enable_occ"]').removeAttr('checked');
				mdid_project_list();
				jQuery("#edit-download").change(function() {
					var downloadedit = parseInt(jQuery("#edit-download").val());
					jQuery('.lassign').removeAttr('checked');
					if (downloadedit) {
						jQuery("#download-name").val(downloads[downloadedit].download_name);
						jQuery("#download-version").val(downloads[downloadedit].version);
						if (downloads[downloadedit].enable_occ == '1') {
							jQuery('input[name="enable_occ"]').attr('checked', 'checked');
						}
						else {
							jQuery('input[name="enable_occ"]').removeAttr('checked');
						}
						if (downloads[downloadedit].hidden == '1') {
							jQuery('input[name="hidden"]').attr('checked', 'checked');
						}
						else {
							jQuery('input[name="hidden"]').removeAttr('checked');
						}
						if (downloads[downloadedit].enable_s3 == '1') {
							jQuery('input[name="enable_s3"]').attr('checked', 'checked');
						}
						else {
							jQuery('input[name="enable_s3"]').removeAttr('checked');
						}
						jQuery('#occ_level').val(downloads[downloadedit].occ_level);
						jQuery('#id_project').val(downloads[downloadedit].id_project);
						jQuery("#dash-position").val(downloads[downloadedit].position);
						jQuery('#licensed').val(downloads[downloadedit].licensed);
						var levels = downloads[downloadedit].levels;
						jQuery.each(levels, function(k,v) {
							//console.log(v);
							jQuery('.lassign[value="' + v + '"]').attr('checked', 'checked');
						});
						jQuery("#download-link").val(downloads[downloadedit].download_link);
						jQuery("#info-link").val(downloads[downloadedit].info_link);
						jQuery("#doc-link").val(downloads[downloadedit].doc_link);
						jQuery("#image-link").val(downloads[downloadedit].image_link);
						jQuery("#button-text").val(downloads[downloadedit].button_text);
						jQuery("#download-submit").val('Update');
						jQuery("#download-delete").show();
					}
					else {
						jQuery("#download-name").val('');
						jQuery("#download-version").val('');
						jQuery('input[name="enable_s3"]').removeAttr('checked');
						jQuery('input[name="hidden"]').removeAttr('checked', 'checked');
						jQuery('input[name="enable_occ"]').removeAttr('checked');
						jQuery('#occ_level').val(0);
						jQuery('#id_project').val(0);
						jQuery("#dash-position").val('a');
						jQuery('#licensed').val('0');
						//jQuery('.lassign').removeAttr('checked');
						jQuery("#download-link").val('');
						jQuery("#info-link").val('');
						jQuery("#doc-link").val('');
						jQuery("#image-link").val('');
						jQuery("#button-text").val('');
						jQuery("#download-submit").val('Create');
						jQuery("#download-delete").hide();
					}
					toggle_occ_dash();
				});
				jQuery('input[name="enable_occ"]').change(function() {
					toggle_occ_dash();
				});
			}
		});
	}
	
	var creatorPerms = jQuery('select[name="creator_permissions"] option:selected').val();
	toggleCreatorPerms(creatorPerms);
	jQuery('select[name="creator_permissions"]').change(function() {
		creatorPerms = jQuery('select[name="creator_permissions"] option:selected').val();
		toggleCreatorPerms(creatorPerms);
	});
	
	function toggleCreatorPerms(creatorPerms) {
		if (creatorPerms == '2') {
			jQuery('#allowed-creator-levels').show();
		}
		else {
			jQuery('#allowed-creator-levels').hide();
		}
	}
	function allowExport() {
		var productExport = jQuery('select[name="export_product_choice"]').val();
		if (productExport > 0) {
			jQuery('input[name="export_customers"]').removeAttr('disabled');
		}
		else {
			jQuery('input[name="export_customers"]').attr('disabled', 'disabled');
		}
	}
	function export_customers() {
		jQuery('input[name="export_customers"]').attr('disabled', 'disabled');
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'md_export_customers'},
			success: function(res) {
				console.log(res);
				var url = res;
				if (url !== undefined) {
					//jQuery('input[name="export_customers"]').after('&nbsp;<a href="' + url + '"><button class="button">Download File</button></a>');
					jQuery('input[name="export_customers"]').removeAttr('disabled');
					//window.location.href = url;
				}
			}
		});
	}
	function toggle_occ_dash() {
		occEnabled = jQuery('input[name="enable_occ"]').attr('checked');
		//console.log(occEnabled);
		if (occEnabled == 'checked') {
			jQuery('#occ_level').parents('.form-input').show();
			if (jQuery('#id_project').length > 0) {
				jQuery('#id_project').parents('.form-input').show();
			}
			else {
				jQuery('#id_project').parents('.form-input').hide();
			}
		}
		else {
			jQuery('#occ_level').parents('.form-input').hide();
			jQuery('#id_project').parents('.form-input').hide();
		}
	}
	function mdid_project_list() {
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'mdid_project_list'},
			success: function(res) {
				if (res) {
					json = JSON.parse(res);
					if (json) {
						//console.log(json);
						jQuery.each(json, function() {
							if (jQuery('#id_project option[value="' + this.id + '"]').length <= 0) {
								jQuery('#id_project').append(jQuery('<option/>', {
									value: this.id,
									text: this.product_name
								}));
							}
						});
						
					}
				}
			}
		});
	}
	get_levels();
	get_downloads();
	allowExport();
	var showLove = jQuery('#powered_by').attr('checked');
	var parentDiv = jQuery('#aff_link').parent('p').parent('div');
	//console.log(showLove);
	if (showLove == 'checked') {
		jQuery(parentDiv).show();
	}
	jQuery('#powered_by').change(function() {
		showLove = jQuery('#powered_by').attr('checked');
		if (showLove == 'checked') {
			jQuery(parentDiv).show();
		}
		else {
			jQuery(parentDiv).hide();
		}
	});

	jQuery.ajax({
		url: md_ajaxurl,
		type: 'POST',
		data: {action: 'idmember_get_credits'},
		success: function(res) {
			//console.log(res);
			credits = JSON.parse(res);
			jQuery.each(credits, function() {
				jQuery("#edit-credit").append(jQuery("<option/>", {
					value: this.id,
					text: this.credit_name
				}));
			});
			jQuery("#edit-credit").change(function() {
				var creditedit = parseInt(jQuery("#edit-credit").val());
				jQuery("#credit-name").val(credits[creditedit].credit_name);
				jQuery("#credit-price").val(credits[creditedit].credit_price);
				jQuery("#credit-count").val(credits[creditedit].credit_count);
				jQuery("#credit-assign").val(credits[creditedit].credit_level);
				jQuery("#credit-submit").val('Update');
				jQuery("#credit-delete").show();
			});
		}
	});
	jQuery('#memberdeck-users td.name-title').click(function(e) {
		e.preventDefault();
		var parent = jQuery(this).parent('tr').attr('id');
		if (parent) {
			id = parent.replace('user-', '');
			jQuery("#user-list, .search-box").hide();
			jQuery("#user-profile").show();
			jQuery.ajax({
				async: false,
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_get_profile', ID: id},
				success: function(res) {
					//console.log(res);
					json = JSON.parse(res);
					if (json.shipping_info) {
						jQuery.each(json.shipping_info, function(k, v) {
							//console.log('k: ' + k);
							//console.log('v: ' + v);
							jQuery('#user-profile .' + k).val(v);
						});
					}
					if (json.usermeta) {
						jQuery.each(json.usermeta, function(k, v) {
							//console.log('k: ' + k);
							//console.log('v: ' + v);
							jQuery('#user-profile .' + k).val(v);
						});
					}
					if (json.userdata.data) {
						jQuery.each(json.userdata.data, function(k, v) {
							//console.log('k: ' + k);
							//console.log('v: ' + v);
							jQuery('#user-profile .' + k).val(v);
						});
					}
					jQuery(document).trigger('user_profile_edit', json);
					jQuery('#confirm-edit-profile').click(function(e) {
						e.preventDefault();
						var new_userdata = {};
						var inputs = jQuery('#user-profile input');
						var error = false;
						//console.log(inputs);
						jQuery.each(jQuery(inputs), function(k,v) {
							//console.log(jQuery(this).attr('name'));
							//console.log('k: ' + k + ' = v: ' + v);
							var inputName = jQuery(this).attr('name');
							if (inputName == 'display_name' || inputName == 'user_email') {
								if (jQuery(this).val().length <= 0) {
									error = true;
								}
							}
							if (jQuery(this).attr('type') == 'checkbox') {
								if (jQuery(this).attr('checked') == 'checked') {
									new_userdata[inputName] = jQuery(this).val();
								}
							}
							else {
								new_userdata[inputName] = jQuery(this).val();
							}
						});
						new_userdata['id'] = id;
						//console.log(new_userdata);
						if (error) {
							jQuery('p.error').remove();
							jQuery('#user-profile').prepend('<p class="error">Error, missing or empty fields.</p>');
						}
						else {
							jQuery.ajax({
								async: false,
								url: md_ajaxurl,
								type: 'POST',
								data: {action: 'idmember_edit_profile', Userdata: new_userdata},
								success: function(res) {	
									//console.log(res);
									location.reload();
								}
							});
						}
					});
				}
			});
			/* Edit Subscriptions js */
			jQuery.ajax({
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'admin_edit_subscription', user_id: id},
				success: function(res) {
					//console.log(res);
					if (res) {
						json = JSON.parse(res);
						if (!jQuery.isEmptyObject(json)) {
							jQuery('.postbox').show();
							//console.log(json);
							jQuery.each(json, function() {
								//console.log(this);
								jQuery('select[name="sub_list"]').append('<option value="' + this.id + '">' + this.plan_id + '</option>');
							});
						}
					}
				}
			});
			jQuery('select[name="sub_list"]').change(function() {
				var planID = jQuery(this).children('option:selected').val();
				if (planID !== '0') {
					var plan = jQuery(this).children('option:selected').text();
					//console.log(planID);
					jQuery('button[name="cancel_sub"]').removeAttr('disabled').show();
				}
				else {
					jQuery('button[name="cancel_sub"]').attr('disabled', 'disabled').hide();
				}
			});
			jQuery('button[name="cancel_sub"]').click(function(e) {
				e.preventDefault();
				jQuery('.sub_response').text('').removeClass().addClass('sub_response');
				var planID = jQuery('select[name="sub_list"]').children('option:selected').val();
				var plan = jQuery('select[name="sub_list"]').children('option:selected').text();
				jQuery.ajax({
					url: md_ajaxurl,
					type: 'POST',
					data: {action: 'idc_cancel_sub', plan_id: planID, plan: plan, user_id: id},
					success: function(res) {
						//console.log(res);
						if (res) {
							var json = JSON.parse(res);
							if (json.status == 'success') {
								jQuery('select[name="sub_list"] option:selected').remove();
								if (jQuery('select[name="sub_list"] option').size()  == 1) {
									jQuery('button[name="cancel_sub"]').attr('disabled', 'disabled').hide();
								}
							}
							else {

							}
							jQuery('.sub_response').text(json.message).addClass(json.status);
						}
					}
				});
			});
		}
		jQuery("#cancel-edit-profile").click(function(e) {
			jQuery(".form-input").html('');
			jQuery("#user-list, .search-box").show();
			jQuery("#user-profile").hide();
			e.preventDefault();
		});
	});
	jQuery("#memberdeck-users td.current-levels").click(function(e) {
		e.preventDefault();
		var parent = jQuery(this).parent('tr').attr('id');
		if (parent) {
			id = parent.replace('user-', '');
			jQuery("#user-list, .search-box").hide();
			jQuery("#edit-user").show();

			jQuery.ajax({
				async: false,
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_get_levels'},
				success: function(res) {
					//console.log(res);
					levels = JSON.parse(res);
					//console.log(levels);
					jQuery.each(levels, function() {
						jQuery(".form-input").append(jQuery("<tr class='level" + this.id + "'><th class='check-column'><input type='checkbox' value='" + this.id + "'/></th><td><label>" + this.level_name + "</label></td></tr>"));
					});
				}
			});
			jQuery.ajax({
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_edit_user', ID: id},
				success: function(res) {
					//console.log(res);
					var count = jQuery(".form-input input[type='checkbox']").size() - 1;
					json = JSON.parse(res);
					console.log(json);
					if (Object.keys(json.levels).length > 0) {
						for (i = 0; i <= count; i++) {
							//jQuery("input[value='" + json.levels[i] + "']").attr('checked', 'checked');
							jQuery('.form-input tr').eq(i).append('<td/><td></td>');
						}
						jQuery.each(json.levels, function(k,v) {
							jQuery("input[value='" + v + "']").attr('checked', 'checked');
						});
					}
					else {
						for (i = 0; i <= count; i++) {
							jQuery('.form-input tr').eq(i).append('<td/><td>none</td>');
						}
					}
					jQuery.each(json.levels, function(k, v) {
						var lid = v;
						var aid = k;
						if (json.lasts[k]) {
							var edate = json.lasts[k]['e_date'];
							var odate = json.lasts[k]['order_date'];
							var oid = json.lasts[k]['id'];

							if (!edate || edate == undefined || edate == '') {
								edate = 'lifetime';
							}
							else if (edate.indexOf('0000-00-00 00:00:00') !== -1) {
								edate = 'lifetime';
							}
							jQuery(".form-input tr.level" + v).children('td:last-child').prev().text(odate);
							jQuery(".form-input tr.level" + v).children('td:last-child').html('<a data-id="' + oid + '" class="edit-date" href="#">' + edate + '</a>');
						}
					});
					jQuery('.form-input').on('click', '.edit-date', function(e) {
						e.preventDefault();
						var clone = this;
						oID = jQuery(this).data('id');
						jQuery(this).replaceWith('<span id="edit-fields"><input type="text" name="edit-date" value="yyyy-mm-dd"/><p class="edit-options"><span class="trash"><a href="#" class="edit-cancel delete">cancel</a></span>&nbsp;|&nbsp;<a href="#" class="lifetime">non-expiring</a></span></p>');
						jQuery('input[name="edit-date"]').datepicker({
							defaultDate: '0000-00-00',
							dateFormat : 'yy-mm-dd',
							onClose: function() {
								var newDate = jQuery(this).val();
								jQuery('span.trash').remove();
								if (newDate !== '' || newDate !== 'yyyy-mm-dd') {
									jQuery(this).replaceWith('<a data-id="' + oID + '" class="edit-date date-edited" href="#">' + newDate + '</a>');
								}
								else {
									jQuery(this).replaceWith('<a data-id="' + oID + '" class="edit-date date-edited" href="#">0000-00-00</a>');
								}
							}
						});
						jQuery(".edit-cancel").click(function(e) {
							e.preventDefault();
							jQuery('#edit-fields').replaceWith(clone);
						});
						jQuery(".lifetime").click(function(e) {
							e.preventDefault();
							jQuery('p.edit-options').remove();
							jQuery('input[name="edit-date"]').replaceWith('<a data-id="' + id + '" class="edit-date date-edited" href="#">0000-00-00</a>');
						});
					});
				}
			});

			jQuery("#confirm-edit").click(function(e) {
				e.preventDefault();
				var new_levels = [];
				jQuery.each(levels, function() {
					new_levels.push({'level': this.id, 'value': jQuery(".form-input input[value='" + this.id + "']").attr('checked')});
				});
				var new_dates = [];
				jQuery.each(jQuery('.date-edited'), function() {
					var dateID = jQuery(this).data('id');
					var date = jQuery(this).text();
					new_dates.push({'id': dateID, 'date': date});
				});
				//console.log(new_levels);
				jQuery.ajax({
					url: md_ajaxurl,
					type: 'POST',
					data: {action: 'idmember_save_user', ID: id, Levels: new_levels, Dates: new_dates},
					success: function(res) {
						console.log(res);
						if (!res) {
							jQuery(".form-input").html('');
							jQuery("#user-list, .search-box").show();
							jQuery("#edit-user").hide();
							window.location = "?page=idc-users";
						}
					}
				});
			});
		}
		jQuery("#cancel-edit").click(function(e) {
			e.preventDefault();
			//console.log('this');
			jQuery(".form-input").html('');
			jQuery("#user-list").show();
			jQuery("#edit-user").hide();
		});
	});
	jQuery('#memberdeck-users td.current-credits').click(function(e) {
		e.preventDefault();
		var parent = jQuery(this).parent('tr').attr('id');
		if (parent) {
			id = parent.replace('user-', '');
			jQuery("#user-list, .search-box").hide();
			jQuery("#user-credits").show();

			jQuery.ajax({
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_credit_total', ID: id},
				success: function(res) {
					console.log(res);
					if (res == '') {
						res = 0;
					}
					jQuery('input[name="current-credits"]').val(res);
				}
			});
			jQuery('#confirm-credits').click(function(e) {
				var credits = jQuery('input[name="current-credits"]').val();
				e.preventDefault();
				jQuery.ajax({
					url: md_ajaxurl,
					type: 'POST',
					data: {action: 'idmember_save_credits', ID: id, Credits: credits},
					success: function(res) {
						//console.log(res);
						jQuery(".form-input").html('');
						jQuery("#user-list").show();
						jQuery("#user-credits").hide();
						window.location = "?page=idc-users";
					}
				});
			});
		}
		jQuery("#cancel-credits").click(function(e) {
			e.preventDefault();
			//console.log('this');
			jQuery(".form-input").html('');
			jQuery("#user-list").show();
			jQuery("#user-credits").hide();
		});
	});
	jQuery('#assign-checkbox .select').click(function(e) {
		e.preventDefault();
		jQuery('.lassign').attr('checked', 'checked');
	});
	jQuery('#assign-checkbox .clear').click(function(e) {
		e.preventDefault();
		jQuery('.lassign').removeAttr('checked');
	});
	// Gateway js
	jQuery.getJSON(md_currencies, function(data) {
		jQuery.each(data.currency, function() {
			jQuery('#pp-currency').append('<option value="' + this.code + '" data-symbol="' + this.symbol + '">' + this.code + '</option>');
			var selCurrency = jQuery('#pp-currency').data('selected');
			jQuery('#pp-currency').val(selCurrency);
			// If the selection for global currency exists in the page
			if (jQuery('#global-currency').length > 0) {
				jQuery('#global-currency').append('<option value="' + this.code + '" data-symbol="' + this.symbol + '">' + this.code + '</option>');
			}
			// if PayPal Adaptive exists in the page
			if (jQuery('#ppada_currency').length > 0) {
				jQuery('#ppada_currency').append('<option value="' + this.code + '" data-symbol="' + this.symbol + '">' + this.code + '</option>');
			}
		});
		// If the selection for global currency exists in the page, append virtual currency to it also
		if (jQuery('#global-currency').length > 0) {
			// Appending Coinbase currency Bitcoins
			jQuery('#global-currency').append('<option value="BTC" data-symbol="BTC">BTC</option>');
			jQuery('#global-currency').append('<option value="credits" data-symbol="credits">'+ idc_localization_strings.virtual_currency + '</option>');
			// Selecting the option saved
			var selGlobalCurrency = jQuery('#global-currency').data('selected');
			jQuery('#global-currency').val(selGlobalCurrency);
			// Adding an option to use idcf settings for currency
			//jQuery('#global-currency').prepend('<option value="idcf">'+ idc_localization_strings.use_idcf_settings +'</option>');
		}
		jQuery('#pp-currency').change(function() {
			var selSymbol = jQuery(this).find(':selected').data('symbol');
			jQuery('input[name="pp-symbol"]').val(selSymbol);
		});
	});

	/* This code ensures that only one credit card processing gateway can be active at once */

	if (jQuery('#es').attr('checked') == 'checked') {
		jQuery('#eb').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#ebm').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#efd').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#eauthnet').removeAttr('checked').attr('disabled', 'disabled');
	}
	else if (jQuery('#eb').attr('checked') == 'checked') {
		jQuery('#es').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#esc').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#efd').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#eauthnet').removeAttr('checked').attr('disabled', 'disabled');
	}
	else if (jQuery('#efd').attr('checked') == 'checked') {
		jQuery('#es').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#esc').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#eb').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#ebm').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#eauthnet').removeAttr('checked').attr('disabled', 'disabled');
	}
	else if (jQuery('#eauthnet').attr('checked') == 'checked') {
		jQuery('#es').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#eb').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#ebm').removeAttr('checked').attr('disabled', 'disabled');
		jQuery('#efd').removeAttr('checked').attr('disabled', 'disabled');
	}

	jQuery('#es').change(function() {
		if (jQuery(this).attr('checked') == 'checked') {
			jQuery('#eb').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#ebm').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#efd').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#eauthnet').removeAttr('checked').attr('disabled', 'disabled');
		}
		else {
			jQuery('#eb').removeAttr('disabled');
			jQuery('#ebm').removeAttr('disabled');
			jQuery('#efd').removeAttr('disabled');
			jQuery('#eauthnet').removeAttr('disabled');
		}
	});

	jQuery('#eb').change(function() {
		if (jQuery(this).attr('checked') == 'checked') {
			jQuery('#es').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#esc').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#efd').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#eauthnet').removeAttr('checked').attr('disabled', 'disabled');
		}
		else {
			jQuery('#es').removeAttr('disabled');
			jQuery('#esc').removeAttr('disabled');
			jQuery('#efd').removeAttr('disabled');
			jQuery('#eauthnet').removeAttr('disabled');
		}
	});

	jQuery('#efd').change(function() {
		if (jQuery(this).attr('checked') == 'checked') {
			jQuery('#es').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#esc').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#eb').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#ebm').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#eauthnet').removeAttr('checked').attr('disabled', 'disabled');
		}
		else {
			jQuery('#es').removeAttr('disabled');
			jQuery('#esc').removeAttr('disabled');
			jQuery('#eb').removeAttr('disabled');
			jQuery('#ebm').removeAttr('disabled');
			jQuery('#eauthnet').removeAttr('disabled');
		}
	});
	
	jQuery('#eauthnet').change(function(e) {
		if (jQuery(this).attr('checked') == 'checked') {
			jQuery('#es').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#esc').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#efd').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#ebm').removeAttr('checked').attr('disabled', 'disabled');
			jQuery('#eb').removeAttr('checked').attr('disabled', 'disabled');
		}
		else {
			jQuery('#es').removeAttr('disabled');
			jQuery('#esc').removeAttr('disabled');
			jQuery('#efd').removeAttr('disabled');
			jQuery('#ebm').removeAttr('disabled');
			jQuery('#eb').removeAttr('disabled');
		}
	});
	
	// PayPal checks for selecting one paypal payment method at a time
	jQuery('#epp').click(function(e) {
		if (jQuery(this).is(":checked")) {
			jQuery('#eppadap').prop('checked', false);
		}
	});
	jQuery('#eppadap').click(function(e) {
		if (jQuery(this).is(":checked")) {
			jQuery('#epp').prop('checked', false);
		}
	});
	// If it's gateway settings page
	if (jQuery('.enable_paypal').length > 0) {
		var ppMethod = jQuery('.enable_paypal:checked').attr('name');
		togglePP(ppMethod);
		toggleLiveTest(ppMethod);
		jQuery('.enable_paypal').change(function(e) {
			ppMethod = jQuery('.enable_paypal:checked').attr('name');
			togglePP(ppMethod);
			toggleLiveTest(ppMethod);
		});
		jQuery('input[name="test"]').change(function() {
			ppMethod = jQuery('.enable_paypal:checked').attr('name');
			toggleLiveTest(ppMethod);
			togglePP(ppMethod);
		});
		
		jQuery('#ppadap_max_preauth_period').keyup(function(e) {
			if (/[0-9]/.test(jQuery('#ppadap_max_preauth_period').val()) === false) {
				jQuery(this).val('');
			} else if (jQuery(this).val() > 364) {
				jQuery(this).val('364');
			}
		});
	}

	function togglePP(ppMethod) {
		if (ppMethod == undefined) {
			jQuery('.enable_paypal').removeAttr('disabled');
		}
		else {
			jQuery('.enable_paypal:not(:checked)').attr('disabled', 'disabled');
		}
	}

	function toggleLiveTest(ppMethod) {
		jQuery('.test-field').hide();
		jQuery('.live-field').hide();
		if (jQuery('input[name="test"]').is(':checked')) {
			if (ppMethod == 'epp' || ppMethod == undefined) {
				jQuery('.pp-standard-field.test-field').show();
			}
			else if (ppMethod == 'eppadap') {
				jQuery('.pp-adaptive-field.test-field').show();
			}
		}
		else {
			if (ppMethod == 'epp' || ppMethod == undefined) {
				jQuery('.pp-standard-field.live-field').show();
			}
			else if (ppMethod == 'eppadap') {
				jQuery('.pp-adaptive-field.live-field').show();
			}
		}
	}

	/* This code ensures that only one smtp client can be active at once */

	jQuery('#enable_sendgrid').change(function() {
		if (jQuery(this).attr('checked') == 'checked') {
			jQuery('#enable_mandrill').removeAttr('checked').attr('disabled', 'disabled');
		}
		else {
			jQuery('#enable_mandrill').removeAttr('disabled');
		}
	});

	jQuery('#enable_mandrill').change(function() {
		if (jQuery(this).attr('checked') == 'checked') {
			jQuery('#enable_sendgrid').removeAttr('checked').attr('disabled', 'disabled');
		}
		else {
			jQuery('#enable_sendgrid').removeAttr('disabled');
		}
	});

	jQuery.ajax({
		url: md_ajaxurl,
		type: 'POST',
		data: {action: 'md_get_levels'},
		success: function(res) {
			//console.log(res);
			if (res) {
				json = JSON.parse(res);
				jQuery.each(json, function(k,v) {
					jQuery('.md-settings-container #level-list').append('<option value="' + this.id + '">' + this.level_name + '</option>');
				});
			}
		}
	});
	jQuery('#btnProcessPreauth').click(function(e) {
		e.preventDefault();
		jQuery('#charge-notice').hide();
		jQuery('#charge-notice .success-count, #charge-notice .fail-count').text('');
		jQuery(this).attr('disabled', 'disabled');
		var level = jQuery('#level-list').val();
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'md_process_preauth', Level: level},
			success: function(res) {
				//console.log(res);
				json = JSON.parse(res);
				jQuery("#charge-confirm").html('<div id="charge-notice" class="updated fade below-h2" id="message"><p><span class="success-count">' + json.counts.success + '</span> Successful Transactions Processed, <span class="fail-count">' + json.counts.failures + '</span> Failed Transactions.</p><a id="close-notice" href="#">Close</a></div>');
				jQuery('#charge-notice').show();
	    		jQuery("#close-notice").click(function(event) {
	    			if (jQuery("#charge-notice").is(":visible")) {
	    				jQuery("#charge-notice").hide();
	    			}
	    		});
				jQuery('#btnProcessPreauth').removeAttr('disabled');
			}
		});
	});
});