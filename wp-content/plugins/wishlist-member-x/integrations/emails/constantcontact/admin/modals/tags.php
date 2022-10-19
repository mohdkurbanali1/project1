<?php
/**
 * Constant Contact admin UI > Tags modal
 *
 * @package WishListMember/Autoresponders
 */

foreach ( $wpm_levels as $lid => $level ) :
	$level     = (object) $level;
	$level->id = $lid;
	?>
<div
	data-process="modal"
	id="constantcontact-tags-modal-<?php echo esc_attr( $level->id ); ?>-template" 
	data-id="constantcontact-tags-modal-<?php echo esc_attr( $level->id ); ?>"
	data-label="constantcontact-tags-modal-<?php echo esc_attr( $level->id ); ?>"
	data-title="Editing <?php echo esc_attr( $config['name'] ); ?> Settings for <?php echo esc_attr( $level->name ); ?>"
	data-classes="modal-lg"
	data-show-default-footer="1"
	style="display:none">
	<div class="body">
		<ul class="nav nav-tabs">
			<li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#constantcontact-tag-ar-when-added-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Added', 'wishlist-member' ); ?></a></li>
			<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#constantcontact-tag-ar-when-removed-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Removed', 'wishlist-member' ); ?></a></li>
			<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#constantcontact-tag-ar-when-cancelled-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Cancelled', 'wishlist-member' ); ?></a></li>
			<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#constantcontact-tag-ar-when-uncancelled-<?php echo esc_attr( $level->id ); ?>"><?php esc_html_e( 'When Uncancelled', 'wishlist-member' ); ?></a></li>
		</ul>
		<div class="tab-content">
			<div class="row tab-pane active" id="constantcontact-tag-ar-when-added-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Add Tags', 'wishlist-member' ); ?>',
					type : 'select',
					multiple : 'multiple',
					class : 'constantcontact-tags-select',
					style : 'width: 100%',
					name : 'tag_actions[<?php echo esc_attr( $level->id ); ?>][added][add]',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select Tags', 'wishlist-member' ); ?>',
				}
				</template>
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Remove Tags', 'wishlist-member' ); ?>',
					type : 'select',
					multiple : 'multiple',
					class : 'constantcontact-tags-select',
					style : 'width: 100%',
					name : 'tag_actions[<?php echo esc_attr( $level->id ); ?>][added][remove]',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select Tags', 'wishlist-member' ); ?>',
				}
				</template>
			</div>
			<div class="row tab-pane" id="constantcontact-tag-ar-when-removed-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Add Tags', 'wishlist-member' ); ?>',
					type : 'select',
					multiple : 'multiple',
					class : 'constantcontact-tags-select',
					style : 'width: 100%',
					name : 'tag_actions[<?php echo esc_attr( $level->id ); ?>][removed][add]',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select Tags', 'wishlist-member' ); ?>',
				}
				</template>
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Remove Tags', 'wishlist-member' ); ?>',
					type : 'select',
					multiple : 'multiple',
					class : 'constantcontact-tags-select',
					style : 'width: 100%',
					name : 'tag_actions[<?php echo esc_attr( $level->id ); ?>][removed][remove]',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select Tags', 'wishlist-member' ); ?>',
				}
				</template>
			</div>
			<div class="row tab-pane" id="constantcontact-tag-ar-when-cancelled-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Add Tags', 'wishlist-member' ); ?>',
					type : 'select',
					multiple : 'multiple',
					class : 'constantcontact-tags-select',
					style : 'width: 100%',
					name : 'tag_actions[<?php echo esc_attr( $level->id ); ?>][cancelled][add]',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select Tags', 'wishlist-member' ); ?>',
				}
				</template>
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Remove Tags', 'wishlist-member' ); ?>',
					type : 'select',
					multiple : 'multiple',
					class : 'constantcontact-tags-select',
					style : 'width: 100%',
					name : 'tag_actions[<?php echo esc_attr( $level->id ); ?>][cancelled][remove]',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select Tags', 'wishlist-member' ); ?>',
				}
				</template>
			</div>
			<div class="row tab-pane" id="constantcontact-tag-ar-when-uncancelled-<?php echo esc_attr( $level->id ); ?>">
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Add Tags', 'wishlist-member' ); ?>',
					type : 'select',
					multiple : 'multiple',
					class : 'constantcontact-tags-select',
					style : 'width: 100%',
					name : 'tag_actions[<?php echo esc_attr( $level->id ); ?>][uncancelled][add]',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select Tags', 'wishlist-member' ); ?>',
				}
				</template>
				<template class="wlm3-form-group">
				{
					label : '<?php esc_js_e( 'Remove Tags', 'wishlist-member' ); ?>',
					type : 'select',
					multiple : 'multiple',
					class : 'constantcontact-tags-select',
					style : 'width: 100%',
					name : 'tag_actions[<?php echo esc_attr( $level->id ); ?>][uncancelled][remove]',
					column : 'col-12',
					'data-placeholder' : '<?php esc_js_e( 'Select Tags', 'wishlist-member' ); ?>',
				}
				</template>
			</div>
		</div>
	</div>
</div>
	<?php
endforeach;
?>
