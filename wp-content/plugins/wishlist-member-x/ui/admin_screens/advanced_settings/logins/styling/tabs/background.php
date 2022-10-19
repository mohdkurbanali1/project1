<div class="horizontal-tabs">
	<div class="row no-gutters">
		<div class="col-12 col-md-auto">
			<div class="horizontal-tabs-sidebar">
				<ul class="nav nav-tabs -h-tabs flex-column" id="xys">
					<li class="active nav-item"><a class="active nav-link" data-toggle="tab" href="#" data-target="#background-image"><?php esc_html_e( 'Image', 'wishlist-member' ); ?></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#" data-target="#background-color"><?php esc_html_e( 'Color', 'wishlist-member' ); ?></a></li>
				</ul>
			</div>
		</div>
		<div class="col">
			<div class="tab-content">
				<div class="tab-pane active" id="background-image">
					<div class="row">
						<div class="col">
							<div class="row">
								<template class="wlm3-form-group">
									{
										type : 'wlm3media',
										label : '<?php esc_js_e( 'Image', 'wishlist-member' ); ?>',
										name : 'login_styling_custom_bgimage',
										value : <?php echo wp_json_encode( wlm_or( $this->get_option( 'login_styling_custom_bgimage' ), '' ) ); ?>,
										placeholder : '<?php esc_js_e( 'Theme Default', 'wishlist-member' ); ?>',
										column : 'col-12',
										group_class : 'img-uploader-big'
									}
								</template>
							</div>
						</div>
						<div class="col-md-4">

							<div class="row">
								<template class="wlm3-form-group">
									{
										type : 'select',
										label : '<?php esc_js_e( 'Position', 'wishlist-member' ); ?>',
										name : 'login_styling_custom_bgposition',
										value : <?php echo json_encode( $this->get_option( 'login_styling_custom_bgposition' ) ); ?>,
										options : [
											{ value : '', text : '<?php esc_attr_e( 'Theme Default', 'wishlist-member' ); ?>' },
											{ value : 'center center', text : '<?php esc_attr_e( 'Centered', 'wishlist-member' ); ?>' },
											{ value : 'left top', text : '<?php esc_attr_e( 'Top Left', 'wishlist-member' ); ?>' },
											{ value : 'right top', text : '<?php esc_attr_e( 'Top Right', 'wishlist-member' ); ?>' },
											{ value : 'left bottom', text : '<?php esc_attr_e( 'Bottom Left', 'wishlist-member' ); ?>' },
											{ value : 'right bottom', text : '<?php esc_attr_e( 'Bottom Right', 'wishlist-member' ); ?>' },
											{ value : 'center top', text : '<?php esc_attr_e( 'Top Center', 'wishlist-member' ); ?>' },
											{ value : 'right center', text : '<?php esc_attr_e( 'Right Center', 'wishlist-member' ); ?>' },
											{ value : 'center bottom', text : '<?php esc_attr_e( 'Bottom Center', 'wishlist-member' ); ?>' },
											{ value : 'left center', text : '<?php esc_attr_e( 'Left Center', 'wishlist-member' ); ?>' }
										],
										column: 'col-12',
										style: 'width: 100%'
									}
								</template>
								<template class="wlm3-form-group">
									{
										type : 'select',
										label : '<?php esc_js_e( 'Repeat', 'wishlist-member' ); ?>',
										name : 'login_styling_custom_bgrepeat',
										value : <?php echo json_encode( $this->get_option( 'login_styling_custom_bgrepeat' ) ); ?>,
										options : [
											{ value : '', text : '<?php esc_attr_e( 'Theme Default', 'wishlist-member' ); ?>' },
											{ value : 'no-repeat', text : '<?php esc_attr_e( 'Do Not Repeat', 'wishlist-member' ); ?>' },
											{ value : 'repeat', text : '<?php esc_attr_e( 'Repeat', 'wishlist-member' ); ?>' },
											{ value : 'repeat-x', text : '<?php esc_attr_e( 'Repeat Horizontally', 'wishlist-member' ); ?>' },
											{ value : 'repeat-y', text : '<?php esc_attr_e( 'Repeat Vertically', 'wishlist-member' ); ?>' },
											{ value : 'space', text : '<?php esc_attr_e( 'Smart Spacing', 'wishlist-member' ); ?>' },
										],
										column: 'col-12',
										style: 'width: 100%'
									}
								</template>
								<template class="wlm3-form-group">
									{
										type : 'select',
										label : '<?php esc_js_e( 'Size', 'wishlist-member' ); ?>',
										name : 'login_styling_custom_bgsize',
										value : <?php echo json_encode( $this->get_option( 'login_styling_custom_bgsize' ) ); ?>,
										options : [
											{ value : '', text : '<?php esc_attr_e( 'Theme Default', 'wishlist-member' ); ?>' },
											{ value : 'auto', text : '<?php esc_attr_e( 'Original Size', 'wishlist-member' ); ?>' },
											{ value : 'cover', text : '<?php esc_attr_e( 'Fill', 'wishlist-member' ); ?>' },
											{ value : 'contain', text : '<?php esc_attr_e( 'Fit', 'wishlist-member' ); ?>' },
											{ value : '100% 100%', text : '<?php esc_attr_e( 'Stretch', 'wishlist-member' ); ?>' },
										],
										column: 'col-12',
										style: 'width: 100%'
									}
								</template>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="background-color">
					<div class="row">
						<template class="wlm3-form-group">
							{
								type : 'text',
								label : '<?php esc_js_e( 'Background Color', 'wishlist-member' ); ?>',
								name : 'login_styling_custom_bgcolor',
								value : <?php echo wp_json_encode( wlm_or( $this->get_option( 'login_styling_custom_bgcolor' ), '' ) ); ?>,
								column: 'col-md-4',
								placeholder : '<?php esc_js_e( 'Theme Default', 'wishlist-member' ); ?>',
								class : 'wlm3colorpicker'
							}
						</template>
					</div>
					<div class="row">
						<template class="wlm3-form-group">
							{
								type : 'select',
								label : '<?php esc_js_e( 'Background Blend Mode', 'wishlist-member' ); ?>',
								name : 'login_styling_custom_bgblend',
								value : <?php echo wp_json_encode( wlm_or( $this->get_option( 'login_styling_custom_bgblend' ), '' ) ); ?>,
								options : [
									{ value : '', text : '<?php esc_attr_e( 'Theme Default', 'wishlist-member' ); ?>' },
									{ value : 'normal', text : '<?php esc_attr_e( 'None', 'wishlist-member' ); ?>' },
									{ value : 'multiply', text : '<?php esc_attr_e( 'Multiply', 'wishlist-member' ); ?>' },
									{ value : 'overlay', text : '<?php esc_attr_e( 'Overlay', 'wishlist-member' ); ?>' },
									{ value : 'luminosity', text : '<?php esc_attr_e( 'Luminosity', 'wishlist-member' ); ?>' },
								],
								column: 'col-md-4',
								style: 'width: 100%'
							}
						</template>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<br>
