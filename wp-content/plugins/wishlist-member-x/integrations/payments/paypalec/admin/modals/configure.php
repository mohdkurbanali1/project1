<div
	data-process="modal"
	id="configure-<?php echo esc_attr( $config['id'] ); ?>-template" 
	data-id="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-label="configure-<?php echo esc_attr( $config['id'] ); ?>"
	data-title="<?php echo esc_attr( $config['name'] ); ?> Configuration"
	data-show-default-footer="1"
	data-classes="modal-lg"
	style="display:none">
	<div class="body">
		<div class="row">
			<div class="col-md-12">
				<ul class="nav nav-tabs">
					<li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#paypalec-connect"><?php esc_html_e( 'API', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#paypalec-spb"><?php esc_html_e( 'Smart Payment Buttons', 'wishlist-member' ); ?></a></li>
				</ul>
			</div>
		</div>
		<div class="tab-content">
			<div class="tab-pane active in" id="paypalec-connect">
				<div class="row">
					<div class="col-md-12">
						<p><a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_get-api-signature&amp;generic-flow=true" target="paypal-api-get-signature" onclick="window.open(this.href, 'paypal-api-get-signature', 'height=500,width=360')"><?php esc_html_e( 'Click here to get your live PayPal API credentials', 'wishlist-member' ); ?></a></p>
					</div>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'API Username', 'wishlist-member' ); ?>',
							name : 'paypalecsettings[live][api_username]',
							column : 'col-md-6',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'API Password', 'wishlist-member' ); ?>',
							name : 'paypalecsettings[live][api_password]',
							column : 'col-md-6',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'API Signature', 'wishlist-member' ); ?>',
							name : 'paypalecsettings[live][api_signature]',
							column : 'col-md-12',
						}
					</template>
				</div>
				<div class="row">
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Enable Sandbox Testing', 'wishlist-member' ); ?>',
							name : 'paypalecsettings[sandbox_mode]',
							id : 'paypalec-enable-sandbox',
							value : 1,
							uncheck_value : 0,
							type : 'checkbox',
							column : 'col-md-12 mb-2',
						}
					</template>
				</div>
				<div class="row" id="paypalec-sandbox-settings" style="display:none">
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Sandbox API Username', 'wishlist-member' ); ?>',
							name : 'paypalecsettings[sandbox][api_username]',
							column : 'col-md-6',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Sandbox API Password', 'wishlist-member' ); ?>',
							name : 'paypalecsettings[sandbox][api_password]',
							column : 'col-md-6',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Sandbox API Signature', 'wishlist-member' ); ?>',
							name : 'paypalecsettings[sandbox][api_signature]',
							column : 'col-md-12',
						}
					</template>
				</div>
			</div>
			<div class="tab-pane" id="paypalec-spb">
				<div class="row">
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Use Smart Payment Buttons', 'wishlist-member' ); ?>',
							name : 'paypalec_spb[enable]',
							column : 'col-md-12',
							type : 'checkbox',
							value : '1',
							uncheck_value : '0',
						}
					</template>
				</div>
				<div class="row mt-4" id="paypalec-spb-settings" style="display: none">
					<div class="col-6">
						<div class="row">
							<template class="wlm3-form-group">
								{
									type : 'select',
									options : [
										{ value : 'vertical', text : '<?php esc_attr_e( 'Vertical', 'wishlist-member' ); ?>' },
										{ value : 'horizontal', text : '<?php esc_attr_e( 'Horizontal', 'wishlist-member' ); ?>' },
									],
									name : 'paypalec_spb[layout]',
									label : '<?php esc_js_e( 'Layout', 'wishlist-member' ); ?>',
									column : 'col-md-6',
									style : 'width: 100%',
									group_class : 'mb-2',
								}
							</template>
							<template class="wlm3-form-group">
								{
									type : 'select',
									options : [
										{ value : 'medium', text : '<?php esc_attr_e( 'Medium', 'wishlist-member' ); ?>' },
										{ value : 'large', text : '<?php esc_attr_e( 'Large', 'wishlist-member' ); ?>' },
										{ value : 'responsive', text : '<?php esc_attr_e( 'Responsive', 'wishlist-member' ); ?>' },
									],
									name : 'paypalec_spb[size]',
									label : '<?php esc_js_e( 'Size', 'wishlist-member' ); ?>',
									column : 'col-md-6',
									style : 'width: 100%',
									group_class : 'mb-2',
								}
							</template>
							<template class="wlm3-form-group">
								{
									type : 'select',
									options : [
										{ value : 'pill', text : '<?php esc_attr_e( 'Pill', 'wishlist-member' ); ?>' },
										{ value : 'rect', text : '<?php esc_attr_e( 'Rectangle', 'wishlist-member' ); ?>' },
									],
									name : 'paypalec_spb[shape]',
									label : '<?php esc_js_e( 'Shape', 'wishlist-member' ); ?>',
									column : 'col-md-6',
									style : 'width: 100%',
									group_class : 'mb-2',
								}
							</template>
							<template class="wlm3-form-group">
								{
									type : 'select',
									options : [
										{ value : 'gold', text : '<?php esc_attr_e( 'Gold', 'wishlist-member' ); ?>' },
										{ value : 'blue', text : '<?php esc_attr_e( 'Blue', 'wishlist-member' ); ?>' },
										{ value : 'silver', text : '<?php esc_attr_e( 'Silver', 'wishlist-member' ); ?>' },
										{ value : 'white', text : '<?php esc_attr_e( 'White', 'wishlist-member' ); ?>' },
										{ value : 'black', text : '<?php esc_attr_e( 'Black', 'wishlist-member' ); ?>' },
									],
									name : 'paypalec_spb[color]',
									label : '<?php esc_js_e( 'Color', 'wishlist-member' ); ?>',
									column : 'col-md-6',
									style : 'width: 100%',
									group_class : 'mb-2',
								}
							</template>
							<template class="wlm3-form-group">
								{
									type : 'select',
									options : [
										{ value : 'CARD', text : '<?php esc_attr_e( 'Card', 'wishlist-member' ); ?>' },
										{ value : 'CREDIT', text : '<?php esc_attr_e( 'Credit', 'wishlist-member' ); ?>' },
										{ value : 'ELV', text : '<?php esc_attr_e( 'ELV', 'wishlist-member' ); ?>' },
									],
									name : 'paypalec_spb[funding]',
									label : '<?php esc_js_e( 'Allowed Funding Source', 'wishlist-member' ); ?>',
									column : 'col-md-12',
									style : 'width: 100%',
									multiple : 'multiple',
								}
							</template>
						</div>
					</div>
					<div class="col-6 text-center">
						<div id="paypalec-spb-preview" class="d-inline-block mt-4"></div>
						<div style="position:absolute;top:0;left:0;right:0;bottom:0;z-index:99999999"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
