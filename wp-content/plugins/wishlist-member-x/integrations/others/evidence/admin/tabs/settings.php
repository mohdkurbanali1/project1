<div class="row">
	<template class="wlm3-form-group">
		{
			type : 'url',
			name : 'evidence_settings[webhook_url]',
			column : 'col-md-8 pr-0',
			class : 'applycancel',
			label : '<?php esc_js_e( 'Default Webhook URL', 'wishlist-member' ); ?>',
			placeholder : '<?php esc_js_e( 'https://', 'wishlist-member' ); ?>',
		}
	</template>
	<div class="col-auto pr-0">
			<label>&nbsp;</label>
			<button class="btn d-block -default -condensed evidence-test-webhook"><?php esc_html_e( 'Test', 'wishlist-member' ); ?></button>
	</div>
</div>
