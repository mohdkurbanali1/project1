<form>
	<div class="row">
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'Secret Word', 'wishlist-member' ); ?>',
				name : 'cydecsecret',
				column : 'col-md-9',
				class : 'applycancel',
				tooltip : '<p><?php esc_js_e( 'The Secret Word is used to generate a hash key for security purposes.', 'wishlist-member' ); ?></p><p><?php esc_js_e( 'The Secret Word can be edited if desired. Note that this Secret Word must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member' ); ?></p>',
				tooltip_size: 'lg',
				help_block : '<?php esc_js_e( 'Copy the Secret Word and paste it into Cydec.', 'wishlist-member' ); ?>',
			}
		</template>
	</div>
	<div class="row">
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'Post To URL', 'wishlist-member' ); ?>',
				name : 'cydecthankyou',
				addon_left : '<?php echo esc_js( $wpm_scregister ); ?>',
				column : 'col-md-auto',
				class : 'text-center -url',
				group_class : '-url-group',
				help_block : '<?php esc_js_e( 'Set the Post To URL in Cydec or the Post To URL for each product to this URL.', 'wishlist-member' ); ?>',
				tooltip : '<?php esc_js_e( 'The end string of the displayed Post To URL can be edited if desired. Note that this Post To URL must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member' ); ?>',
				tooltip_size: 'lg',
				help_block : '<?php esc_js_e( 'Copy the Post To URL and paste it into Cydec.', 'wishlist-member' ); ?>',
			}
		</template>
	</div>
	<input type="hidden" name="action" value="admin_actions" />
	<input type="hidden" name="WishListMemberAction" value="save" />
</form>
