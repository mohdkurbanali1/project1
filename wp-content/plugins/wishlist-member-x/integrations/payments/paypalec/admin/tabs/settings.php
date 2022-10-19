<form>
	<div class="row">
		<?php echo wp_kses_post( $pp_upgrade_instructions ); ?>
		<div class="col-auto mb-4"><?php echo wp_kses_post( $config_button ); ?></div>
	</div>
	<div class="row">
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'Instant Payment Notification URL', 'wishlist-member' ); ?>',
				column : 'col-md-6',
				class : 'copyable',
				readonly : 'readonly',
				value : '<?php echo esc_js( add_query_arg( 'action', 'ipn', $data->paypalecthankyou_url ) ); ?>',
				help_block : '<?php esc_js_e( 'Set this as the Instant Payment Notification URL in PayPal by updating your settings under My Profile > Selling Tools > Instant Payment Notifications', 'wishlist-member' ); ?>'
			}
		</template>
	</div>
	<div class="row">
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'Cancellation URL', 'wishlist-member' ); ?>',
				name : 'paypalec_cancel_url',
				column : 'col-md-6',
				class : 'applycancel',
				tooltip : '<p><?php esc_js_e( 'The URL a member will be redirected to if they cancel their purchase on the PayPal Checkout Page.', 'wishlist-member' ); ?></p><p><?php esc_js_e( 'The member will be redirected to the home page by default if no URL is set here.', 'wishlist-member' ); ?></p>',
				tooltip_size : 'lg',
			}
		</template>
	</div>
	<input type="hidden" class="-url" name="paypalecthankyou" />
	<input type="hidden" name="action" value="admin_actions" />
	<input type="hidden" name="WishListMemberAction" value="save" />
</form>
