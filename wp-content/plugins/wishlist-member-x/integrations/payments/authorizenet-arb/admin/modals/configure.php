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
			<div class="col-12">
				<ul class="nav nav-tabs">
					<li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#anetarb-connect"><?php esc_html_e( 'API', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#anetarb-settings"><?php esc_html_e( 'Settings', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#anetarb-form"><?php esc_html_e( 'Payment Form', 'wishlist-member' ); ?></a></li>
				</ul>
			</div>
		</div>
		<div class="tab-content">
			<div class="tab-pane active in" id="anetarb-connect">
				<div class="row">
					<?php echo wp_kses_post( $api_status_markup ); ?>
					<div class="col-12">
						<p><?php esc_html_e( 'API Credentials are in the Authorize.net Merchant Interface under Account > Settings > API Login ID and Transaction Key', 'wishlist-member' ); ?></em></p>
					</div>	
				</div>
				<div class="row -integration-keys">
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'API Login ID', 'wishlist-member' ); ?>',
							name : 'anetarbsettings[api_login_id]',
							column : 'col-6'
						}
					</template>	
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Transaction Key', 'wishlist-member' ); ?>',
							name : 'anetarbsettings[api_transaction_key]',
							column : 'col-6',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Sandbox Testing', 'wishlist-member' ); ?>',
							name : 'anetarbsettings[sandbox_mode]',
							id : 'anetarb-enable-sandbox',
							value : 1,
							uncheck_value : 0,
							type : 'checkbox',
							column : 'col-12',
						}
					</template>
				</div>
			</div>
			<div class="tab-pane" id="anetarb-settings">
				<div class="row">
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Support Email', 'wishlist-member' ); ?>',
							name : 'authnet_arb_formsettings[supportemail]',
							column : 'col-12',
						}
					</template>
					<?php foreach ( $data->card_types as $value => $label ) : ?>
					<template class="wlm3-form-group">
						{
							type : 'checkbox',
							label : '<?php echo esc_js( $label ); ?>',
							name : 'authnet_arb_formsettings[credit_cards][]',
							value : '<?php echo esc_js( $value ); ?>',
							column : 'col-4',
						}
					</template>	
					<?php endforeach; ?>
				</div>
			</div>
			<div class="tab-pane" id="anetarb-form">
				<div class="row">
					<div class="col-2"><label>Heading Logo</label></div>
					<template class="wlm3-form-group">
						{
							name : 'authnet_arb_formsettings[logo]',
							column : 'col-10',
							type : 'wlm3media'
						}
					</template>
					<div class="offset-2 col-5"><label>One-Time Payment Form</label></div>
					<div class="col-5"><label>Recurring Payment Form</label></div>
					<div class="col-2"><label>Heading Text</label></div>
					<template class="wlm3-form-group">
						{
							name : 'authnet_arb_formsettings[formheading]',
							column : 'col-5',
							group_class : 'mb-2',
						}
					</template>
					<template class="wlm3-form-group">
						{
							name : 'authnet_arb_formsettings[formheadingrecur]',
							column : 'col-5',
							group_class : 'mb-2',
						}
					</template>
					<div class="col-2"><label>Button Label</label></div>
					<template class="wlm3-form-group">
						{
							name : 'authnet_arb_formsettings[formbuttonlabel]',
							column : 'col-5',
							group_class : 'mb-2',
						}
					</template>
					<template class="wlm3-form-group">
						{
							name : 'authnet_arb_formsettings[formbuttonlabelrecur]',
							column : 'col-5',
							group_class : 'mb-2',
						}
					</template>
					<div class="col-2"><label>Text BEFORE</label></div>
					<template class="wlm3-form-group">
						{
							name : 'authnet_arb_formsettings[beforetext]',
							column : 'col-5',
							group_class : 'mb-2',
						}
					</template>
					<template class="wlm3-form-group">
						{
							name : 'authnet_arb_formsettings[beforetextrecur]',
							column : 'col-5',
							group_class : 'mb-2',
						}
					</template>
					<div class="col-2"><label>Text AFTER</label></div>
					<template class="wlm3-form-group">
						{
							name : 'authnet_arb_formsettings[aftertext]',
							column : 'col-5',
							group_class : 'mb-2',
						}
					</template>
					<template class="wlm3-form-group">
						{
							name : 'authnet_arb_formsettings[aftertextrecur]',
							column : 'col-5',
							group_class : 'mb-2',
						}
					</template>
					<template class="wlm3-form-group">
						{
							label : '<?php esc_js_e( 'Show Address Fields', 'wishlist-member' ); ?>',
							name : 'authnet_arb_formsettings[display_address]',
							value : 1,
							uncheck_value : 0,
							type : 'checkbox',
							column : 'offset-2 col-5',
						}
					</template>
					<div class="col-5">
						<p class="text-right"><a href="#arbshortcodes" class="hide-show"><?php esc_html_e( 'Available Short Codes', 'wishlist-member' ); ?></a></p>
					</div>
					<div class="offset-2 col-10">
						<div id="arbshortcodes" class="d-none text-right">
							<code class="arb-shortcodes ml-1" title="Level Name">%<?php esc_html_e( 'level', 'wishlist-member' ); ?></code>
							<code class="arb-shortcodes ml-1" title="Amount">%<?php esc_html_e( 'amount', 'wishlist-member' ); ?></code>
							<code class="arb-shortcodes ml-1" title="Billing Frequency for Recurring Payments only.">%<?php esc_html_e( 'frequency', 'wishlist-member' ); ?></code>
							<code class="arb-shortcodes ml-1" title="Billing Period for Recurring Payments only.">%<?php esc_html_e( 'period', 'wishlist-member' ); ?></code>
							<code class="arb-shortcodes ml-1" title="Billing Cycle for Recurring Payments only.">%<?php esc_html_e( 'cycle', 'wishlist-member' ); ?></code>
							<code class="arb-shortcodes ml-1" title="Trial Cycles for Recurring Payments only.">%<?php esc_html_e( 'trial_cycle', 'wishlist-member' ); ?></code>
							<code class="arb-shortcodes ml-1" title="Trial Amount for Recurring Payments only.">%<?php esc_html_e( 'trial_amount', 'wishlist-member' ); ?></code>
							<code class="arb-shortcodes ml-1" title="Billing Cycle + Trial Cycle for Recurring Payments only.">%<?php esc_html_e( 'total_cycle', 'wishlist-member' ); ?></code>
							<code class="arb-shortcodes ml-1" title="Currency">%<?php esc_html_e( 'currency', 'wishlist-member' ); ?></code>
							<code class="arb-shortcodes ml-1" title="Support Email if set.">%<?php esc_html_e( 'supportemail', 'wishlist-member' ); ?></code>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
