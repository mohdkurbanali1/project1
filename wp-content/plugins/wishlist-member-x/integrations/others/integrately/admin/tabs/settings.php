<?php
/**
 * Integrately settings
 *
 * @package WishListMember\Integrations\Others\Integrately
 */

$auth_user = esc_attr( 'others/' . $config['id'] );
?>
<form>
	<div class="row">
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'WordPress URL', 'wishlist-member' ); ?>',
				name : '',
				column : 'col-12 col-md-6',
				value : '<?php echo esc_url( admin_url() ); ?>',
				readonly : 'readonly',
				class : 'copyable',
			}
		</template>
		<div class="w-100"></div>
		<template class="wlm3-form-group">
			{
				label : '<?php esc_js_e( 'Digest Auth Username', 'wishlist-member' ); ?>',
				name : '',
				column : 'col-12 col-md-6',
				value : '<?php echo esc_js( $auth_user ); ?>',
				class : 'copyable',
				readonly : 'readonly',
			}
		</template>
		<div class="col-12">
			<label for=""><?php esc_html_e( 'API Key / Digest Auth Password', 'wishlist-member' ); ?></label>
		</div>
		<template class="wlm3-form-group">
			{
				name : '',
				column : 'col-12 col-md-6',
				value : <?php echo wp_json_encode( $wlmapikey ); ?>,
				readonly : 'readonly',
				id : '<?php echo esc_js( $config['id'] ); ?>-apikey',
				'data-keyname' : '<?php echo esc_js( $auth_user ); ?>',
				class : 'copyable',
				group_class : 'mb-2 mb-md-4'
			}
		</template>
		<div class="col-12 col-md-auto pl-md-0 pb-3 text-right">
			<button type="button" data-action="gen-api-key" data-target="#<?php echo esc_attr( $config['id'] ); ?>-apikey" name="button" class="btn -default -condensed"><?php esc_html_e( 'Generate New Key', 'wishlist-member' ); ?></button>
		</div>
	</div>
</form>
