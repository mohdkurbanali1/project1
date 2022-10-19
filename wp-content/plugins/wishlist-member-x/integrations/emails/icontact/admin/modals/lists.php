<?php
foreach ( $wpm_levels as $lid => $level ) :
	$level     = (object) $level;
	$level->id = $lid;
	?>
<div
	data-process="modal"
	id="icontact-lists-modal-<?php echo esc_attr( $level->id ); ?>-template" 
	data-id="icontact-lists-modal-<?php echo esc_attr( $level->id ); ?>"
	data-label="icontact-lists-modal-<?php echo esc_attr( $level->id ); ?>"
	data-title="Editing <?php echo esc_attr( $config['name'] ); ?> Settings for <?php echo esc_attr( $level->name ); ?>"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<div class="row">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Contact List', 'wishlist-member' ); ?>',
					type : 'select',
					class : 'icontact-lists',
					style : 'width: 100%',
					name : 'icID[<?php echo esc_attr( $level->id ); ?>]',
					multiple : 'multiple',
					column : 'col-12',
					'data-mirror-value' : '#icontact-lists-<?php echo esc_attr( $level->id ); ?>',
				}
			</template>
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Log Unsubscribes', 'wishlist-member' ); ?>',
					name  : 'iclog[<?php echo esc_attr( $level->id ); ?>]',
					value : '1',
					uncheck_value : '',
					type  : 'checkbox',
					column : 'col-12',
					'data-mirror-value' : '#icontact-unsubscribe-<?php echo esc_attr( $level->id ); ?>',
				}
			</template>
		</div>
	</div>
</div>
	<?php
endforeach;
?>
