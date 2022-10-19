<div
	data-process="modal"
	id="configure-<?php echo esc_attr( $config['id'] ); ?>-template"
	data-id="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-label="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-title="<?php echo esc_attr( $config['name'] ); ?> Configuration"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<input type="hidden" class="-url" name="stripethankyou" />
		<div class="row">
			<div class="col-md-12">
				<ul class="nav nav-tabs">
					<li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#stripe-connect"><?php esc_html_e( 'API', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#stripe-settings"><?php esc_html_e( 'Settings', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#stripe-form"><?php esc_html_e( 'Payment Form', 'wishlist-member' ); ?></a></li>
				</ul>
			</div>
		</div>
		<div class="tab-content">
			<div class="tab-pane active in" id="stripe-connect">
				<div class="row">
					<?php echo wp_kses_post( $api_status_markup ); ?>
				</div>
				<div class="row -integration-keys">
					<div class="col-md-12">
						<p>Copy the Secret Key and Publishable Key from the <a href="https://dashboard.stripe.com/account/apikeys" target="_blank"><?php esc_html_e( 'Account > API Keys', 'wishlist-member' ); ?></a> section of Stripe and paste them into the appropriate fields.</p>
					</div>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Publishable Key', 'wishlist-member' ); ?>',
							name : 'stripepublishablekey',
							column : 'col-md-12',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Secret Key', 'wishlist-member' ); ?>',
							name : 'stripeapikey',
							column : 'col-md-12',
						}
					</template>
				</div>
			</div>
			<div class="tab-pane" id="stripe-settings">
				<div class="row">
					<template class="wlm3-form-group">
						{
							type : 'select',
							label : '<?php esc_js_e( 'Primary Currency', 'wishlist-member' ); ?>',
							name : 'stripesettings[currency]',
							style : 'width: 100%',
							options : WLM3ThirdPartyIntegration.stripe.currencies,
							column : 'col-12',
							'data-placeholder' : '<?php esc_js_e( 'Select a Currency', 'wishlist-member' ); ?>',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Support Email', 'wishlist-member' ); ?>',
							name : 'stripesettings[supportemail]',
							column : 'col-12',
						}
					</template>
					<template class="wlm3-form-group">
						{
							type : 'select',
							label : '<?php esc_js_e( 'Cancellation Redirect', 'wishlist-member' ); ?>',
							name : 'stripesettings[cancelredirect]',
							style : 'width: 100%',
							options : WLM3ThirdPartyIntegration.stripe.pages,
							column : 'col-12',
							'data-placeholder' : '<?php esc_js_e( 'Select a Page', 'wishlist-member' ); ?>',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Immediately cancel Stripe Subscription and Level in WishList Member when the user cancels their subscription via the Stripe Profile Shortcode.', 'wishlist-member' ); ?>',
							name : 'stripesettings[endsubscriptiontiming]',
							value : 'immediate',
							uncheck_value : 'periodend',
							type  : 'checkbox',
							column : 'col-12',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Prorate Upgrades', 'wishlist-member' ); ?>',
							name  : 'stripesettings[prorate]',
							value : 'yes',
							uncheck_value : 'no',
							type  : 'checkbox',
							column : 'col-12',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Allow Stripe to Automatically Calculate Tax on Subscriptions', 'wishlist-member' ); ?>',
							name  : 'stripesettings[automatictax]',
							value : 'yes',
							uncheck_value : 'no',
							type  : 'checkbox',
							column : 'col-12',
						}
					</template>
				</div>
			</div>
			<div class="tab-pane" id="stripe-form">
				<div class="row">
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Heading Text', 'wishlist-member' ); ?>',
							name : 'stripesettings[formheading]',
							column : 'col-12',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Heading Logo', 'wishlist-member' ); ?>',
							name : 'stripesettings[logo]',
							column : 'col-12',
							type : 'wlm3media'
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Button Label', 'wishlist-member' ); ?>',
							name : 'stripesettings[buttonlabel]',
							column : 'col-6',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Panel Button Label', 'wishlist-member' ); ?>',
							name : 'stripesettings[panelbuttonlabel]',
							column : 'col-6',
						}
					</template>
				</div>
			</div>
		</div>
	</div>
</div>
