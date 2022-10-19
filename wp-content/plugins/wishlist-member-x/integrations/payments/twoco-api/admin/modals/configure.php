<div
	data-process="modal"
	id="configure-<?php echo esc_attr( $config['id'] ); ?>-template" 
	data-id="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-label="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-title="<?php echo esc_attr( $config['name'] ); ?> Configuration"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<input type="hidden" id="twoco-api-vendor-id" name="twocovendorid">
		<div class="row">
			<div class="col-md-12">		
				<ul class="nav nav-tabs">
					<li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#twocoapi-connect"><?php esc_html_e( 'API', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#twocoapi-settings"><?php esc_html_e( 'Settings', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#twocoapi-form"><?php esc_html_e( 'Payment Form', 'wishlist-member' ); ?></a></li>
				</ul>
			</div>
		</div>
		<div class="tab-content">
			<div class="tab-pane active in" id="twocoapi-connect">
				<div class="row">
					<?php echo wp_kses_post( $api_status_markup ); ?>		
				</div>
				<div class="row -integration-keys">
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Merchant Code', 'wishlist-member' ); ?>',
							name : 'twocheckoutapisettings[twocheckoutapi_seller_id]',
							id : 'twoco-api-seller-id',
							column : 'col-6'
						}
					</template>	
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Publishable Key', 'wishlist-member' ); ?>',
							name : 'twocheckoutapisettings[twocheckoutapi_publishable_key]',
							column : 'col-12',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Private Key', 'wishlist-member' ); ?>',
							name : 'twocheckoutapisettings[twocheckoutapi_private_key]',
							column : 'col-12',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Enable Sandbox Mode', 'wishlist-member' ); ?>',
							name : 'twocheckoutapisettings[twocheckoutapi_sandbox]',
							value : 1,
							uncheck_value : 0,
							type : 'checkbox',
							column : 'col-md-12',
						}
					</template>
				</div>
			</div>
			<div class="tab-pane" id="twocoapi-settings">
				<div class="row">
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Primary Currency', 'wishlist-member' ); ?>',
							type : 'select',
							name : 'twocheckoutapisettings[currency]',
							options : WLM3ThirdPartyIntegration['twoco-api'].currencies,
							style : 'width: 100%',
							column : 'col-6',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Support Email', 'wishlist-member' ); ?>',
							name : 'twocheckoutapisettings[supportemail]',
							column : 'col-12',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Secret Key', 'wishlist-member' ); ?>',
							name : 'twocosecret',
							column : 'col-6',
						}
					</template>
				</div>
			</div>
			<div class="tab-pane" id="twocoapi-form">
				<div class="row">
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Heading', 'wishlist-member' ); ?>',
							name : 'twocheckoutapisettings[formheading]',
							column : 'col-12',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Heading Logo', 'wishlist-member' ); ?>',
							name : 'twocheckoutapisettings[logo]',
							column : 'col-12',
							type : 'wlm3media'
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Button Label', 'wishlist-member' ); ?>',
							name : 'twocheckoutapisettings[buttonlabel]',
							column : 'col-6',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Panel Button Label', 'wishlist-member' ); ?>',
							name : 'twocheckoutapisettings[panelbuttonlabel]',
							column : 'col-6',
						}
					</template>
				</div>
			</div>
		</div>
	</div>
</div>
