<div class="page-header">
	<div class="row">
		<div class="col-md-9 col-sm-9 col-xs-8">
			<h2 class="page-title">
				<?php esc_html_e( 'Legacy', 'wishlist-member' ); ?>
			</h2>
		</div>
		<div class="col-md-3 col-sm-3 col-xs-4">
			<?php require $this->plugindir3 . '/helpers/header-icons.php'; ?>
		</div>
	</div>
</div>
<div class="content-wrapper">
	<div class="row">
		<?php $option_val = $this->get_option( 'disable_legacy_reg_shortcodes' ); ?>
		<div class="col-md-12">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Allow Legacy Registration Shortcodes', 'wishlist-member' ); ?>',
					name  : 'disable_legacy_reg_shortcodes',
					value : '0',
					checked_value : '<?php echo esc_js( $option_val ); ?>',
					uncheck_value : '1',
					class : 'wlm_toggle-switch notification-switch',
					type  : 'checkbox',
					tooltip: '<?php esc_js_e( 'Older Legacy Registration Shortcodes (Example: wlm_register_levelname) will continue to function if this setting is enabled', 'wishlist-member' ); ?>',
					more_text: 'Learn More',
					more_link: 'https://help.wishlistproducts.com/',
				}
			</template>
			<input type="hidden" name="action" value="admin_actions" />
			<input type="hidden" name="WishListMemberAction" value="save" />
		</div>
	</div>
	<div class="row">
		<?php $option_val = $this->get_option( 'disable_legacy_private_tags' ); ?>
		<div class="col-md-12">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Allow Legacy Private Tags Mergecodes', 'wishlist-member' ); ?>',
					name  : 'disable_legacy_private_tags',
					value : '0',
					checked_value : '<?php echo esc_js( $option_val ); ?>',
					uncheck_value : '1',
					class : 'wlm_toggle-switch notification-switch',
					type  : 'checkbox',
					tooltip : '<?php esc_js_e( 'Legacy Private Tags (Example: wlm_private_levename) will continue to function if this setting is enabled.<br><br>If you did not use the legacy method of Private Tags mentioned above, this setting should remain disabled to improve site performance.', 'wishlist-member' ); ?>',
					tooltip_size : 'lg',
					more_text: 'Learn More',
					more_link: 'https://help.wishlistproducts.com/',
				}
			</template>
			<input type="hidden" name="action" value="admin_actions" />
			<input type="hidden" name="WishListMemberAction" value="save" />
		</div>
	</div>
	<div class="row">
		<?php $option_val = $this->get_option( 'show_legacy_integrations' ); ?>
		<div class="col-md-12">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Enable Legacy Integrations', 'wishlist-member' ); ?>',
					name  : 'show_legacy_integrations',
					value : '1',
					checked_value : '<?php echo esc_js( $option_val ); ?>',
					uncheck_value : '0',
					class : 'wlm_toggle-switch notification-switch',
					type  : 'checkbox',
					tooltip: '<?php esc_js_e( 'Legacy Integration options will appear as available in the Setup > Integrations section if this setting is enabled.', 'wishlist-member' ); ?>',
					more_text: 'Learn More',
					more_link: 'https://help.wishlistproducts.com/',
				}
			</template>
			<input type="hidden" name="action" value="admin_actions" />
			<input type="hidden" name="WishListMemberAction" value="save" />
		</div>
	</div>
	<div class="row" style="margin-bottom: 6px;">
		<?php $option_val = $this->get_option( 'show_legacy_features' ); ?>
		<div class="col-md-12">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Enable Legacy Features', 'wishlist-member' ); ?>',
					name  : 'show_legacy_features',
					value : '1',
					checked_value : '<?php echo esc_js( $option_val ); ?>',
					uncheck_value : '0',
					class : 'wlm_toggle-switch notification-switch',
					type  : 'checkbox',
					tooltip: '<?php esc_js_e( 'Some features have been discontinued within WishList Member. Enabling this option will allow them to be used. However, they are no longer supported.', 'wishlist-member' ); ?>',
					more_text: 'Learn More',
					more_link: 'https://help.wishlistproducts.com/',
				}
			</template>
			<input type="hidden" name="action" value="admin_actions" />
			<input type="hidden" name="WishListMemberAction" value="save" />
		</div>
	</div>
</div>
