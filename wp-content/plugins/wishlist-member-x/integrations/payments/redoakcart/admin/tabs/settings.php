<form>
	<div class="row">
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'Secret Key', 'wishlist-member' ); ?>',
				name : 'redoakcartsecret',
				column : 'col-md-9',
				class : 'applycancel',
				help_block : '<?php esc_js_e( 'Copy the Secret Key and paste it into RedOakCart in the following section: Setup > Membership Sites > Secret Key.', 'wishlist-member' ); ?>',
				tooltip: '<?php esc_js_e( 'The Secret Key can be edited if desired. Note that this Secret Key must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member' ); ?>',
				tooltip_size: 'lg',
			}
		</template>
	</div>
	<div class="row">
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'Post To URL', 'wishlist-member' ); ?>',
				name : 'redoakcartthankyou',
				addon_left : '<?php echo esc_js( $wpm_scregister ); ?>',
				column : 'col-md-auto',
				class : 'text-center -url',
				group_class : '-url-group',
				help_block : '<?php esc_js_e( 'Copy the Post To URL and paste it into RedOakCart in the following section: Setup > Membership Sites > URL.', 'wishlist-member' ); ?>',
				tooltip : '<?php esc_js_e( 'The end string of the displayed Post URL can be edited if desired. Note that this Post URL must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member' ); ?>',
				tooltip_size : 'lg',
			}
		</template>
	</div>
	<input type="hidden" name="action" value="admin_actions" />
	<input type="hidden" name="WishListMemberAction" value="save" />
</form>
