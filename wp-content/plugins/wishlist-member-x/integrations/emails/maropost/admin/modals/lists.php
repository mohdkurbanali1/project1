<?php
foreach ( $wpm_levels as $lid => $level ) :
	$level     = (object) $level;
	$level->id = $lid;
	?>
<div
	data-process="modal"
	id="maropost-lists-modal-<?php echo esc_attr( $level->id ); ?>-template" 
	data-id="maropost-lists-modal-<?php echo esc_attr( $level->id ); ?>"
	data-label="maropost-lists-modal-<?php echo esc_attr( $level->id ); ?>"
	data-title="Editing <?php echo esc_attr( $config['name'] ); ?> Settings for <?php echo esc_attr( $level->name ); ?>"
	data-show-default-footer="1"
	data-classes="modal-lg"
	style="display:none">
	<div class="body">
		<ul class="nav nav-tabs">
			<li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#maropost-ar-when-added-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Added', 'wishlist-member' ); ?></a></li>
			<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#maropost-ar-when-removed-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Removed', 'wishlist-member' ); ?></a></li>
			<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#maropost-ar-when-cancelled-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Cancelled', 'wishlist-member' ); ?></a></li>
			<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#maropost-ar-when-uncancelled-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Uncancelled', 'wishlist-member' ); ?></a></li>
		</ul>
		<div class="tab-content">
			<div class="row tab-pane active" id="maropost-ar-when-added-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Add to List', 'wishlist-member' ); ?>',
					type : 'select',
					class : 'maropost-lists-select',
					style : 'width: 100%',
					name : 'list_actions[<?php echo esc_attr( $level->id ); ?>][added][add]',
					multiple : 'multiple',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select a List', 'wishlist-member' ); ?>',
					'data-allow-clear' : 1,
				}
				</template>
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Remove from List', 'wishlist-member' ); ?>',
					type : 'select',
					class : 'maropost-lists-select',
					style : 'width: 100%',
					name : 'list_actions[<?php echo esc_attr( $level->id ); ?>][added][remove]',
					multiple : 'multiple',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select a List', 'wishlist-member' ); ?>',
					'data-allow-clear' : 1,
				}
				</template>
			</div>
			<div class="row tab-pane" id="maropost-ar-when-removed-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Add to List', 'wishlist-member' ); ?>',
					type : 'select',
					class : 'maropost-lists-select',
					style : 'width: 100%',
					name : 'list_actions[<?php echo esc_attr( $level->id ); ?>][removed][add]',
					multiple : 'multiple',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select a List', 'wishlist-member' ); ?>',
					'data-allow-clear' : 1,
				}
				</template>
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Remove from List', 'wishlist-member' ); ?>',
					type : 'select',
					class : 'maropost-lists-select',
					style : 'width: 100%',
					name : 'list_actions[<?php echo esc_attr( $level->id ); ?>][removed][remove]',
					multiple : 'multiple',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select a List', 'wishlist-member' ); ?>',
					'data-allow-clear' : 1,
				}
				</template>
			</div>
			<div class="row tab-pane" id="maropost-ar-when-cancelled-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Add to List', 'wishlist-member' ); ?>',
					type : 'select',
					class : 'maropost-lists-select',
					style : 'width: 100%',
					name : 'list_actions[<?php echo esc_attr( $level->id ); ?>][cancelled][add]',
					multiple : 'multiple',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select a List', 'wishlist-member' ); ?>',
					'data-allow-clear' : 1,
				}
				</template>
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Remove from List', 'wishlist-member' ); ?>',
					type : 'select',
					class : 'maropost-lists-select',
					style : 'width: 100%',
					name : 'list_actions[<?php echo esc_attr( $level->id ); ?>][cancelled][remove]',
					multiple : 'multiple',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select a List', 'wishlist-member' ); ?>',
					'data-allow-clear' : 1,
				}
				</template>
			</div>
			<div class="row tab-pane" id="maropost-ar-when-uncancelled-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Add to List', 'wishlist-member' ); ?>',
					type : 'select',
					class : 'maropost-lists-select',
					style : 'width: 100%',
					name : 'list_actions[<?php echo esc_attr( $level->id ); ?>][uncancelled][add]',
					multiple : 'multiple',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select a List', 'wishlist-member' ); ?>',
					'data-allow-clear' : 1,
				}
				</template>
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Remove from List', 'wishlist-member' ); ?>',
					type : 'select',
					class : 'maropost-lists-select',
					style : 'width: 100%',
					name : 'list_actions[<?php echo esc_attr( $level->id ); ?>][uncancelled][remove]',
					multiple : 'multiple',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select a List', 'wishlist-member' ); ?>',
					'data-allow-clear' : 1,
				}
				</template>
			</div>
		</div>
	</div>
</div>
	<?php
endforeach;
?>
