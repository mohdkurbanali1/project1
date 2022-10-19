WLM3ThirdPartyIntegration.woocommerce.fxn = {
	load_products : function() {
		// $.each(all_levels, function(k, v) {
		// 	if(!Object.keys(v).length) return true;
			var data = {
				products : WLM3ThirdPartyIntegration.woocommerce.woocommerce_products
			}
			var tmpl = _.template($('script#woocommerce-products-template2').html(), {variable: 'data'});
			var html = tmpl(data);
			$('#woocommerce-products tbody').html(html.trim());
		// });
		$('#thirdparty-provider-container-woocommerce .-del-btn')
		.do_confirm( { confirm_message : wlm.translate( 'Delete this Product?' ), yes_button : wlm.translate( 'Delete' ) } )
		.on('yes.do_confirm', function() {
			var tr = $(this).closest('tr');
			$.post(
				WLM3VARS.ajaxurl,
				{
					action : 'wlm3_delete_woocommerce_product',
					access : tr.data('access'),
					id : tr.data('id'),
				},
				function(result) {
					if(result.status) {
						WLM3ThirdPartyIntegration.woocommerce.woocommerce_products = result.data.woocommerce_products;
						WLM3ThirdPartyIntegration.woocommerce.fxn.load_products();
					}
				},
				'json'
			);
		});
	}
}

integration_after_open['woocommerce'] = function(obj) {
	WLM3ThirdPartyIntegration.woocommerce.fxn.load_products();
	var $me = $('#thirdparty-provider-container-woocommerce');
	$me.off('.wlm3-woocommerce');
	$me.on('click.wlm3-woocommerce', '.-edit-btn', function() {
		var tr = $(this).closest('tr');
		$('#products-woocommerce [name="access"]').val(new String(tr.data('access')).split(',')).trigger('change');
		$('#products-woocommerce [name="id"]').val([tr.data('id')]).trigger('change');
		$('#products-woocommerce [name="old_id"]').val([tr.data('id')]).trigger('change');
		$('#products-woocommerce').modal('show');
	});
	$me.on('click.wlm3-woocommerce', '.-add-btn', function() {
		var tr = $(this).closest('tr');
		$('#products-woocommerce [name="access"]').val('-1').trigger('change');
		$('#products-woocommerce [name="id"]').val('-1').trigger('change');
		$('#products-woocommerce [name="old_id"]').val('').trigger('change');
		$('#products-woocommerce').modal('show');
	});
}

integration_modal_save['woocommerce'] = function(me, settings_data, result, textStatus) {
	if(result.status) {
		WLM3ThirdPartyIntegration.woocommerce.woocommerce_products = result.data.woocommerce_products;
		WLM3ThirdPartyIntegration.woocommerce.fxn.load_products();
	}
}