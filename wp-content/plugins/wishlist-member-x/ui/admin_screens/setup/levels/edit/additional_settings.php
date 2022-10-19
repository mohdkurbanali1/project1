<div role="tabpanel" class="tab-pane" id="" data-id="levels_additional_settings">
	<div class="content-wrapper">
		
		<br>
		<div class="row">
			<div class="col-xxxl-4 col-md-6">
				<h5><?php esc_html_e( 'Registration Date Reset', 'wishlist-member' ); ?></h5>
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'For Expired Level', 'wishlist-member' ); ?>',
						name  : 'registrationdatereset',
						value : '1',
						class : 'wlm_toggle-switch',
						type  : 'checkbox',
						tooltip : '<?php esc_js_e( 'Resets the registration date when a member re-registers for an expired level.', 'wishlist-member' ); ?>'
					}
				</template>
				<br class="d-block d-sm-none">
			</div>
			<div class="col-md-4">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Member Role', 'wishlist-member' ); ?>',
						name  : 'role',
						value : '',
						type  : 'select',
						options : js_roles,
						group_class : 'no-margin',
						style : 'width: 100%',
						tooltip : '<?php esc_js_e( 'The WordPress Role for all of the Members within a Membership Level can be set accordingly. In most cases this should be set to Subscriber.', 'wishlist-member' ); ?>'
					}
				</template>
				<br class="d-block d-sm-none">
			</div>
			<div class="col-md-12">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'For Active Level', 'wishlist-member' ); ?>',
						name  : 'registrationdateresetactive',
						value : '1',
						class : 'wlm_toggle-switch',
						type  : 'checkbox',
						tooltip : '<?php esc_js_e( 'Resets the registration date when a member re-registers for an active level.', 'wishlist-member' ); ?>'
					}
				</template>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12">
				<h5><?php esc_html_e( 'Redirects', 'wishlist-member' ); ?></h5>
			</div>
		</div>
		<div class="row">
			<div class="col-xxxl-4 col-md-6 col-xs-8">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Custom After Registration Redirect', 'wishlist-member' ); ?>',
						name  : 'custom_afterreg_redirect',
						value : '1',
						type : 'toggle-adjacent-disable',
						tooltip_size: 'md',
						tooltip: '<?php esc_js_e( 'If enabled, this will control what is  immediately displayed after a successful registration for this level. If not enabled, the information configured in the Global Defaults of the Advanced options will be used. <br><br>NOTE this will appear ONE time only.', 'wishlist-member' ); ?>'
					}
				</template>
			</div>
			<div class="col-md-4 col-xs-4">
				<button data-toggle="modal" data-target="#custom-redirects" data-notif-setting="afterreg-redirect" class="btn -primary -condensed" data-notif-title="Custom After Registration Redirect">
					<i class="wlm-icons">settings</i>
					<span class="text"><?php esc_html_e( 'Configure', 'wishlist-member' ); ?></span>
				</button>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xxxl-4 col-md-6 col-xs-8">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Custom After Login Redirect', 'wishlist-member' ); ?>',
						name  : 'custom_login_redirect',
						value : '1',
						type : 'toggle-adjacent-disable',
						tooltip_size: 'md',
						tooltip : '<?php esc_js_e( 'If enabled, this will determine what is immediately displayed after every successful login for this Membership Level.<br><br>If it is not enabled the settings in the Global Defaults in the Advanced Options will be used. ', 'wishlist-member' ); ?>'
					}
				</template>
			</div>
			<div class="col-md-4 col-xs-4">
				<button data-toggle="modal" data-target="#custom-redirects" data-notif-setting="login-redirect" class="btn -primary -condensed" data-notif-title="Custom After Login Redirect">
					<i class="wlm-icons">settings</i>
					<span class="text"><?php esc_html_e( 'Configure', 'wishlist-member' ); ?></span>
				</button>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xxxl-4 col-md-6 col-xs-8">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Custom After Logout Redirect', 'wishlist-member' ); ?>',
						name  : 'custom_logout_redirect',
						value : '1',
						type : 'toggle-adjacent-disable',
						tooltip_size: 'md',
						tooltip: '<?php esc_js_e( 'If enabled, this will determine what is immediately displayed after a logout for this Membership Level.<br><br>If it is not enabled the settings in the Global Defaults in the Advanced Options will be used. ', 'wishlist-member' ); ?>'
					}
				</template>
			</div>
			<div class="col-md-4 col-xs-4">
				<button data-toggle="modal" data-target="#custom-redirects" data-notif-setting="logout-redirect" class="btn -primary -condensed" data-notif-title="Custom After Logout Redirect">
					<i class="wlm-icons">settings</i>
					<span class="text"><?php esc_html_e( 'Configure', 'wishlist-member' ); ?></span>
				</button>
			</div>
		</div>

		<div class="row legacy-feature">
			<div class="col-xxxl-4 col-md-4 col-xs-8">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Enable Sales Page URL', 'wishlist-member' ); ?>',
						name  : 'enable_salespage',
						value : '1',
						class : 'wlm_toggle-switch wlm_toggle-adjacent',
						tooltip : '<?php esc_js_e( 'This is a Legacy Feature and is no longer supported.', 'wishlist-member' ); ?>',
						type  : 'checkbox'
					}
				</template>
			</div>
			<div class="col-md-8 col-xs-4">
				<template class="wlm3-form-group">
					{
						group_class : 'no-margin',
						name  : 'salespage',
						value : '',
						type  : 'text',
						placeholder : '<?php esc_js_e( 'Optional Sales Page URL', 'wishlist-member' ); ?>'
					}
				</template>
			</div>
		</div>
		<br>
		<div class="panel-footer -content-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<?php echo wp_kses_post( $tab_footer ); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- keeping these for the sake of rolling back to 2.9x -->
<input type="hidden" name="afterregredirect">
<input type="hidden" name="loginredirect">
