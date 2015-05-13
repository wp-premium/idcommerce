jQuery(document).ready(function() {
	jQuery('.bm_payouts #level-list').change(function() {
		jQuery('.bm_payouts input[name="bm_submit"]').attr('disabled', 'disabled');
		jQuery('.payout-message').remove();
		var productID = jQuery(this).val();
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {'action': 'md_bm_process_prep', ID: productID},
			success: function(res) {
				//console.log(res);
				jQuery('.bm_payouts input[name="bm_submit"]').removeAttr('disabled');
				if (res) {
					var json = JSON.parse(res);
					if (json) {
						//console.log(json);
						jQuery('.payout-info').show();
						jQuery('.total-orders').text(json.orders);
						jQuery('.total-payout').text(json.total);
						jQuery('.payee-name').text(json.name);
						jQuery('.customer-id').text(json.customer_id);
						jQuery('.total-payout').data('payout', json.total);
					}
				}
			}
		});
	});
	jQuery('.bm_payouts input[name="bm_submit"]').click(function(e) {
		e.preventDefault();
		jQuery(this).attr('disabled', 'disabled');
		var productID = jQuery('.bm_payouts #level-list').val();
		var payout = jQuery('.total-payout').data('payout');
		if (payout > 0) {
			jQuery.ajax({
				url: md_ajaxurl,
				type: 'POST',
				data: {'action': 'md_bm_process_payout', ID: productID, Payout: payout},
				success: function(res) {
					//console.log(res);
					jQuery(this).removeAttr('disabled');
					if (res) {
						json = JSON.parse(res);
						if (json) {
							if (json.status == 'failure') {
								jQuery('.payout-message').remove();
								jQuery('.payout-info p').after('<p class="payout-message">' + json.message + '</p>');
							}
							else {
								jQuery('.payout-message').remove();
								jQuery('.payout-info p').after('<p class="payout-message">' + json.status + '</p>');
							}
						}
					}
				}
			});
		}
		else {
			jQuery('.payout-info p').after('<p class="payout-message">Nothing to Pay</p>');
			jQuery(this).removeAttr('disabled');
		}
	});
});