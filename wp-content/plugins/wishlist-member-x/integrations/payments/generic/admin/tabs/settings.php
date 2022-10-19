<form>
	<div class="row">
		<div class="col-md-12">
			<p>
				<a href="https://help.wishlistproducts.com/knowledge-base/generic-integration-payment-providers/" target="_blank"><?php esc_html_e( 'Click Here for Documentation', 'wishlist-member' ); ?></a>
			</p>
		</div>
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'Secret Word', 'wishlist-member' ); ?>',
				name : 'genericsecret',
				column : 'col-md-9',
				class : 'applycancel',
				tooltip : '<p><?php esc_js_e( 'The Secret Word is used to generate a hash key for security purposes.', 'wishlist-member' ); ?></p><p><?php esc_js_e( 'The Secret Word can be edited if desired. Note that this Secret Word must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member' ); ?></p>',
				tooltip_size : 'lg',
			}
		</template>
	</div>
	<div class="row">
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'Post To URL', 'wishlist-member' ); ?>',
				name : 'genericthankyou',
				addon_left : '<?php echo esc_js( $wpm_scregister ); ?>',
				column : 'col-md-auto',
				class : 'text-center -url',
				group_class : '-url-group',
				tooltip : '<?php esc_js_e( 'The end string of the displayed Post URL can be edited if desired. Note that this Post URL must be copied and pasted exactly without any spaces before or after it.', 'wishlist-member' ); ?>',
				tooltip_size : 'lg',
				help_block : '<?php esc_js_e( 'The Post To URL is where the information it sent to.', 'wishlist-member' ); ?>',
			}
		</template>
	</div>
	<input type="hidden" name="action" value="admin_actions" />
	<input type="hidden" name="WishListMemberAction" value="save" />
</form>
