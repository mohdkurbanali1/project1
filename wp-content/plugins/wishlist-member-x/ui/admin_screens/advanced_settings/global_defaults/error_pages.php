<?php
	$system_pages_arr = array(
		'error' => array(
			'title'   => 'Error Pages',
			'options' => array(
				'non_members_error_page'    => array(
					'label'        => 'Non-Members',
					'tooltip'      => 'The Non-Members Error Page will be displayed to Non-Members or those who are not logged in if they attempt to access protected content.',
					'tooltip_size' => '',
				),
				'wrong_level_error_page'    => array(
					'label'        => 'Wrong Membership Level',
					'tooltip'      => "The Wrong Membership Level Error Page will be displayed to logged in Members if they attempt to access protected content assigned to a Membership Level they don't belong to.",
					'tooltip_size' => 'md',
				),
				'membership_cancelled'      => array(
					'label'        => 'Membership Cancelled',
					'tooltip'      => 'The Membership Cancelled Error Page will be displayed to a Member if their Membership Level has been cancelled. This can be done manually by a site admin or by a supported payment provider as listed in the Setup > Integrations > Payment Providers section of WishList Member.',
					'tooltip_size' => 'md',
				),
				'membership_expired'        => array(
					'label'        => 'Membership Expired',
					'tooltip'      => 'The Membership Expired Error Page will be displayed to logged in Members if they attempt to access protected content assigned to a Membership Level they have expired from.',
					'tooltip_size' => 'md',
				),
				'membership_expired'        => array(
					'label'        => 'Membership Expired',
					'tooltip'      => 'The Membership Expired Error Page will be displayed to logged in Members if they attempt to access protected content assigned to a Membership Level they have expired from.',
					'tooltip_size' => 'md',
				),
				'duplicate_post_error_page' => array(
					'toggle'       => 'PreventDuplicatePosts',
					'label'        => 'Prevent duplicate shopping cart registrations',
					'tooltip'      => 'When enabled and a Member has registered before and then attempts to use the same info to register again (username, password, etc.) WishList Member will NOT create a new Membership, but will update the existing Membership.',
					'tooltip_size' => 'md',
				),
			),
		),
	);
	$_pages           = get_pages( 'exclude=' . implode( ',', $this->exclude_pages( array(), true ) ) );
	?>
<div class="content-wrapper">
	<?php foreach ( $system_pages_arr as $system_pages ) : ?>
		<?php foreach ( $system_pages['options'] as $key => $option ) : ?>
			<?php
				$page_type = $this->get_option( $key . '_type' );
			if ( false === $page_type ) {
				$p = $this->get_option( $key . '_internal' );
				if ( $p ) {
					$page_type = 'internal';
				} else {
					$_pages_url = $this->get_option( $key );
					$page_type  = $_pages_url ? 'url' : 'text';
				}
			}

				$button_disable = '';
			if ( ! empty( $option['toggle'] ) ) {
				if ( ! $this->get_option( $option['toggle'] ) ) {
					$button_disable = '-disable';
				}
			}
			?>

			<div class="row">
				<div class="col-sm-7 col-md-6 col-lg-6 col-xxxl-3 col-xxl-6">
					<div class="form-group">
						<?php if ( empty( $option['toggle'] ) ) : ?>
							<label>
								<span class="title-label"><?php echo esc_html( $option['label'] ); ?></span>
								<?php $this->tooltip( $option['tooltip'], $option['tooltip_size'] ); ?>
							</label>
						<?php else : ?>
							<template class="wlm3-form-group">
								{
									label : '<?php echo esc_js( $option['label'] ); ?>',
									name  : '<?php echo esc_js( $option['toggle'] ); ?>',
									value : '1',
									uncheck_value : '',
									class : 'auto-save',
									type  : 'toggle-adjacent-disable',
									tooltip_size: '<?php echo esc_js( $option['tooltip_size'] ); ?>',
									tooltip : '<?php echo esc_js( $option['tooltip'] ); ?>',
									<?php
									if ( $this->get_option( $option['toggle'] ) ) {
										echo 'checked : "checked"';}
									?>
								}
							</template>
						<?php endif; ?>
					</div>
				</div>
				<div class="col-sm-5 col-md-4">
					<a href="#" class="btn -primary configure-btn -condensed <?php echo esc_attr( $button_disable ); ?>" ptype="<?php echo esc_attr( $page_type ); ?>" key="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $option['label'] ); ?>">
						<i class="wlm-icons">settings</i>
						<span><?php esc_html_e( 'Configure', 'wishlist-member' ); ?></span>
					</a>
				</div>
			</div>
<!-- 			<div class="row">
				<div class="col-xxxl-3 col-xxl-6 col-lg-6 col-md-6">
					<div class="form-group">
						<?php if ( empty( $option['toggle'] ) ) : ?>
							<label>
								<span class="title-label"><?php echo esc_html( $option['label'] ); ?></span>
								<?php $this->tooltip( $option['tooltip'], $option['tooltip_size'] ); ?>
							</label>
						<?php else : ?>
							<template class="wlm3-form-group">
								{
									label : '<?php echo esc_js( $option['label'] ); ?>',
									name  : '<?php echo esc_js( $option['toggle'] ); ?>',
									value : '1',
									uncheck_value : '',
									class : 'auto-save',
									type  : 'toggle-adjacent-disable',
									tooltip_size: '<?php echo esc_js( $option['tooltip_size'] ); ?>',
									tooltip : '<?php echo esc_js( $option['tooltip'] ); ?>',
									<?php
									if ( $this->get_option( $option['toggle'] ) ) {
										echo 'checked : "checked"';}
									?>
								}
							</template>
						<?php endif; ?>
					</div>
				</div>
				<div class="col-md-4">
					<a href="#" class="btn -primary configure-btn -condensed <?php echo esc_attr( $button_disable ); ?>" ptype="<?php echo esc_attr( $page_type ); ?>" key="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $option['label'] ); ?>">
						<i class="wlm-icons">settings</i>
						<span><?php esc_html_e( 'Configure', 'wishlist-member' ); ?></span>
					</a>
				</div>
			</div> -->
		<?php endforeach; ?>
	<?php endforeach; ?>
</div>

<!-- Modal -->
<div id="configure-pages" data-id="configure-pages" data-label="configure-pages-label" data-title="Configure System Pages" data-classes="modal-lg" style="display:none">
	<div class="body">
		<div class="row settings-content">
			<div class="col-md-12">
				<h4 class="mb-3"><?php esc_html_e( 'Select one of the following options:', 'wishlist-member' ); ?></h4>
			</div>
			<div class="col-md-2 col-border-right">
				<template class="wlm3-form-group">
				  {
					label : '<?php esc_js_e( 'Page', 'wishlist-member' ); ?>',
					name  : 'sp',
					value : 'internal',
					type  : 'radio',
					id : 'sp-internal',
					tooltip : '<?php esc_js_e( 'This option can be used in order to select a specific page created in WordPress.', 'wishlist-member' ); ?>'
				  }
				</template>
				<template class="wlm3-form-group">
				  {
					label : '<?php esc_js_e( 'Message', 'wishlist-member' ); ?>',
					name  : 'sp',
					value : 'text',
					type  : 'radio',
					id : 'sp-text',
					tooltip : '<?php esc_js_e( 'This option can be used in order to specify a message that will automatically be displayed by WishList Member.', 'wishlist-member' ); ?>'
				  }
				</template>
				<template class="wlm3-form-group">
				  {
					label : '<?php esc_js_e( 'URL', 'wishlist-member' ); ?>',
					name  : 'sp',
					value : 'url',
					type  : 'radio',
					id : 'sp-url',
					tooltip : '<?php esc_js_e( 'This option can be used in order to redirect to a specific URL that may be located or hosted outside of your WordPress site.', 'wishlist-member' ); ?>'
				  }
				</template>
			</div>
			<div class="col-md-10">
				<input type="hidden" name="type" class="system-page-type" value="" />
				<div class="sp-text-content">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group mb-2">
								<textarea class="form-control system-page-text" name="text" cols="30" rows="10" placeholder="Your message" required></textarea>
							</div>
						</div>
						<div class="col-md-12">
							<button class="btn -default -condensed page-message-reset-button">Reset to Default</button>
							<template class="wlm3-form-group">{
								type : 'select',
								column : 'col-md-5 pull-right no-margin no-padding',
								'data-placeholder' : '<?php esc_js_e( 'Insert Merge Codes', 'wishlist-member' ); ?>',
								group_class : 'shortcode_inserter mb-0',
								style : 'width: 100%',
								options : get_merge_codes([{value : '[password]', text : 'Password'}]),
								grouped: true,
								class : 'insert_text_at_caret',
								'data-target' : '.system-page-text',
							}</template>
						</div>
					</div>
				</div>
				<div class="sp-page-content" style="display:none">
					<div class="row">
						<div class="col-md-8">
						  <div class="form-group">
							<select class="form-control wlm-select wlm-select-pages system-page-internal" name="internal" style="width: 100%" data-placeholder="Select a page">
							  <option></option>
							  <?php foreach ( $_pages as $p ) : ?>
								<option value="<?php echo esc_attr( $p->ID ); ?>" ><?php echo esc_html( $p->post_title ); ?></option>
							  <?php endforeach; ?>
							</select>
						  </div>
						</div>
						<div class="col-md-4">
						  <a href="javascript:void(0);" class="btn -success -icon-only add-page-btn" style="margin-bottom: 15px" title="Add a page">
							<i class="wlm-icons">add</i>
						  </a>
						</div>
					</div>
					<div class="row create-page-holder">
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" class="form-control " name="page_title" value="" placeholder="Page title" required="required" />
							</div>
						</div>
						<div class="col-md-4">
							<a href="javascript:void(0);" class="btn -primary -condensed -no-icon create-page-btn" title="Create Page">
								<span><?php esc_html_e( 'Create Page', 'wishlist-member' ); ?></span>
							</a>
							<a href="javascript:void(0);" class="btn -bare -condensed -no-icon hide-create-page-btn" title="Create Page">
								<i class="wlm-icons">close</i>
							</a>
						</div>
					</div>
				</div>
				<div class="sp-url-content" style="display:none">
					<div class="row">
						<div class="col-md-10">
							<div class="form-group ">
								<input type="text" class="form-control system-page-url" name="url" value="" placeholder="Specify the URL" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="footer">
	<button type="button" class="btn -bare" data-dismiss="modal">
		<span><?php esc_html_e( 'Close', 'wishlist-member' ); ?></span>
	</button>
	<button type="button" class="btn -primary save-button">
		<i class="wlm-icons">save</i>
		<span><?php esc_html_e( 'Save', 'wishlist-member' ); ?></span>
	</button>
	<button class="-close btn -success -modal-btn save-button">
		<i class="wlm-icons">save</i>
		<span><?php esc_html_e( 'Save & Close', 'wishlist-member' ); ?></span>
	</button>
	</div>
</div>
