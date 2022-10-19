<?php

$shortcodes     = $this->wlmshortcode->shortcodes;
$wlm_shortcodes = array(
	array(
		'name'    => 'Merge Codes',
		'options' => array(
			array(
				'value' => '',
				'text'  => '',
			),
		),
	),
);
for ( $i = 0; $i < count( $shortcodes ); $i += 3 ) {
	$wlm_shortcodes[0]['options'][] = array(
		'value' => sprintf( '[%s]', $shortcodes[ $i ][0] ),
		'text'  => $shortcodes[ $i + 1 ],
	);
}

$custom_user_data = $this->wlmshortcode->custom_user_data;
if ( $custom_user_data ) {
	$wlm_shortcodes[] = array(
		'name'    => 'Custom Registration Fields',
		'options' => array(),
	);
	foreach ( $custom_user_data as $c ) {
		$wlm_shortcodes[0]['options'][] = array(
			'value' => sprintf( '[wlm_custom %s]', $c ),
			'text'  => $c,
		);
	}
}

// supply options for after reg and after login select
$_pages         = get_pages( 'exclude=' . implode( ',', $this->exclude_pages( array(), true ) ) );
$afterreg_pages = array(
	array(
		'value' => '',
		'text'  => __( 'WordPress Home Page', 'wishlist-member' ),
	),
	array(
		'value' => 'backtopost',
		'text'  => __( 'Redirect Back to Post', 'wishlist-member' ),
	),
);
if ( $_pages ) {
	foreach ( $_pages as $_page ) {
		$afterreg_pages[] = array(
			'value' => $_page->ID,
			'text'  => $_page->post_title,
		);
	}
}

$login_pages = $afterreg_pages;
unset( $login_pages[1] );

printf( "\n<script type='text/javascript'>\n var ppp_defaults = %s\nvar wlm_shortcodes = %s;\nvar afterreg_pages = %s;\nvar login_pages = %s;\n</script>\n", json_encode( $this->ppp_defaults ), json_encode( $wlm_shortcodes ), json_encode( $afterreg_pages ), json_encode( $login_pages ) );

$ppp_settings = $this->get_option( 'payperpost' );
?>
<div id="ppp-global-settings">
	<div class="content-wrapper">
		<div class="row mb-4">
			<?php $option_val = (int) $this->get_option( 'default_ppp' ); ?>
			<div class="col-md-6">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Automatically Enable Pay Per Post for New Content', 'wishlist-member' ); ?>',
						name  : 'default_ppp',
						value : '1',
						checked_value : '<?php echo esc_js( $option_val ); ?>',
						uncheck_value : '0',
						class : 'wlm_toggle-switch notification-switch',
						type  : 'checkbox',
						tooltip : '<?php esc_js_e( 'All newly created Posts and Pages will automatically have Pay Per Post protection turned on if this setting is enabled.', 'wishlist-member' ); ?>',
					}
				</template>
				<input type="hidden" name="action" value="admin_actions" />
				<input type="hidden" name="WishListMemberAction" value="save" />
			</div>
		</div>
		<div class="row">
			<?php $option_val = (int) $ppp_settings['requirecaptcha']; ?>
			<div class="col-xxxl-4 col-md-7 col-sm-7">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Require Captcha Image on Registration Page', 'wishlist-member' ); ?>',
						name  : 'payperpost[requirecaptcha]',
						value : '1',
						checked_value : '<?php echo esc_js( $option_val ); ?>',
						uncheck_value : '0',
						type  : 'toggle-adjacent-disable',
						class : 'notification-switch',
						tooltip : '<?php esc_js_e( 'When checked, a random word will be displayed on the registration form that must be identified by the user and typed into a field to help reduce spam.', 'wishlist-member' ); ?>'
					}
				</template>
				<input type="hidden" name="action" value="admin_actions" />
				<input type="hidden" name="WishListMemberAction" value="save_payperpost_settings" />
			</div>
			<div class="col-md-5 col-sm-5 mb-sm-2">
				<button data-toggle="modal" data-target="#recaptcha-settings" class="btn -primary -condensed <?php echo esc_attr( $option_val ? '' : '-disable' ); ?>" <?php echo $option_val ? '' : 'disabled'; ?>>
					<i class="wlm-icons">settings</i>
					<span class="text"><?php esc_html_e( 'Configure', 'wishlist-member' ); ?></span>
				</button>
				<br class="d-block d-sm-none">
				<br class="d-block d-sm-none">
				<br class="d-block d-sm-none">	
			</div>
		</div>

		<div class="row">
			<?php $option_val = (int) $ppp_settings['custom_afterreg_redirect']; ?>
			<div class="col-xxxl-4 col-md-7 col-sm-7">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Custom After Registration Redirect', 'wishlist-member' ); ?>',
						name  : 'payperpost[custom_afterreg_redirect]',
						value : '1',
						checked_value : '<?php echo esc_js( $option_val ); ?>',
						uncheck_value : '0',
						type  : 'toggle-adjacent-disable',
						class : 'notification-switch',
						tooltip : '<?php esc_js_e( 'If enabled, this will control what is  immediately displayed after a successful registration for this Pay Per Post. If not enabled, the information configured in the Global Defaults of the Advanced options will be used. <br><br>NOTE this will appear ONE time only.', 'wishlist-member' ); ?>'
					}
				</template>
				<input type="hidden" name="action" value="admin_actions" />
				<input type="hidden" name="WishListMemberAction" value="save_payperpost_settings" />
			</div>
			<div class="col-md-5 col-sm-5 mb-sm-2">
				<button data-toggle="modal" data-target="#custom-redirects-afterreg" class="btn -primary -condensed <?php echo esc_attr( $option_val ? '' : '-disable' ); ?>" <?php echo $option_val ? '' : 'disabled'; ?>>
					<i class="wlm-icons">settings</i>
					<span class="text"><?php esc_html_e( 'Configure', 'wishlist-member' ); ?></span>
				</button>
				<br class="d-block d-sm-none">
				<br class="d-block d-sm-none">
				<br class="d-block d-sm-none">	
			</div>
		</div>
		<div class="row">
			<?php $option_val = (int) $ppp_settings['custom_login_redirect']; ?>
			<div class="col-xxxl-4 col-md-7 col-sm-7">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Custom After Login Redirect', 'wishlist-member' ); ?>',
						name  : 'payperpost[custom_login_redirect]',
						value : '1',
						checked_value : '<?php echo esc_js( $option_val ); ?>',
						uncheck_value : '0',
						type  : 'toggle-adjacent-disable',
						class : 'notification-switch',
						tooltip : '<?php esc_js_e( 'If enabled, this will determine what is immediately displayed after every successful login for this Pay Per Post.<br><br>If it is not enabled the settings in the Global Defaults in the Advanced Options will be used. ', 'wishlist-member' ); ?>'
					}
				</template>
				<input type="hidden" name="action" value="admin_actions" />
				<input type="hidden" name="WishListMemberAction" value="save_payperpost_settings" />
			</div>
			<div class="col-md-5 col-sm-5 mb-sm-2">
				<button data-toggle="modal" data-target="#custom-redirects-login" class="btn -primary -condensed <?php echo esc_attr( $option_val ? '' : '-disable' ); ?>" <?php echo $option_val ? '' : 'disabled'; ?>>
					<i class="wlm-icons">settings</i>
					<span class="text"><?php esc_html_e( 'Configure', 'wishlist-member' ); ?></span>
				</button>
				<br class="d-block d-sm-none">
				<br class="d-block d-sm-none">
				<br class="d-block d-sm-none">	
			</div>
		</div>
	</div>

	<?php
		require 'settings/modals/recaptcha.php';
		require 'settings/modals/custom_redirects.php';
	?>
</div>
