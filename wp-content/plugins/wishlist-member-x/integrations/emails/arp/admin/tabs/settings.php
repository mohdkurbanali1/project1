<div class="row">
	<template class="wlm3-form-group">
		{
			label : '<?php esc_js_e( 'ARP Application URL', 'wishlist-member' ); ?>',
			name : 'arpurl',
			column : 'col-md-9',
			class : 'applycancel',
			tooltip : '<?php esc_js_e( 'todo', 'wishlist-member' ); ?>',
			type : 'url',
			help_block : '<?php esc_js_e( 'Example: http://www.yourdomain.com/cgi-bin/arp3/arp3-formcapture.pl', 'wishlist-member' ); ?>',
			tooltip : '<?php printf( esc_js( /* Translators: %s = your-domain */ __( 'Copy the example URL displayed below the ARP Application URL field and paste it into the corresponding field and replace %s portion with the correct domain name.', 'wishlist-member' ) ), '<em>your-domain</em>' ); ?>',
			tooltip_size : 'lg',
		}
	</template>
</div>
