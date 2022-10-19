<?php
foreach ( $wpm_levels as $lid => $level ) :
	$level     = (object) $level;
	$level->id = $lid;
	?>
<div
	data-process="modal"
	id="gotomeetingapi-lists-modal-<?php echo esc_attr( $level->id ); ?>-template" 
	data-id="gotomeetingapi-lists-modal-<?php echo esc_attr( $level->id ); ?>"
	data-label="gotomeetingapi-lists-modal-<?php echo esc_attr( $level->id ); ?>"
	data-title="Editing <?php echo esc_attr( $config['name'] ); ?> Settings for <?php echo esc_attr( $level->name ); ?>"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<div class="row">
			<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Webinar', 'wishlist-member' ); ?>',
					type : 'select',
					class : 'gotomeetingapi-webinars',
					style : 'width: 100%',
					name : 'webinar[gotomeetingapi][<?php echo esc_attr( $level->id ); ?>]',
					column : 'col-12',
					'data-allow-clear' : 'true',
					'data-placeholder' : '<?php esc_js_e( 'Select a Webinar', 'wishlist-member' ); ?>',
					'data-mirror-value' : '#gotomeetingapi-lists-<?php echo esc_attr( $level->id ); ?>',
				}
			</template>
		</div>
	</div>
</div>
	<?php
endforeach;
?>
