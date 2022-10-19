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
					label : '<?php esc_js_e( 'PayPal Email', 'wishlist-member' ); ?>',
					name : 'ppemail',
					column : 'col-md-12',
					tooltip : '<?php esc_js_e( 'Enter the PayPal email address tied to the PayPal account that will be used for the integration.', 'wishlist-member' ); ?>',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'PDT Identity Token', 'wishlist-member' ); ?>',
					name : 'pptoken',
					column : 'col-md-12',
					tooltip : '<?php esc_js_e( 'The PayPal PDT Identity Token is located in the My Account > My Selling Tools > Website Preferences section of PayPal.', 'wishlist-member' ); ?>',
					tooltip_size : 'lg',
				}
			</template>
		</div>
		<div class="row">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Enable Sandbox Testing', 'wishlist-member' ); ?>',
					name : 'ppsandbox',
					id : 'paypalps-enable-sandbox',
					value : 1,
					uncheck_value : 0,
					type : 'checkbox',
					column : 'col-md-12 mb-2',
				}
			</template>
		</div>
		<div class="row" id="paypalps-sandbox-settings" style="display: none">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Sandbox PayPal Email', 'wishlist-member' ); ?>',
					name : 'ppsandboxemail',
					column : 'col-md-12',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Sandbox PDT Identity Token', 'wishlist-member' ); ?>',
					name : 'ppsandboxtoken',
					column : 'col-md-12',
				}
			</template>
		</div>
	</div>
</div>
