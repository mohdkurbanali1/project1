<div class="row">
	<div class="col-auto mb-4"><?php echo wp_kses_post( $config_button ); ?></div>
	<?php echo wp_kses_post( $api_status_markup ); ?>		
</div>

<div class="row api-required">
	<template class="wlm3-form-group">
		{
			label : '<?php esc_js_e( 'Web Hook', 'wishlist-member' ); ?>',
			readonly : 'readonly',
			column : 'col-auto',
			value : WLM3ThirdPartyIntegration.stripe.stripethankyou_url + '?stripe_action=sync',
			help_block : '<?php printf( esc_js( /* Translators: %s link to stripe webhooks */ __( 'Copy and paste this URL into Stripe at %s', 'wishlist-member' ) ), '<a href="https://dashboard.stripe.com/account/webhooks" target="_blank">https://dashboard.stripe.com/account/webhooks</a>' ); ?>',
		}
	</template>	
</div>

