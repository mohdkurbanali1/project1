<div
	data-process="modal"
	id="configure-<?php echo esc_attr( $config['id'] ); ?>-template" 
	data-id="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-label="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-title="<?php echo esc_attr( $config['name'] ); ?> Configuration"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<div class="row">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Merchant ID', 'wishlist-member' ); ?>',
					name : 'onescmerchantid',
					column : 'col-md-12',
					tooltip : '<?php esc_js_e( 'Your Merchant ID can be found at the upper-right corner of your 1ShoppingCart page.', 'wishlist-member' ); ?>',
					tooltip_size : 'md',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'API Key', 'wishlist-member' ); ?>',
					name : 'onescapikey',
					column : 'col-md-12',
					tooltip : '<?php esc_js_e( 'You can find your API Key by going to My Account -> API Settings in 1ShoppingCart.', 'wishlist-member' ); ?>',
					tooltip_size : 'md',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Retry Grace Period', 'wishlist-member' ); ?>',
					name : 'onescgraceperiod',
					column : 'col-md-5',
					tooltip : '<?php esc_js_e( 'Set the number of days between credit card charge attempts for recurring payments.', 'wishlist-member' ); ?>',
					tooltip_size : 'md',
					type: 'number',
				}
			</template>
			<div class="col-md-6">
				<label>&nbsp;</label>
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Process Upsells', 'wishlist-member' ); ?>',
						name : 'onesc_include_upsells',
						value : 1,
						uncheck_value : 0,
						type : 'checkbox',
					}
				</template>
			</div>
		</div>
	</div>
</div>
