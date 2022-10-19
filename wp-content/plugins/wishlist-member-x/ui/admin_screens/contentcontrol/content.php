<?php
$wl           = wlm_get_data()['wl'];
$wl           = explode( '/', $wl );
$content_type = $wl[1];
$post_types   = array(
	'post' => 'Posts',
	'page' => 'Pages',
);
$args         = array( '_builtin' => false );
$cpost_types  = get_post_types( $args, 'objects' );
foreach ( (array) $cpost_types as $key => $value ) {
	$post_types[ $key ] = $value->label;
}
$content_type       = array_key_exists( $content_type, $post_types ) ? $content_type : 'post';
$is_custom_posttype = ! in_array( $content_type, array( 'page', 'post' ) );
// check if content is heirarchical, including custom post type
$is_heirarchical = 'page' === $content_type ? true : false;
$is_heirarchical = $is_custom_posttype ? is_post_type_hierarchical( $content_type ) : $is_heirarchical;

$is_protection_enabled = $this->post_type_enabled( $content_type );

$support_categories = 'post' === $content_type ? true : false;
if ( 'post' !== $content_type && 'page' !== $content_type ) {
	$p = get_post_type_object( $content_type );
	if ( in_array( 'category', $p->taxonomies ) ) {
		$support_categories = true;
	}
}

$perpage = $this->get_option( 'content-tab-perpage' );
if ( is_numeric( wlm_get_data()['perpage'] ) || ! $perpage || 'Show All' == wlm_get_data()['perpage'] ) {
	$perpage = wlm_get_data()['perpage'];
	if ( ! $perpage ) {
		$perpage = $this->pagination_items[1];
	}
	if ( ! in_array( $perpage, $this->pagination_items ) ) {
		$perpage = $this->pagination_items[1];
	}
	// we only save if not show all
	if ( 'Show All' !== $perpage ) {
		$this->save_option( 'content-tab-perpage', $perpage );
	}
}
$perpage = 'Show All' === $perpage ? 999999999 : $perpage;


$s_currentpage = isset( wlm_get_data()['paged'] ) ? wlm_get_data()['paged'] - 1 : -1;
if ( $s_currentpage < 0 ) {
	$s_currentpage = 0;
}
$s_offset      = $s_currentpage * $perpage;
$s_currentpage = $s_offset / $perpage + 1;

$exclude_pages = $this->exclude_pages( array() );

$args = array(
	'post_type'    => $content_type,
	'post__not_in' => $exclude_pages,
);

$args['offset'] = $s_offset;

$args['orderby'] = wlm_or( wlm_trim( wlm_get_data()['orderby'] ), 'post_title' );
$args['order']   = wlm_or( wlm_trim( wlm_get_data()['order'] ), 'asc' );

$sort_name      = 'desc';
$sort_name_icon = '';
if ( 'post_title' == $args['orderby'] ) {
	$sort_name      = 'desc' == strtolower( $args['order'] ) ? 'asc' : 'desc';
	$sort_name_icon = 'desc' === $sort_name ? 'arrow_drop_up' : 'arrow_drop_down';
}

$sort_date      = 'desc';
$sort_date_icon = '';
if ( 'post_date' == $args['orderby'] ) {
	$sort_date      = 'desc' == strtolower( $args['order'] ) ? 'asc' : 'desc';
	$sort_date_icon = 'desc' === $sort_date ? 'arrow_drop_up' : 'arrow_drop_down';
}

$args['posts_per_page'] = $perpage;
if ( isset( wlm_request_data()['wlm_post_search_term'] ) ) {
	$args['s'] = wlm_request_data()['wlm_post_search_term'];
}

// used for $page_href below
$url_keys = array_intersect_key(
	wlm_get_data( true ),
	array(
		'wlm_post_search_term' => '',
		'orderby'              => '',
		'order'                => '',
	)
);
$url_keys = array_filter( $url_keys, 'strlen' );


$post_children = array();
$post_parents  = array();
$postids       = array();
if ( $is_heirarchical && ! count( $url_keys ) ) {
	$args['sort_order']  = $args['order'];
	$args['sort_column'] = $args['orderby'];
	$args['exclude']     = implode( ',', $exclude_pages );

	$pages_list    = get_pages( $args );
	$total_items   = count( $pages_list );
	$content_items = array_slice( $pages_list, $s_offset, $perpage );

	// lets do it heirarchically
	if ( null == wlm_get_data()['orderby'] ) {
		// get all the parents
		foreach ( $content_items as $key => $value ) {
			if ( $value->post_parent ) {
				$post_parents[] = $value->post_parent;
			}
			$postids[] = $value->ID;
		}

		// check the parent if present, if not, add it
		foreach ( $post_parents as $parent_id ) {
			if ( ! in_array( $parent_id, $postids ) ) {
				array_unshift( $content_items, get_post( $parent_id ) );
				$postids[] = $parent_id;
			}
		}

		// get all the children
		foreach ( $content_items as $key => $value ) {
			if ( $value->post_parent ) {
				$post_children[ $value->post_parent ][] = $value;
				unset( $content_items[ $key ] ); // remove the children from list, they be added seperately
			}
		}
	}
} else { // for non heirarchical post types
	$the_posts       = new WP_Query( $args );
	$content_items   = $the_posts->posts;
	$total_items     = $the_posts->found_posts;
	$is_heirarchical = count( $url_keys );
}


$total_pages = ceil( $total_items / $perpage );
++$s_offset;

// Get Membership Levels
$page_href  = "?page={$this->MenuID}&wl=" . ( isset( wlm_get_data()['wl'] ) ? wlm_get_data()['wl'] : "contentcontrol/{$content_type}" );
$page_href .= '&' . build_query( $url_keys );

$wpm_levels = $this->get_option( 'wpm_levels' );

$tbl_collapse = $this->get_option( 'wlm_toggle_contentcontrol_table' );
$tbl_collapse = $tbl_collapse ? $tbl_collapse : 'expand';
$icollapse1   = 'expand' === $tbl_collapse ? '' : 'd-none';
$icollapse2   = 'expand' === $tbl_collapse ? 'd-none' : '';

function display_items( $that, $item, $post_children, $content_type, $icollapse1, $icollapse2, $is_heirarchical, $support_categories ) {
	$scheduler = $that->content_control->scheduler ? $that->content_control->scheduler : null;
	$archiver  = $that->content_control->archiver ? $that->content_control->archiver : null;
	$manager   = $that->content_control->manager ? $that->content_control->manager : null;

	$wpm_levels = $that->get_option( 'wpm_levels' );
	if ( isset( $post_children[ $item->ID ] ) ) {
		include $that->plugindir3 . '/ui/admin_screens/contentcontrol/content/items.php';
		foreach ( $post_children[ $item->ID ] as $key => $value ) {
			display_items( $that, $value, $post_children, $content_type, $icollapse1, $icollapse2, $is_heirarchical, $support_categories );
		}
	} else {
		include $that->plugindir3 . '/ui/admin_screens/contentcontrol/content/items.php';
	}
}
?>
<div class="page-header">
	<div class="row">
		<div class="col-md-9 col-sm-9 col-xs-8">
			<h2 class="page-title pull-left">
				<?php echo esc_html( $post_types[ $content_type ] ); ?>
			</h2>
		</div>
		<div class="col-md-3 col-sm-3 col-xs-4">
			<?php require $this->plugindir3 . '/helpers/header-icons.php'; ?>
		</div>
	</div>
</div>

<?php
	$allow_scheduler = $this->get_option( 'enable_content_scheduler' ) && $is_protection_enabled ? true : false;
	$allow_archiver  = $this->get_option( 'enable_content_archiver' ) && $is_protection_enabled ? true : false;
	$allow_manager   = $this->get_option( 'enable_content_manager' ) ? true : false;
?>

<div class="header-tools -no-border no-padding">
	<div class="row">
		<div class="col-sm-12 col-md-3 col-lg-3 mb-sm-1">
			<div class="form-group">
				<label class="sr-only" for=""><?php esc_html_e( 'Actions', 'wishlist-member' ); ?></label>
				<select class="form-control wlm-select blk-actions" name="" id="" style="width: 100%">
					<option value="">- Select an Action -</option>
					<optgroup label="Content Scheduler">
						<?php if ( $allow_scheduler ) : ?>
							<option value="scheduler">Set Content Schedule (Drip)</option>
							<option value="remove_scheduler">Remove Content Schedule</option>
						<?php else : ?>
							<?php if ( ! $this->get_option( 'enable_content_scheduler' ) ) : ?>
								<option value="" disabled="true">Content Scheduler is disabled</option>
							<?php else : ?>
								<option value="" disabled="true">Enable Content Protection for this Post Type first</option>
							<?php endif; ?>
						<?php endif; ?>
					</optgroup>
					<optgroup label="Content Archiver">
						<?php if ( $allow_archiver ) : ?>
							<option value="archiver">Set Archive Date</option>
							<option value="remove_archiver">Remove Archive Date</option>
						<?php else : ?>
							<?php if ( ! $this->get_option( 'enable_content_archiver' ) ) : ?>
								<option value="" disabled="true">Content Archiver is disabled</option>
							<?php else : ?>
								<option value="" disabled="true">Enable Content Protection for this Post Type first</option>
							<?php endif; ?>
						<?php endif; ?>
					</optgroup>
					<optgroup label="Content Manager">
						<?php if ( $allow_manager ) : ?>
							<option value="manager">Add Content Manager Date</option>
							<option value="remove_manager">Remove Content Manager Date</option>
						<?php else : ?>
							<?php if ( ! $this->get_option( 'enable_content_manager' ) ) : ?>
								<option value="" disabled="true">Content Manager is disabled</option>
							<?php else : ?>
								<option value="" disabled="true">Enable Content Protection for this Post Type first</option>
							<?php endif; ?>
						<?php endif; ?>
					</optgroup>
				</select>
			</div>
		</div>
		<div id="AdvancedSearchForm" class="search-bar col-sm-12 col-lg-9 col-md-9 mb-sm-1">
			<form method="get" target="_parent" id="search-form" action="?<?php echo esc_attr( $this->QueryString() ); ?>">
				<?php
					// lets add the querystring in hidden fields
					// this is needed since we are passing form tru GET
					$retain_keys = array( 'page', 'wl' );
				foreach ( wlm_get_data( true ) as $key => $content ) {
					if ( in_array( $key, $retain_keys ) ) {
						echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $content ) . '" />';
					}
				}
				?>
				<div class="input-group">
					<input type="text" class="form-control" placeholder="Search Content" name="wlm_post_search_term" value="<?php echo esc_attr( stripslashes( (string) wlm_get_data()['wlm_post_search_term'] ) ); ?>">
					<div class="input-group-append">
						<button class="btn -default -icon search-btn btn-block">
							<i class="wlm-icons">search</i>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="row header-tools pb-0 -no-border">
	<div class="col-md-12">
		<div class="pagination form-inline pull-right mt-3">
			<div class="input-group icon-group ml-lg-auto mr-4">
				<a href="#" title="Collapsed table view" class="btn -icon-only toggle-collapse-table<?php echo esc_attr( 'collapse' === $tbl_collapse ? ' active' : '' ); ?> mr-1">
					<i class="wlm-icons md-24">reorder</i>
					<input type="hidden" name="wlm_toggle_contentcontrol_table" value="collapse" />
					<input type="hidden" name="action" value="admin_actions" />
					<input type="hidden" name="WishListMemberAction" value="save" />
				</a>
				<a href="#" title="Expanded table view" class="btn -icon-only toggle-collapse-table<?php echo esc_attr( 'expand' === $tbl_collapse ? ' active' : '' ); ?>">
					<i class="wlm-icons md-24">view_agenda</i>
					<input type="hidden" name="wlm_toggle_contentcontrol_table" value="expand" />
					<input type="hidden" name="action" value="admin_actions" />
					<input type="hidden" name="WishListMemberAction" value="save" />
				</a>
			</div>
			<?php if ( $total_items && $total_items > $this->pagination_items[0] ) : ?>
					<?php if ( $perpage <= $total_items ) : ?>
						<div class="input-group ml-sm-auto ml-lg-0">
							<div class="input-group-prepend">
								<span class="text-muted pr-2">
									<div role="presentation" class="dropdown mt-9px">
										<a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
											<?php echo number_format( $s_offset, 0, '.', ',' ); ?>
											-
											<?php
											$x = $perpage * $s_currentpage;
											echo number_format( $x > $total_items ? $total_items : $x, 0, '.', ',' );
											?>
										</a> of <?php echo number_format( $total_items, 0, '.', ',' ); ?>
										<ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
											<?php foreach ( $this->pagination_items as $key => $value ) : ?>
												<a class="dropdown-item" target="_parent" href="<?php echo esc_url( $page_href . '&perpage=' . $value ); ?>"><?php echo esc_html( $value ); ?></a>
											<?php endforeach; ?>
										</ul>
									</div>
								</span>
								<?php if ( $s_currentpage > 1 ) : ?>
									<a target="_parent" href="<?php echo esc_url( $page_href . '&paged=1' ); ?>" class="mt-6px"><i class="wlm-icons md-26">first_page</i></a>
								<?php else : ?>
									<a class="mt-6px text-muted disabled" disabled='disabled'><i class="wlm-icons md-26">first_page</i></a>
								<?php endif; ?>

								<?php
								if ( $s_currentpage <= 1 ) {
									$previous_link = $page_href . '&paged=' . $total_pages;
								} else {
									$previous_link = $page_href . '&paged=' . ( $s_currentpage - 1 );
								}
								?>
								<a target="_parent" href="<?php echo esc_url( $previous_link ); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_left</i></a>
							</div>
							<input type="text" value="<?php echo esc_attr( $s_currentpage ); ?>" class="form-control text-center pagination-pagenum" data-pages="<?php echo esc_attr( $total_pages ); ?>" data-link="<?php echo esc_attr( $page_href . '&paged=' ); ?>" data-lpignore="true">
							<div class="input-group-append">
								<span class="mt-9px"> of <?php echo (int) $total_pages; ?></span>
								<?php
								if ( $s_currentpage < $total_pages ) {
									$next_link = $page_href . '&paged=' . ( $s_currentpage + 1 );
								} else {
									$next_link = $page_href . '&paged=1';
								}
								?>
								<a target="_parent" href="<?php echo esc_url( $next_link ); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_right</i></a>

								<?php if ( $s_currentpage < $total_pages ) : ?>
									<a target="_parent" href="<?php echo esc_url( $page_href . '&paged=' . $total_pages ); ?>" class="mt-6px"><i class="wlm-icons md-26">last_page</i></a>
								<?php else : ?>
									<a class="mt-6px text-muted disabled" disabled='disabled'><i class="wlm-icons md-26">last_page</i></a>
								<?php endif; ?>
							</div>
						</div>
					<?php else : ?>
							<div style="width: auto" class="input-group pull-right">
								<div class="input-group-prepend">
									<span class="text-muted pr-2">
										<div role="presentation" class="dropdown mt-9px">
											<a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
												<?php echo number_format( $s_offset, 0, '.', ',' ); ?>
												-
												<?php
												$x = $perpage * $s_currentpage;
												echo number_format( $x > $total_items ? $total_items : $x, 0, '.', ',' );
												?>
											</a> of <?php echo number_format( $total_items, 0, '.', ',' ); ?>
											<ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
												<?php foreach ( $this->pagination_items as $key => $value ) : ?>
													<a class="dropdown-item" target="_parent" href="<?php echo esc_url( $page_href . '&perpage=' . $value ); ?>"><?php echo esc_html( $value ); ?></a>
												<?php endforeach; ?>
											</ul>
										</div>
									</span>
								</div>
							</div>
					<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="table-wrapper -special table-responsive">
	<table class="table table-condensed">
		<thead>
			<tr>
				<th style="width: 40px;" class="text-center">
					<div class="form-check -table-check-header">
						<input value="" type="checkbox" class="chk-all form-check-input">
						<label for="" class="form-check-label d-none"></label>
					</div>
				</th>
				<th>
					<a href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'orderby' => 'post_title',
								'order'   => $sort_name,
							),
							admin_url( 'admin.php' . $page_href )
						)
					);
					?>
					"><?php esc_html_e( 'Name', 'wishlist-member' ); ?><span class="wlm-icons"><?php echo esc_html( $sort_name_icon ); ?></span></a>
				</th>
				<?php if ( $support_categories ) : ?>
					<th class="text-center" style="width: 20%"><?php esc_html_e( 'Categories', 'wishlist-member' ); ?></th>
				<?php endif; ?>
				<th style="width: 150px;" class="text-center"><a href="
				<?php
				echo esc_url(
					add_query_arg(
						array(
							'orderby' => 'post_date',
							'order'   => $sort_date,
						),
						admin_url( 'admin.php' . $page_href )
					)
				);
				?>
				"><?php esc_html_e( 'Date Published', 'wishlist-member' ); ?><span class="wlm-icons"><?php echo esc_html( $sort_date_icon ); ?></span></a></th>
				<th style="width: 100px;" class="text-center"></th>
			</tr>
		</thead>
		<tbody class="outer-tbody button-hover">
			<?php if ( count( $content_items ) > 0 ) : ?>
				<?php foreach ( $content_items as $item ) : ?>
					<?php display_items( $this, $item, $post_children, $content_type, $icollapse1, $icollapse2, $is_heirarchical, $support_categories ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="user-details-tr user-details-tr-<?php echo esc_attr( $item->ID ); ?>">
					<td class="" colspan="2">No content available</td>
					<?php if ( $support_categories ) : ?>
						<td class="text-center">&nbsp;</td>
					<?php endif; ?>
					<td class="text-center">&nbsp;</td>
					<td class="text-center">&nbsp;</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<div class="pagination pull-right">
	<?php if ( $total_items && $total_items > $this->pagination_items[0] ) : ?>
			<?php if ( $perpage <= $total_items ) : ?>
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="text-muted pr-2">
							<div role="presentation" class="dropdown mt-9px">
								<a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
									<?php echo number_format( $s_offset, 0, '.', ',' ); ?>
									-
									<?php
									$x = $perpage * $s_currentpage;
									echo number_format( $x > $total_items ? $total_items : $x, 0, '.', ',' );
									?>
								</a> of <?php echo number_format( $total_items, 0, '.', ',' ); ?>
								<ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
									<?php foreach ( $this->pagination_items as $key => $value ) : ?>
										<a class="dropdown-item" target="_parent" href="<?php echo esc_url( $page_href . '&perpage=' . $value ); ?>"><?php echo esc_html( $value ); ?></a>
									<?php endforeach; ?>
								</ul>
							</div>
						</span>
						<?php if ( $s_currentpage > 1 ) : ?>
							<a target="_parent" href="<?php echo esc_url( $page_href . '&paged=1' ); ?>" class="mt-6px"><i class="wlm-icons md-26">first_page</i></a>
						<?php else : ?>
							<a class="mt-6px text-muted disabled" disabled='disabled'><i class="wlm-icons md-26">first_page</i></a>
						<?php endif; ?>

						<?php
						if ( $s_currentpage <= 1 ) {
							$previous_link = $page_href . '&paged=' . $total_pages;
						} else {
							$previous_link = $page_href . '&paged=' . ( $s_currentpage - 1 );
						}
						?>
						<a target="_parent" href="<?php echo esc_url( $previous_link ); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_left</i></a>
					</div>
					<input type="text" value="<?php echo esc_attr( $s_currentpage ); ?>" class="form-control text-center pagination-pagenum" data-pages="<?php echo esc_attr( $total_pages ); ?>" data-link="<?php echo esc_attr( $page_href . '&paged=' ); ?>" data-lpignore="true">
					<div class="input-group-append">
						<span class="mt-9px"> of <?php echo (int) $total_pages; ?></span>
						<?php
						if ( $s_currentpage < $total_pages ) {
							$next_link = $page_href . '&paged=' . ( $s_currentpage + 1 );
						} else {
							$next_link = $page_href . '&paged=1';
						}
						?>
						<a target="_parent" href="<?php echo esc_url( $next_link ); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_right</i></a>

						<?php if ( $s_currentpage < $total_pages ) : ?>
							<a target="_parent" href="<?php echo esc_url( $page_href . '&paged=' . $total_pages ); ?>" class="mt-6px"><i class="wlm-icons md-26">last_page</i></a>
						<?php else : ?>
							<a class="mt-6px text-muted disabled" disabled='disabled'><i class="wlm-icons md-26">last_page</i></a>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
					<div style="width: auto" class="input-group pull-right">
						<div class="input-group-prepend">
							<span class="text-muted pr-2">
								<div role="presentation" class="dropdown mt-9px">
									<a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
										<?php echo number_format( $s_offset, 0, '.', ',' ); ?>
										-
										<?php
											$x = $perpage * $s_currentpage;
											echo number_format( $x > $total_items ? $total_items : $x, 0, '.', ',' );
										?>
									</a> of <?php echo number_format( $total_items, 0, '.', ',' ); ?>
									<ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
										<?php foreach ( $this->pagination_items as $key => $value ) : ?>
											<a class="dropdown-item" target="_parent" href="<?php echo esc_url( $page_href . '&perpage=' . $value ); ?>"><?php echo esc_html( $value ); ?></a>
										<?php endforeach; ?>
									</ul>
								</div>
							</span>
						</div>
					</div>
			<?php endif; ?>
	<?php endif; ?>
</div>

<!-- Modal -->
<div id="set-schedule-modal" data-id="set-schedule-modal" data-label="set_schedule_modal_label" data-title="Set Content Schedule" data-classes="modal-md" style="display:none">
	<div class="body">
		<div class="form-group col-md-12 membership-level-select">
			<label for="">Membership Levels</label>
			<select class="form-control wlm-levels" multiple="multiple" name="wlm_levels[]" placeholder="Select Membership Level" style="width: 100%" required>
				<?php foreach ( $wpm_levels as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="row ml-1 mr-1 content-sched-holder">
			<div class="form-group col-md-6 show-after-select">
				<label for="">Show After</label>
				<div class="input-group">
					<input type="number" min="1" max="999999" class="form-control wlm-show-after" name="show_after">
					<div class="input-group-append">
						<div class="input-group-text">day/s</div>
					</div>
				</div>
			</div>
			<div class="form-group col-md-6 show-after-select">
				<label for="">Show For</label>
				<div class="input-group">
					<input type="number" min="1" max="999999" class="form-control wlm-show-for" name="show_for">
					<div class="input-group-append">
						<div class="input-group-text">day/s</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group col-md-12 archive-date-holder">
			<label for="">Archive Date</label>
			<input id="DateRangePicker" type="text" class="form-control wlm-datetimepicker" value="" autocomplete="off" name="archive_date" placeholder="Archive Date">
		</div>
		<div class="form-group col-md-12 manager-date-holder">
			<div class="form-group col-md-12 manager-action-select">
				<label for="">Action</label>
				<select class="form-control wlm-select wlm-select-action" name="content_action" placeholder="Select Action" style="width: 100%">
					<option value="set">Set Content Status</option>
					<?php if ( $support_categories ) : ?>
						<option value="add">Add Content to a Category</option>
						<option value="move">Move Content to a Category</option>
					<?php endif; ?>
					<option value="repost">Repost Content</option>
				</select>
			</div>
			<div class="form-group col-md-12 date-holder">
				<label for="">Schedule Date</label>
				<input id="DateRangePicker" type="text" class="form-control wlm-datetimepicker" autocomplete="off" value="" name="schedule_date" placeholder="Schedule Date">
			</div>
			<div class="form-group col-md-12 action-status-holder">
				<label for="">Status</label>
				<select class="form-control wlm-select wlm-select-status" name="content_status" placeholder="Select Status" style="width: 100%">
					<option value="publish">Published</option>
					<option value="pending">Pending Review</option>
					<option value="draft">Draft</option>
					<option value="trash">Trash</option>
				</select>
			</div>
			<div class="form-group col-md-12 membership-level-select action-moveadd-holder">
				<label for="">Categories</label>
				 <?php $cats = get_categories( 'hide_empty=0' ); ?>
				<select class="form-control wlm-select wlm-select-cat" name="content_cat[]" multiple="multiple" placeholder="Select Categories" style="width: 100%">
					<?php foreach ( (array) $cats as $cats ) : ?>
						<option value="<?php echo esc_attr( $cats->cat_ID ); ?>"><?php echo esc_html( $cats->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-group col-md-12 membership-level-select action-repost-holder">
				<div class="row">
					<div class="col-md-4">
						<label for="">Every</label>
						<input type="number" min="1" max="999999" class="form-control" name="content_every">
					</div>
					<div class="col-md-4">
						<label for="">&nbsp;</label>
						<select class="form-control wlm-select wlm-select-by" name="content_by" placeholder="Select Frequency" style="width: 100%">
							<option value="day">Day(s)</option>
							<option value="month">Month(s)</option>
							<option value="year">Year(s)</option>
						</select>
					</div>
					<div class="col-md-4">
						<label for="">Repetition</label>
						<input type="number" min="1" max="999999" class="form-control" name="content_repeat">
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="sched_action" value="" />
		<input type="hidden" name="contentids" value="" />
		<input type="hidden" name="schedid" value="" />
		<input type="hidden" name="action" value="admin_actions" />
		<input type="hidden" name="WishListMemberAction" value="" />
	</div>
	<div class="footer">
		<button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e( 'Close', 'wishlist-member' ); ?></button>
		<button type="button" class="btn -primary confirm-save-button"><i class="wlm-icons">save</i>  <span>Set Schedule</span></button>
	</div>
</div>
