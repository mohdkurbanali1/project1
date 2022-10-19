<form>
	<div class="row">
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'Enter the URL or URLs to Forward the IPN to (One URL per Line)', 'wishlist-member' ); ?>',
				column : 'col-md-9',
				value : <?php echo json_encode( $data->paypalec_ipnforwarding ); ?>,
				type : 'textarea',
				name : 'paypalec_ipnforwarding',
				placeholder : '<?php esc_js_e( 'https://...', 'wishlist-member' ); ?>',
				group_class : 'mb-2',
			}
		</template>
		<div class="col-md-12 mb-4">
			<button type="button" class="save-button btn -primary" data-lpignore="true">
				<i class="wlm-icons">save</i>
				<span>Save</span>
			</button>
		</div>
	</div>
	<input type="hidden" name="action" value="admin_actions" />
	<input type="hidden" name="WishListMemberAction" value="save_payment_provider" />
</form>
