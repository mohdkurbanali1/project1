<?php
foreach ( $all_levels as $levels ) :
	foreach ( $levels as $level ) :
		$level = (object) $level;
		?>
<div
	data-process="modal"
	id="products-<?php echo esc_attr( $config['id'] ); ?>-<?php echo esc_attr( $level->id ); ?>-template" 
	data-id="products-<?php echo esc_attr( $config['id'] ); ?>-<?php echo esc_attr( $level->id ); ?>"
	data-label="products-<?php echo esc_attr( $config['id'] ); ?>-<?php echo esc_attr( $level->id ); ?>"
	data-title="Editing <?php echo esc_attr( $config['name'] ); ?> Product for <?php echo esc_attr( $level->name ); ?>"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<div class="row">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Amount', 'wishlist-member' ); ?>',
					name : 'twocheckoutapisettings[connections][<?php echo esc_attr( $level->id ); ?>][rebill_init_amount]',
					'data-mirror-value' : '#twocoapi-products-amount-<?php echo esc_attr( $level->id ); ?>',
					type : 'text',
					column : 'col-6',
					class : '-amount',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Recurring', 'wishlist-member' ); ?>',
					name : 'twocheckoutapisettings[connections][<?php echo esc_attr( $level->id ); ?>][subscription]',
					'data-mirror-value' : '#twocoapi-products-recur-<?php echo esc_attr( $level->id ); ?>',
					type : 'checkbox',
					column : 'col-12',
					value : '1',
					uncheck_value : '',
					'data-target' : '.twoco-api-recurring-<?php echo esc_attr( $level->id ); ?>',
					class : 'twoco-api-recurring-toggle',
				}
			</template>
		</div>
		<div class="row twoco-api-recurring-<?php echo esc_attr( $level->id ); ?>">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Recurring Amount', 'wishlist-member' ); ?>',
					name : 'twocheckoutapisettings[connections][<?php echo esc_attr( $level->id ); ?>][rebill_recur_amount]',
					'data-mirror-value' : '#twocoapi-products-recuramount-<?php echo esc_attr( $level->id ); ?>',
					type : 'text',
					column : 'col-6',
					class : '-amount',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<span style="white-space: nowrap"><?php esc_html_e( 'Interval', 'wishlist-member' ); ?></span>',
					type : 'select',
					name : 'twocheckoutapisettings[connections][<?php echo esc_attr( $level->id ); ?>][rebill_interval]',
					'data-mirror-value' : '#twocoapi-products-interval-<?php echo esc_attr( $level->id ); ?>',
					options : WLM3ThirdPartyIntegration['twoco-api'].rebill_interval,
					style : 'width: 100%',
					column : 'col-2 pr-0',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( '&nbsp;', 'wishlist-member' ); ?>',
					type : 'select',
					name : 'twocheckoutapisettings[connections][<?php echo esc_attr( $level->id ); ?>][rebill_interval_type]',
					'data-mirror-value' : '#twocoapi-products-intervaltype-<?php echo esc_attr( $level->id ); ?>',
					options : WLM3ThirdPartyIntegration['twoco-api'].rebill_interval_type,
					style : 'width: 100%',
					column : 'col-4 pl-0',
				}
			</template>
		</div>
	</div>
</div>
		<?php
	endforeach;
endforeach;
?>
