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
					label : '<?php esc_js_e( 'Authorization Code', 'wishlist-member' ); ?>',
					type : 'text',
					name : 'webinar[gotomeetingapi][authorizationcode]',
					column : 'col-md-12',
					help_block : '<a href="<?php echo esc_url( $oauth->getApiAuthorizationUrl() ); ?>" target="_blank">Click here to obtain an authorization code</a>'
				}
			</template>
			<input type="hidden" name="webinar[gotomeetingapi][accesstoken]">
			<input type="hidden" name="webinar[gotomeetingapi][organizerkey]">
		</div>
	</div>
</div>
