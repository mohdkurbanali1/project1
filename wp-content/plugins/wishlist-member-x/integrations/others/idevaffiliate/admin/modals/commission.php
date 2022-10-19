<?php
foreach ( $wpm_levels as $lid => $level ) :
	$level     = (object) $level;
	$level->id = $lid;
	?>
<div
	data-process="modal"
	id="idevaffiliate-lists-modal-<?php echo esc_attr( $level->id ); ?>-template" 
	data-id="idevaffiliate-lists-modal-<?php echo esc_attr( $level->id ); ?>"
	data-label="idevaffiliate-lists-modal-<?php echo esc_attr( $level->id ); ?>"
	data-title="Editing <?php echo esc_attr( $config['name'] ); ?> Settings for <?php echo esc_attr( $level->name ); ?>"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<div class="row">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Currency', 'wishlist-member' ); ?>',
					type : 'select',
					name : 'WLMiDev[wlm_idevcurrency][<?php echo esc_attr( $level->id ); ?>]',
					options : WLM3ThirdPartyIntegration['idevaffiliate'].currencies,
					column : 'col-3',
					tooltip : '<?php esc_js_e( 'Make sure to add the currency and its conversion rate in your iDevAffiliate account-> System Settings-> Localization-> Multicurrency before setting it here.', 'wishlist-member' ); ?>',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Initial Price', 'wishlist-member' ); ?>',
					type : 'text',
					class : '-numeric',
					name : 'WLMiDev[wlm_idevamountfirst][<?php echo esc_attr( $level->id ); ?>]',
					placeholder : '<?php esc_js_e( '0.00', 'wishlist-member' ); ?>',
					'data-mirror-value' : '#idev-values-initial-<?php echo esc_attr( $level->id ); ?>',
					column : 'col-3',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Recurring Price', 'wishlist-member' ); ?>',
					type : 'text',
					class : '-numeric',
					name : 'WLMiDev[wlm_idevamountrecur][<?php echo esc_attr( $level->id ); ?>]',
					placeholder : '<?php esc_js_e( '0.00', 'wishlist-member' ); ?>',
					'data-mirror-value' : '#idev-values-recur-<?php echo esc_attr( $level->id ); ?>',
					column : 'col-6',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Fixed Commission', 'wishlist-member' ); ?>',
					type : 'checkbox',
					name : 'WLMiDev[wlm_idevspecificamount][<?php echo esc_attr( $level->id ); ?>]',
					value : 'yes',
					uncheck_value : '',
					column : 'col-12',
					'data-level' : '<?php echo esc_js( $level->id ); ?>',
					'data-mirror-value' : '#idev-values-fixed-<?php echo esc_attr( $level->id ); ?>',
					class : '-commission-type',
				}
			</template>
		</div>
		<div class="row -commission-fixed-<?php echo esc_attr( $level->id ); ?>">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Initial Commission', 'wishlist-member' ); ?>',
					type : 'text',
					class : '-numeric',
					name : 'WLMiDev[wlm_idevamountpayment][<?php echo esc_attr( $level->id ); ?>]',
					placeholder : '<?php esc_js_e( '0.00', 'wishlist-member' ); ?>',
					'data-mirror-value' : '#idev-values-initialc-<?php echo esc_attr( $level->id ); ?>',
					column : 'col-6',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Recurring Commission', 'wishlist-member' ); ?>',
					type : 'text',
					class : '-numeric',
					name : 'WLMiDev[wlm_idevamountpaymentrecur][<?php echo esc_attr( $level->id ); ?>]',
					placeholder : '<?php esc_js_e( '0.00', 'wishlist-member' ); ?>',
					'data-mirror-value' : '#idev-values-recurc-<?php echo esc_attr( $level->id ); ?>',
					column : 'col-6',
				}
			</template>
		</div>
		<div class="row -commission-idev-<?php echo esc_attr( $level->id ); ?>">
			<div class="col-6">
				<p><em>Payout levels set in iDevAffiliate</em></p>
			</div>
		</div>
	</div>
</div>
	<?php
endforeach;
?>
