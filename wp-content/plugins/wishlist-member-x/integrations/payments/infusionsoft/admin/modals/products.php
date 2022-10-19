<?php
foreach ( $all_levels as $ltype => $wpm_level ) :
	foreach ( $wpm_levels as $lid => $level ) :
		$level     = (object) $level;
		$level->id = $lid;
		?>
<div
	data-process="modal"
	id="infusionsoft-products-modal-<?php echo esc_attr( $level->id ); ?>-template" 
	data-id="infusionsoft-products-modal-<?php echo esc_attr( $level->id ); ?>"
	data-label="infusionsoft-products-modal-<?php echo esc_attr( $level->id ); ?>"
	data-title="Editing <?php echo esc_attr( $config['name'] ); ?> Tags for <?php echo esc_attr( $level->name ); ?>"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<div class="row">
			<div class="col-md-12">	
				<ul class="nav nav-tabs">
					<?php if ( '__levels__' === $ltype ) : ?>
					<li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-lvl-when-added-<?php echo esc_attr( $level->id ); ?>">When Added</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-lvl-when-removed-<?php echo esc_attr( $level->id ); ?>">When Removed</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-lvl-when-cancelled-<?php echo esc_attr( $level->id ); ?>">When Cancelled</a></li>
					<?php else : ?>
					<li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-ppp-when-added-<?php echo esc_attr( $level->id ); ?>">When Added</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-ppp-when-removed-<?php echo esc_attr( $level->id ); ?>">When Removed</a></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
		<div class="tab-content">
			<?php if ( '__levels__' === $ltype ) : ?>
			<div class="row tab-pane active in" id="infusionsoft-lvl-when-added-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Apply Tags:', 'wishlist-member' ); ?>',
						column : 'col-12',
						type : 'select',
						multiple : 'multiple',
						name : 'istags_add_app[<?php echo esc_attr( $level->id ); ?>][]',
						class : 'infusionsoft-tags',
						grouped : true,
						'data-placeholder' : '<?php esc_js_e( 'Select tags...', 'wishlist-member' ); ?>',
						style : 'width: 100%',
					}
				</template>
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Remove Tags:', 'wishlist-member' ); ?>',
						column : 'col-12',
						type : 'select',
						multiple : 'multiple',
						name : 'istags_add_rem[<?php echo esc_attr( $level->id ); ?>][]',
						class : 'infusionsoft-tags',
						grouped : true,
						'data-placeholder' : '<?php esc_js_e( 'Select tags...', 'wishlist-member' ); ?>',
						style : 'width: 100%',
					}
				</template>			
			</div>
			<div class="row tab-pane" id="infusionsoft-lvl-when-removed-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Apply Tags:', 'wishlist-member' ); ?>',
						column : 'col-12',
						type : 'select',
						multiple : 'multiple',
						name : 'istags_remove_app[<?php echo esc_attr( $level->id ); ?>][]',
						class : 'infusionsoft-tags',
						grouped : true,
						'data-placeholder' : '<?php esc_js_e( 'Select tags...', 'wishlist-member' ); ?>',
						style : 'width: 100%',
					}
				</template>
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Remove Tags:', 'wishlist-member' ); ?>',
						column : 'col-12',
						type : 'select',
						multiple : 'multiple',
						name : 'istags_remove_rem[<?php echo esc_attr( $level->id ); ?>][]',
						class : 'infusionsoft-tags',
						grouped : true,
						'data-placeholder' : '<?php esc_js_e( 'Select tags...', 'wishlist-member' ); ?>',
						style : 'width: 100%',
					}
				</template>
			</div>
			<div class="row tab-pane" id="infusionsoft-lvl-when-cancelled-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Apply Tags:', 'wishlist-member' ); ?>',
						column : 'col-12',
						type : 'select',
						multiple : 'multiple',
						name : 'istags_cancelled_app[<?php echo esc_attr( $level->id ); ?>][]',
						class : 'infusionsoft-tags',
						grouped : true,
						'data-placeholder' : '<?php esc_js_e( 'Select tags...', 'wishlist-member' ); ?>',
						style : 'width: 100%',
					}
				</template>
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Remove Tags:', 'wishlist-member' ); ?>',
						column : 'col-12',
						type : 'select',
						multiple : 'multiple',
						name : 'istags_cancelled_rem[<?php echo esc_attr( $level->id ); ?>][]',
						class : 'infusionsoft-tags',
						grouped : true,
						'data-placeholder' : '<?php esc_js_e( 'Select tags...', 'wishlist-member' ); ?>',
						style : 'width: 100%',
					}
				</template>
			</div>
			<?php else : ?>
			<div class="row tab-pane active in" id="infusionsoft-ppp-when-added-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Apply Tags:', 'wishlist-member' ); ?>',
						column : 'col-12',
						type : 'select',
						multiple : 'multiple',
						name : 'istagspp_add_app[<?php echo esc_attr( $level->id ); ?>][]',
						class : 'infusionsoft-tags',
						grouped : true,
						'data-placeholder' : '<?php esc_js_e( 'Select tags...', 'wishlist-member' ); ?>',
						style : 'width: 100%',
					}
				</template>
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Remove Tags:', 'wishlist-member' ); ?>',
						column : 'col-12',
						type : 'select',
						multiple : 'multiple',
						name : 'istagspp_add_rem[<?php echo esc_attr( $level->id ); ?>][]',
						class : 'infusionsoft-tags',
						grouped : true,
						'data-placeholder' : '<?php esc_js_e( 'Select tags...', 'wishlist-member' ); ?>',
						style : 'width: 100%',
					}
				</template>			
			</div>
			<div class="row tab-pane" id="infusionsoft-ppp-when-removed-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Apply Tags:', 'wishlist-member' ); ?>',
						column : 'col-12',
						type : 'select',
						multiple : 'multiple',
						name : 'istagspp_remove_app[<?php echo esc_attr( $level->id ); ?>][]',
						class : 'infusionsoft-tags',
						grouped : true,
						'data-placeholder' : '<?php esc_js_e( 'Select tags...', 'wishlist-member' ); ?>',
						style : 'width: 100%',
					}
				</template>
				<template class="wlm3-form-group">
					{
						label : '<?php esc_js_e( 'Remove Tags:', 'wishlist-member' ); ?>',
						column : 'col-12',
						type : 'select',
						multiple : 'multiple',
						name : 'istagspp_remove_rem[<?php echo esc_attr( $level->id ); ?>][]',
						class : 'infusionsoft-tags',
						grouped : true,
						'data-placeholder' : '<?php esc_js_e( 'Select tags...', 'wishlist-member' ); ?>',
						style : 'width: 100%',
					}
				</template>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
		<?php
endforeach;
endforeach;
?>
