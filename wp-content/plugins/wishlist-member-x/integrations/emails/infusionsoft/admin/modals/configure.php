<div
	data-process="modal"
	id="configure-<?php echo esc_attr( $config['id'] ); ?>-template" 
	data-id="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-label="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-title="<?php echo esc_attr( $config['name'] ); ?> Configuration"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<div class="row -integration-keys">
			<?php echo wp_kses_post( $api_status_markup ); ?>		
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'App Name', 'wishlist-member' ); ?>',
					tooltip : '<?php esc_js_e( 'Example:', 'wishlist-member' ); ?> <em>appname</em>.infusionsoft.com',
					tooltip_size : 'md',
					name : 'ismname',
					column : 'col-md-12',
					addon_right : '.infusionsoft.com',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Encrypted Key', 'wishlist-member' ); ?>',
					tooltip : '<?php esc_js_e( 'This key is located in Infusionsoft under', 'wishlist-member' ); ?><br><em><?php esc_js_e( 'Admin > Settings > Application', 'wishlist-member' ); ?></em>',
					tooltip_size : 'lg',
					name : 'iskey',
					column : 'col-md-12',
				}
			</template>
			<!-- <div class="col-md-2">
				<label>&nbsp;</label>
				<a class="btn btn-block -default -condensed -no-icon save-keys"><span class="-processing"><?php esc_html_e( 'Processing...', 'wishlist-member' ); ?></span><span class="-connected"><?php esc_html_e( 'Disconnect', 'wishlist-member' ); ?></span><span class="-disconnected"><?php esc_html_e( 'Connect', 'wishlist-member' ); ?></span></a>
			</div> -->
		</div>
	</div>
</div>
