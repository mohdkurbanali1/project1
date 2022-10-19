<?php
/*
 * Content Manager Module
 * Version: 1.1.34
 * SVN: 34
 * @version $Rev: 30 $
 * $LastChangedBy: feljun $
 * $LastChangedDate: 2016-01-21 05:41:36 -0500 (Thu, 21 Jan 2016) $
 *
 */
if ( ! class_exists( 'WLM3_ContentManager' ) ) {
	/**
	 * Content Archiver Core Class
	 */
	class WLM3_ContentManager {
		// activate module
		public function load_hooks() {
			add_action( 'init', array( &$this, 'ApplyDueDate' ) );
			add_action( 'wishlistmember_post_page_options_menu', array( &$this, 'wlm3_post_options_menu' ) );
			add_action( 'wishlistmember_post_page_options_content', array( &$this, 'ContentManagerOptions' ) );
		}
		// deactivate module
		public function remove_hooks() {
			// remove filters and actions
			remove_action( 'init', array( &$this, 'ApplyDueDate' ) );
			remove_action( 'wishlistmember_post_page_options_menu', array( &$this, 'wlm3_post_options_menu' ) );
			remove_action( 'wishlistmember_post_page_options_content', array( &$this, 'ContentManagerOptions' ) );
		}

		public function wlm3_post_options_menu() {
			echo '<li><a href="#" data-target=".wlm-inside-manager" class="wlm-inside-toggle">Manager</a></li>';
		}
		// page options
		public function ContentManagerOptions() {
			$post_id      = wlm_get_data()['post'];
			$custom_types = get_post_types(
				array(
					'public'   => true,
					'_builtin' => false,
				)
			);
			$ptypes       = array_merge( array( 'post', 'page' ), $custom_types );
			$post_type    = $post_id ? get_post_type( $post_id ) : wlm_get_data()['post_type'];
			$post_type    = $post_type ? $post_type : 'post';

			$support_categories = 'post' === $post_type ? true : false;
			if ( 'post' !== $post_type && 'page' !== $post_type ) {
				$p = get_post_type_object( $post_type );
				if ( in_array( 'category', $p->taxonomies ) ) {
					$support_categories = true;
				}
			}

			global $WishListMemberInstance,$WishListContentControl;
			// default date
			$wlccduedate  = date_parse( wlm_date( 'Y-m-d H:i:s' ) );
			$wlccduedate  = wlm_date( 'Y-m-d H:i:s', mktime( 0, 0, 0, (int) $wlccduedate['month'], (int) $wlccduedate['day'], (int) $wlccduedate['year'] ) );
			$wlcc_duedate = $this->format_date( $wlccduedate, 'm/d/Y h:i A' );

			$sched_type        = array( 'move', 'repost', 'set' );
			$content_schedules = array();
			if ( $post_id ) {
				foreach ( $sched_type as $key => $t ) {
					$content_sched = $this->GetPostManagerDate( $t, $post_id );
					foreach ( (array) $content_sched as $key => $value ) {
						$content_schedules[] = array(
							'type'  => $t,
							'value' => $value,
						);
					}
				}
			}
			wlm_print_script( wishlistmember_instance()->legacy_wlm_url . '/admin/post_page_options/content-control/js/manager.js' );
			?>
				<div class="wlm-inside wlm-inside-manager" style="display: none;">
					<div class="manager-form-holder">
						<table class="widefat" id='wlcc_set' style="width:100%;text-align: left;" cellspacing="0">
							<thead>
								<tr style="width:100%;">
									<th colspan="3"><?php esc_html_e( 'Add Schedule', 'wl-contentcontrol' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr style="width:100%;">
									 <td style="width: 20%;border-bottom: 1px solid #eeeeee;">
										 <label for="">Action</label>
										<select class="form-control wlm-select wlm-select-action" name="content_action" placeholder="Select Action" style="width: 100%">
											<option value="set">Set Content Status</option>
											<?php if ( $support_categories ) : ?>
												<option value="add">Add Content to a Category</option>
												<option value="move">Move Content to a Category</option>
											<?php endif; ?>
											<option value="repost">Repost Content</option>
										</select>
									 </td>
									 <td style="width: 20%; border-bottom: 1px solid #eeeeee;">
										 <label for="">Schedule</label>
										<input id="DateRangePicker" type="text" class="form-control wlm-datetimepicker" value="" name="schedule_date" placeholder="Schedule Date">
									 </td>
									 <td style="width: 60%; border-bottom: 1px solid #eeeeee;">
											<div class="form-group membership-level-select action-moveadd-holder d-none">
												 <?php $cats = get_categories( 'hide_empty=0' ); ?>
												 <label for="">Category</label>
												<select class="form-control wlm-select-cat" name="content_cat[]" multiple="multiple" placeholder="Select Categories" style="width: 100%">
													<?php foreach ( (array) $cats as $cats ) : ?>
														<option value="<?php echo esc_attr( $cats->cat_ID ); ?>"><?php echo esc_html( $cats->name ); ?></option>
													<?php endforeach; ?>
												</select>
											</div>
											<div class="form-group action-status-holder">
												 <label for="">Status</label>
												<select class="form-control wlm-select wlm-select-status" name="content_status" placeholder="Select Status" style="width: 100%">
													<option value="publish">Published</option>
													<option value="pending">Pending Review</option>
													<option value="draft">Draft</option>
													<option value="trash">Trash</option>
												</select>
											</div>
											<div class="form-group action-repost-holder d-none">
												<div class="row">
													<div style="float: left; width: 20%;">
														<label for="">Every</label>
														<input type="number" min="1" max="999999" class="form-control" name="content_every">
													</div>
													<div style="float: left; width: 40%;">
														<label for="">&nbsp;</label>
														<select class="form-control wlm-select-by" name="content_by" placeholder="Select Frequency" style="width: 100%">
															<option value="day">Day/s</option>
															<option value="month">Month/s</option>
															<option value="year">Year/s</option>
														</select>
													</div>
													<div style="float: left; width: 40%; padding-left: 5%;">
														<label for="">Repetition</label>
														<input type="number" min="1" max="999999" class="form-control" name="content_repeat">
													</div>
												</div>
											</div>
									 </td>
								</tr>
							</tbody>
						</table>
						<div style="text-align: right; padding-top: 4px; padding-bottom: 8px;">
							<div class="wlm-message" style="display: none"><?php esc_html_e( 'Saved', 'wishlist-member' ); ?></div>
							<a href="#" class="wlm-btn -with-icons -success -centered-span wlm-manager-save">
								<i class="wlm-icons"><img src="<?php echo esc_url( $WishListMemberInstance->pluginURL3 ); ?>/ui/images/baseline-save-24px.svg" alt=""></i>
								<span><?php esc_html_e( 'Save Schedule', 'wishlist-member' ); ?></span>
							</a>
						</div>
					</div>
					<table class="widefat" id='wlcc_manager_table' style="width:100%;text-align: left;" cellspacing="0">
						<thead>
							<tr style="width:100%;">
								<th style="border-bottom: 1px solid #aaaaaa;"><?php esc_html_e( 'Schedules', 'wl-contentcontrol' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( count( $content_schedules ) > 0 ) : ?>
								<?php foreach ( $content_schedules as $sched ) : ?>
									<tr>
										<td style="border-bottom: 1px solid #eeeeee;">
											<span class='wlm-manage-sched' style="vertical-align: middle;">
												<?php
													$str = '';
													$v   = $sched['value'];
												switch ( $sched['type'] ) {
													case 'move':
														if ( 'move' == $v->action ) {
															$str = 'Move to ';
														} else {
															$str = 'Add to ';
														}
														$cat = explode( '#', $v->categories );
														$t   = array();
														foreach ( (array) $cat as $cati => $c ) {
															$category = get_term_by( 'id', $c, 'category' );
															$t[]      = $category->name;
														}
														$str .= implode( ',', $t );
														$str .= ' on <strong>' . $WishListMemberInstance->format_date( $v->due_date, 0 ) . '</strong>';
														break;
													case 'repost':
														$str  = 'Repost';
														$str .= ' on <strong>' . $WishListMemberInstance->format_date( $v->due_date, 0 ) . '</strong>.';
														if ( $v->rep_num > 0 ) {
															$every = array(
																'day'   => 'Day/s',
																'month' => 'Month/s',
																'year'  => 'Year/s',
															);
															$str  .= ' Repeat every <strong>' . $v->rep_num . ' ' . $every[ $v->rep_by ] . '</strong>.';
															   $d1 = date_parse( $v->due_date );
															if ( 'day' == $v->rep_by ) {
																  $new_bue_date = mktime( $d1['hour'], $d1['minute'], $d1['second'], $d1['month'], ( $d1['day'] + $v->rep_num ), $d1['year'] );
															} elseif ( 'month' == $v->rep_by ) {
																	 $new_bue_date = mktime( $d1['hour'], $d1['minute'], $d1['second'], ( $d1['month'] + $v->rep_num ), $d1['day'], $d1['year'] );
															} elseif ( 'year' == $v->rep_by ) {
																$new_bue_date = mktime( $d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], ( $d1['year'] + $v->rep_num ) );
															} else {
																$new_bue_date = mktime( $d1['hour'], $d1['minute'], $d1['second'], $d1['month'], ( $d1['day'] + $v->rep_num ), $d1['year'] );
															}

															if ( $v->rep_end > 0 ) {
																$str .= ' Next due date is on <strong>' . $WishListMemberInstance->format_date( wlm_date( 'Y-m-d H:i:s', $new_bue_date ), 0 ) . '</strong> (' . ( $v->rep_end - 1 ) . ' repetition/s left)';
															} else {
																$str .= ' No repetition limit.';
															}
														}
														break;
													case 'set':
														$stats = array(
															'publish' => 'Published',
															'pending' => 'Pending Review',
															'draft'   => 'Draft',
															'trash'   => 'Trash',
														);
														$str   = 'Set content status to ' . $stats[ $v->status ];
														$str  .= ' on <strong>' . $WishListMemberInstance->format_date( $v->due_date, 0 ) . '</strong>.';
														break;
												}
													echo wp_kses_data( $str );
												?>
											</span>
											<span class="wlm-manage-actions" style="float: right; vertical-align: middle;">
												<a href="#" class="wlm-manager-remove" type="<?php echo esc_attr( $sched['type'] ); ?>" id="<?php echo esc_attr( $v->id ); ?>">remove</a>
											</span>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else : ?>
									<tr class="empty-tr">
										<td style="border-bottom: 1px solid #eeeeee;">
											<span class='wlm-manage-sched' style="vertical-align: middle;">
												- No schedule -
											</span>
											<span class="wlm-manage-actions" style="float: right; vertical-align: middle;">
												<a href="#" class="wlm-manager-remove" type="" id="">remove</a>
											</span>
										</td>
									</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			<?php
		}

		// save post expiry date
		public function UpdatePostManagerDate( $id, $data ) {
			global $wpdb;
			if ( 'move' === $data['action'] ) {
				$wpdb->update(
					$wpdb->prefix . 'wlcc_contentmanager_move',
					array(
						'due_date'   => $data['date'],
						'categories' => $data['cats'],
						'action'     => $data['method'],
					),
					array( 'id' => $id ),
					array( '%s', '%s', '%s' ),
					array( '%d' )
				);
			} elseif ( 'repost' === $data['action'] ) {
				$wpdb->update(
					$wpdb->prefix . 'wlcc_contentmanager_repost',
					array(
						'due_date' => $data['date'],
						'rep_num'  => $data['rep_num'],
						'rep_by'   => $data['rep_by'],
						'rep_end'  => $data['rep_end'],
					),
					array( 'id' => $id ),
					array( '%s', '%d', '%s', '%d' ),
					array( '%d' )
				);
			} elseif ( 'set' === $data['action'] ) {
				$wpdb->update(
					$wpdb->prefix . 'wlcc_contentmanager_set',
					array(
						'due_date' => $data['date'],
						'status'   => $data['status'],
					),
					array( 'id' => $id ),
					array( '%s', '%s' ),
					array( '%d' )
				);
			}
		}

		// save post expiry date
		public function SavePostManagerDate( $post_id, $data ) {
			global $wpdb;
			if ( 'move' === $data['action'] ) {
				if ( is_array( $post_id ) ) {
					foreach ( $post_id as $key => $value ) {
						$wpdb->insert(
							$wpdb->prefix . 'wlcc_contentmanager_move',
							array(
								'post_id'    => $value,
								'due_date'   => $data['date'],
								'categories' => $data['cats'],
								'action'     => $data['method'],
							),
							array( '%d', '%s', '%s', '%s' )
						);
					}
				} else {
					$wpdb->insert(
						$wpdb->prefix . 'wlcc_contentmanager_move',
						array(
							'post_id'    => $post_id,
							'due_date'   => $data['date'],
							'categories' => $data['cats'],
							'action'     => $data['method'],
						),
						array( '%d', '%s', '%s', '%s' )
					);
				}
			} elseif ( 'repost' === $data['action'] ) {
				if ( is_array( $post_id ) ) {
					foreach ( $post_id as $key => $value ) {
						$wpdb->insert(
							$wpdb->prefix . 'wlcc_contentmanager_repost',
							array(
								'post_id'  => $value,
								'due_date' => $data['date'],
								'rep_num'  => $data['rep_num'],
								'rep_by'   => $data['rep_by'],
								'rep_end'  => $data['rep_end'],
							),
							array( '%d', '%s', '%d', '%s', '%d' )
						);
					}
				} else {
					$wpdb->insert(
						$wpdb->prefix . 'wlcc_contentmanager_repost',
						array(
							'post_id'  => $post_id,
							'due_date' => $data['date'],
							'rep_num'  => $data['rep_num'],
							'rep_by'   => $data['rep_by'],
							'rep_end'  => $data['rep_end'],
						),
						array( '%d', '%s', '%d', '%s', '%d' )
					);
				}
			} elseif ( 'set' === $data['action'] ) {
				if ( is_array( $post_id ) ) {
					foreach ( $post_id as $key => $value ) {
						$wpdb->insert(
							$wpdb->prefix . 'wlcc_contentmanager_set',
							array(
								'post_id'  => $value,
								'due_date' => $data['date'],
								'status'   => $data['status'],
							),
							array( '%d', '%s', '%s' )
						);
					}
				} else {
					$wpdb->insert(
						$wpdb->prefix . 'wlcc_contentmanager_set',
						array(
							'post_id'  => $post_id,
							'due_date' => $data['date'],
							'status'   => $data['status'],
						),
						array( '%d', '%s', '%s' )
					);
				}
			}
			return $wpdb->insert_id;
		}
		// get post expiry date of the post
		public function GetPostManagerDate( $action, $post_id = '', $due_id = '', $start = 0, $limit = 0 ) {
			global $wpdb;
			$limit = $limit < 1 ? array( 0, PHP_INT_MAX ) : array( $start, $limit );
			if ( ! empty( $post_id ) ) {
				$post_id = (array) $post_id;
				return $wpdb->get_results(
					$wpdb->prepare(
						'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentmanager_' . $action ) . ' WHERE post_id IN (' . implode( ', ', array_fill( 0, count( $post_id ), '%d' ) ) . ') LIMIT %d,%d',
						...array_values( $post_id ),
						...array_values( $limit )
					)
				);
			} elseif ( ! empty( $due_id ) ) {
				$due_id = (array) $due_id;
				return $wpdb->get_results(
					$wpdb->prepare(
						'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentmanager_' . $action ) . ' WHERE id IN (' . implode( ', ', array_fill( 0, count( $due_id ), '%d' ) ) . ') LIMIT %d,%d',
						...array_values( $due_id ),
						...array_values( $limit )
					)
				);
			} else {
				return array();
			}
		}

		// get due date
		public function GetDueDate( $action, $due_id = '', $start = 0, $limit = 0 ) {
			global $wpdb;
			$limit = $limit < 1 ? array( 0, PHP_INT_MAX ) : array( $start, $limit );

			if ( is_array( $due_id ) ) {
				$results = $wpdb->query(
					$wpdb->prepare(
						'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentmanager_' . $action ) . ' WHERE id IN (' . implode( ', ', array_fill( 0, count( $due_id ), '%d' ) ) . ') ORDER BY due_date ASC LIMIT %d, %d',
						...array_values( $due_id ),
						...array_values( $limit )
					)
				);
			} else {
				if ( $due_id ) {
					$results = $wpdb->query(
						$wpdb->prepare(
							'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentmanager_' . $action ) . ' WHERE id=%d ORDER BY due_date ASC LIMIT %d, %d',
							$due_id,
							...array_values( $limit )
						)
					);
				} else {
					$results = $wpdb->query(
						$wpdb->prepare(
							'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentmanager_' . $action ) . ' ORDER BY date_added DESC LIMIT %d, %d',
							...array_values( $limit )
						)
					);
				}
			}
			return $results;
		}
		// delete post expiry date by id
		public function DeletePostManagerDate( $ids, $action ) {
			global $wpdb;
			if ( ! is_array( $ids ) ) {
				$ids = array( $ids );
			}
			$wpdb->query(
				$wpdb->prepare(
					'DELETE FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentmanager_' . $action ) . ' WHERE id IN (' . implode( ', ', array_fill( 0, count( $ids ), '%d' ) ) . ')',
					...array_values( $ids )
				)
			);
		}
		// delete post expiry date by id
		public function DeletePostManagerDate_byPostId( $ids, $action ) {
			global $wpdb;
			if ( ! is_array( $ids ) ) {
				$ids = array( $ids );
			}
			$wpdb->query(
				$wpdb->prepare(
					'DELETE FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentmanager_' . $action ) . ' WHERE post_id IN (' . implode( ', ', array_fill( 0, count( $ids ), '%d' ) ) . ')',
					...array_values( $ids )
				)
			);
		}
		// retrieve all posts or with expiry only
		public function GetPosts( $action, $show_all = false, $show_poststat = 'all', $ptype = 'post', $start = 0, $per_page = 0, $sort = 'ID', $asc = true ) {
			global $wpdb,$WishListMemberInstance;
			$table1 = $wpdb->prefix . 'posts';

			$limit = '';
			if ( $per_page < 1 ) {
				$start    = 0;
				$per_page = PHP_INT_MAX;
			}

			if ( $show_all ) {
				if ( 'all' === $show_poststat ) {
					$query_results = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT ID,post_author,post_date,post_status,post_modified,post_title,post_content FROM ' . esc_sql( $table1 ) . " WHERE post_type=%s AND post_status IN ('publish','draft','trash','pending') ORDER BY %0s %0s LIMIT %d,%d",
							$ptype,
							$sort,
							$asc ? 'ASC' : 'DESC',
							$start,
							$per_page
						)
					);
				} else {
					$query_results = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT ID,post_author,post_date,post_status,post_modified,post_title,post_content FROM ' . esc_sql( $table1 ) . ' WHERE post_type=%s AND post_status=%s ORDER BY %0s %0s LIMIT %d,%d',
							$ptype,
							$show_poststat ? $show_poststat : 'publish',
							$sort,
							$asc ? 'ASC' : 'DESC',
							$start,
							$per_page
						)
					);
				}
			} else {
				$table2        = $wpdb->prefix . 'wlcc_contentmanager_' . $action;
				$query_results = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT DISTINCT t1.ID,t1.post_author,t1.post_date,t1.post_status,t1.post_modified,t1.post_title,t1.post_content FROM ' . esc_sql( $table1 ) . ' t1 INNER JOIN ' . esc_sql( $table2 ) . ' t2 ON t1.ID=t2.post_id AND t1.post_type=%s ORDER BY %0s %0s LIMIT %d,%d',
						$ptype,
						$sort,
						$asc ? 'ASC' : 'DESC',
						$start,
						$per_page
					)
				);
			}
			return $query_results;
		}

		// retrieve all posts or with expiry only
		public function ApplyDueDate() {
			global $wpdb,$WishListMemberInstance;
			$table  = $wpdb->prefix . 'posts';
			$table1 = $wpdb->prefix . 'wlcc_contentmanager_repost';
			$table2 = $wpdb->prefix . 'wlcc_contentmanager_move';
			$table3 = $wpdb->prefix . 'wlcc_contentmanager_set';

			$res = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( $table1 ) . ' WHERE due_date <= %s', wlm_date( 'Y-m-d H:i:s' ) ) );
			foreach ( (array) $res as $result ) {
				$wpdb->update(
					$table,
					array(
						'post_date'     => $result->due_date,
						'post_date_gmt' => $result->due_date,
					),
					array( 'ID' => $result->post_id )
				);
				   // check for repetition
				   $rep_num = $result->rep_num;
				   $rep_end = $result->rep_end;
				if ( $rep_num > 0 ) {
					$d1 = date_parse( $result->due_date );
					if ( 'day' == $result->rep_by ) {
						 $new_bue_date = mktime( $d1['hour'], $d1['minute'], $d1['second'], $d1['month'], ( $d1['day'] + $rep_num ), $d1['year'] );
					} elseif ( 'month' == $result->rep_by ) {
						 $new_bue_date = mktime( $d1['hour'], $d1['minute'], $d1['second'], ( $d1['month'] + $rep_num ), $d1['day'], $d1['year'] );
					} elseif ( 'year' == $result->rep_by ) {
						$new_bue_date = mktime( $d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], ( $d1['year'] + $rep_num ) );
					} else {
						$new_bue_date = mktime( $d1['hour'], $d1['minute'], $d1['second'], $d1['month'], ( $d1['day'] + $rep_num ), $d1['year'] );
					}
					if ( $rep_end > 0 ) {
						if ( 1 === (int) $rep_end ) {
							  $this->DeletePostManagerDate( $result->id, 'repost' );
						} else {
							--$rep_end;
						}
					}
					$datum = array(
						'action'  => 'repost',
						'date'    => wlm_date( 'Y-m-d H:i:s', $new_bue_date ),
						'rep_num' => $rep_num,
						'rep_by'  => $result->rep_by,
						'rep_end' => $rep_end,
					);
					$this->UpdatePostManagerDate( $result->id, $datum );
				} else { // if not repeated then delete
					$this->DeletePostManagerDate( $result->id, 'repost' );
				}
			}

			$res = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( $table2 ) . ' WHERE due_date <= %s', wlm_date( 'Y-m-d H:i:s' ) ) );
			foreach ( (array) $res as $result ) {
				$cat = explode( '#', $result->categories );
				if ( 'add' == $result->action ) {
					$cur_cat = wp_get_post_categories( $result->post_id );
					$x       = array_merge( (array) $cat, (array) $cur_cat );
					$cat     = array_unique( (array) $x );
				}
				$catpost                  = array();
				$catpost['ID']            = $result->post_id;
				$catpost['post_category'] = $cat;
				$ret                      = wp_update_post( $catpost );
				$this->DeletePostManagerDate( $result->id, 'move' );
			}

			$res = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( $table3 ) . ' WHERE due_date <= %s', wlm_date( 'Y-m-d H:i:s' ) ) );
			foreach ( (array) $res as $result ) {
				$wpdb->update(
					$table,
					array( 'post_status' => $result->status ),
					array( 'ID' => $result->post_id )
				);
				$this->DeletePostManagerDate( $result->id, 'set' );
			}
		}
		/*
			OTHER FUNCTIONS NOT CORE OF CONTENT ARCHIVER GOES HERE
		*/
		// Save current selection in the dropdown
		public function SaveView() {
			$wpm_current_user = wp_get_current_user();
			if ( ! session_id() ) {
				session_start();
			}
			if ( $wpm_current_user->caps['administrator'] ) {
				$mode = isset( wlm_post_data()['mode'] ) ? wlm_post_data()['mode'] : wlm_get_data()['mode'];
				if ( ! empty( $mode ) ) {
					$_SESSION['wlcmmode'] = $mode;
				}
				$ptype = isset( wlm_post_data()['ptype'] ) ? wlm_post_data()['ptype'] : wlm_get_data()['ptype'];
				if ( ! empty( $ptype ) ) {
					$_SESSION['wlcmptype'] = $ptype;
				}
				if ( isset( wlm_post_data()['frmsubmit'] ) ) {
					$show_post      = isset( wlm_post_data()['show_post'] ) ? wlm_post_data()['show_post'] : wlm_get_data()['show_post'];
					$show_post_stat = isset( wlm_post_data()['show_post_stat'] ) ? wlm_post_data()['show_post_stat'] : wlm_get_data()['show_post_stat'];

					$_SESSION['wlcmshowpost'] = $show_post;
					if ( 'all' === $show_post && '' != $show_post_stat ) {
						$_SESSION['wlcmshowpoststat'] = $show_post_stat;
					}
				}
			}
		}
		// function for string
		public function cut_string( $str, $length, $minword ) {
			$sub = '';
			$len = 0;
			foreach ( explode( ' ', $str ) as $word ) {
				$part = ( ( ! empty( $sub ) ) ? ' ' : '' ) . $word;
				$sub .= $part;
				$len += strlen( $part );
				if ( strlen( $word ) > $minword && strlen( $sub ) >= $length ) {
					break;
				}
			}
			return $sub . ( ( $len < strlen( $str ) ) ? '...' : '' );
		}
		// function to get date difference needs php5.2
		public function isvalid_date( $date ) {
			$ret = false;
			if ( $date > wlm_date( 'Y-m-d H:i:s' ) ) {
				$ret = true;
			}
			return $ret;
		}
		// function to format the date
		public function format_date( $date, $format = 'M j, Y g:i a' ) {
			$d1    = date_parse( $date );
			$pdate = mktime( $d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], $d1['year'] );
			$date  = wlm_date( $format, $pdate );
			return $date;
		}
		// validate integer
		public function validateint( $inData ) {
			$intRetVal = false;
			$IntValue  = intval( $inData );
			$StrValue  = strval( $IntValue );
			if ( $StrValue == $inData ) {
				$intRetVal = true;
			}

			return $intRetVal;
		}
	}//end class
}
?>
