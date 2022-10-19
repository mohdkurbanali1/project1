<?php
/*
 * Content Archiver Module
 * Version: 1.1.34
 * SVN: 34
 * @version $Rev: 30 $
 * $LastChangedBy: feljun $
 * $LastChangedDate: 2016-01-21 05:41:36 -0500 (Thu, 21 Jan 2016) $
 *
 */
if ( ! class_exists( 'WLM3_ContentArchiver' ) ) {
	/**
	 * Content Archiver Core Class
	 */
	class WLM3_ContentArchiver {
		// activate module
		public function load_hooks() {
			// save Content Archiver Options when savign the post
			add_action( 'wp_insert_post', array( &$this, 'SaveContentArchOptions' ) );
			// post filters
			add_filter( 'posts_where', array( &$this, 'PostExpirationWhere' ) );
			add_filter( 'get_next_post_where', array( &$this, 'PostExpirationAdjacentWhere' ) );
			add_filter( 'get_previous_post_where', array( &$this, 'PostExpirationAdjacentWhere' ) );

			// filter for get_pages function because it does not use WP_Query
			add_filter( 'get_pages', array( &$this, 'GetPages' ), 9999, 2 );
			add_filter( 'pre_get_posts', array( &$this, 'PreGetPost' ) );

			add_action( 'wishlistmember_post_page_options_menu', array( &$this, 'wlm3_post_options_menu' ) );
			add_action( 'wishlistmember_post_page_options_content', array( &$this, 'ContentArchOptions' ) );
		}
		// deactivate module
		public function remove_hooks() {
			// remove filters and actions
			// save Content Archiver Options when savign the post
			remove_action( 'wp_insert_post', array( &$this, 'SaveContentArchOptions' ) );
			// post filters
			remove_filter( 'posts_where', array( &$this, 'PostExpirationWhere' ) );
			remove_filter( 'get_next_post_where', array( &$this, 'PostExpirationAdjacentWhere' ) );
			remove_filter( 'get_previous_post_where', array( &$this, 'PostExpirationAdjacentWhere' ) );
			remove_filter( 'get_pages', array( &$this, 'GetPages' ) );
			remove_filter( 'pre_get_posts', array( &$this, 'PreGetPost' ) );

			remove_action( 'wishlistmember_post_page_options_menu', array( &$this, 'wlm3_post_options_menu' ) );
			remove_action( 'wishlistmember_post_page_options_content', array( &$this, 'ContentArchOptions' ) );
		}

		public function wlm3_post_options_menu() {
			echo '<li><a href="#" data-target=".wlm-inside-archiver" class="wlm-inside-toggle">Archiver</a></li>';
		}
		// page options
		public function ContentArchOptions() {
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
			if ( $post_type ) {
				if ( ! in_array( $post_type, $ptypes ) ) {
					return false; // do not display option on pages
				}
			} else {
				return false;
			}

			global $WishListMemberInstance,$WishListContentControl;
			$wpm_levels = $WishListMemberInstance->get_option( 'wpm_levels' );

			// default date
			$wlccexpdate  = date_parse( wlm_date( 'Y-m-d H:i:s' ) );
			$wlccexpdate  = wlm_date( 'Y-m-d H:i:s', mktime( 0, 0, 0, (int) $wlccexpdate['month'], (int) $wlccexpdate['day'], (int) $wlccexpdate['year'] ) );
			$wlcc_expdate = $this->format_date( $wlccexpdate, 'm/d/Y h:i A' );
			wlm_print_script( wishlistmember_instance()->legacy_wlm_url . '/admin/post_page_options/content-control/js/archiver.js' );
			?>
				<div class="wlm-inside wlm-inside-archiver" style="display: none;">
					<table class="widefat" id='wlcc_ca' style="text-align: left;" cellspacing="0">
						<thead>
						<tr style="width:100%;">
							<th style="width: 60%;"> <?php esc_html_e( 'Membership Level/s' ); ?></th>
							<th style="width: 40%;"> <?php esc_html_e( 'Archive Date' ); ?> </th>
						</tr>
						</thead>
					</table>
					<div id="wlcclevels_ca" style="text-align:left;overflow:auto;">
						<table class="widefat" id="wlcc_ca" cellspacing="0" style="text-align:left;">
							<tbody>
						<?php foreach ( (array) $wpm_levels as $id => $level ) : ?>
							<?php
								$date        = '';
								$post_expiry = $this->GetPostExpiryDate( $post_id, $id );
								$post_expiry = is_array( $post_expiry ) ? $post_expiry : array();
							if ( count( $post_expiry ) > 0 && $post_id ) {
								$date = $this->format_date( $post_expiry[0]->exp_date, 'm/d/Y h:i A' );
							}
							?>
								<tr id="tr<?php echo esc_attr( $id ); ?>" style="width:100%;" class="<?php echo ( $alt++ ) % 2 ? '' : 'alternate'; ?>">
									<td style="width: 60%;border-bottom: 1px solid #eeeeee;"><strong><?php echo esc_html( $level['name'] ); ?></strong></td>
									 <td style="width: 40%;border-bottom: 1px solid #eeeeee;">
										 <input style="width: 200px;" type="text" class="form-control wlm-datetimepicker" id="wlcc_expiry<?php echo esc_attr( $id ); ?>" name="wlcc_expiry[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $date ); ?>" >
									 </td>
								</tr>
							<?php endforeach; ?>
						  </tbody>
						</table>
					</div>
					<div style="text-align: right; padding-top: 4px; padding-bottom: 8px;">
						<div class="wlm-message" style="display: none"><?php esc_html_e( 'Saved', 'wishlist-member' ); ?></div>
						<a href="#" class="wlm-btn -with-icons -success -centered-span wlm-archiver-save">
							<i class="wlm-icons"><img src="<?php echo esc_url( $WishListMemberInstance->pluginURL3 ); ?>/ui/images/baseline-save-24px.svg" alt=""></i>
							<span><?php esc_html_e( 'Save Schedule', 'wishlist-member' ); ?></span>
						</a>
					</div>
				</div>
				<input type='hidden' name='wlccca_save_marker' value='1'>
			<?php
		}
		// save content archiver options
		public function SaveContentArchOptions() {
			global $WishListMemberInstance,$WishListContentControl;
			$post_ID = wlm_post_data()['post_ID'];

			$wlccca_save_marker = wlm_post_data()['wlccca_save_marker'];
			if ( 1 !== (int) $wlccca_save_marker ) {
				return false;
			}

			$wpm_levels  = $WishListMemberInstance->get_option( 'wpm_levels' );
			$wlcc_expiry = wlm_post_data()['wlcc_expiry'];
			foreach ( (array) $wpm_levels as $id => $level ) {
				$wlccexpiry  = '' == $wlcc_expiry[ $id ] || empty( $wlcc_expiry[ $id ] ) ? 0 : $wlcc_expiry[ $id ];
				$wlccexpdate = date_parse( $wlccexpiry );
				if ( ( isset( $wlccexpdate['error_count'] ) && $wlccexpdate['error_count'] > 0 ) || ! $wlccexpdate['year'] ) {
					$this->DeletePostExpiryDate( $post_ID, $id );
				} else {
					$date = wlm_date( 'Y-m-d H:i:s', mktime( (int) $wlccexpdate['hour'], (int) $wlccexpdate['minute'], 0, (int) $wlccexpdate['month'], (int) $wlccexpdate['day'], (int) $wlccexpdate['year'] ) );
					$this->SavePostExpiryDate( $post_ID, $id, $date );
				}
			}
		}
		// save post expiry date
		public function SavePostExpiryDate( $post_id, $mlevel, $d ) {
			global $wpdb;
			$exp = $this->GetPostExpiryDate( $post_id, $mlevel );
			$exp = is_array( $exp ) ? $exp : array();
			if ( count( $exp ) > 0 ) {
				$wpdb->update(
					$wpdb->prefix . 'wlcc_contentarchiver',
					array( 'exp_date' => $d ),
					array(
						'mlevel'  => $mlevel,
						'post_id' => $post_id,
					)
				);
			} else {
				$wpdb->insert(
					$wpdb->prefix . 'wlcc_contentarchiver',
					array(
						'post_id'  => $post_id,
						'mlevel'   => $mlevel,
						'exp_date' => $d,
					)
				);
			}
		}
		// get post expiry date
		public function GetPostExpiryDate( $post_id = '', $mlevel = '', $start = 0, $limit = 0 ) {
			global $wpdb;

			$mlevels = is_array( $mlevel ) ? $mlevel : array( $mlevel );
			$q_limit = array( $start, $limit );

			if ( ! empty( $post_id ) && ! empty( $mlevel ) ) {
					return $wpdb->get_results(
						$wpdb->prepare(
							'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . ' WHERE post_id=%d AND mlevel IN (' . implode( ', ', array_fill( 0, count( $mlevels ), '%s' ) ) . ')',
							$post_id,
							...array_values( $mlevels )
						)
					);
			} elseif ( ! empty( $post_id ) ) {
				if ( $limit > 0 ) {
						return $wpdb->get_results(
							$wpdb->prepare(
								'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . ' WHERE post_id=%d ORDER BY date_added DESC LIMIT %d,%d',
								$post_id,
								...array_values( $q_limit )
							)
						);
				} else {
						return $wpdb->get_results(
							$wpdb->prepare(
								'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . ' WHERE post_id=%d',
								$post_id
							)
						);
				}
			} elseif ( ! empty( $mlevel ) ) {
				if ( $limit > 0 ) {
						return $wpdb->get_results(
							$wpdb->prepare(
								'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . ' WHERE mlevel IN (' . implode( ', ', array_fill( 0, count( $mlevels ), '%s' ) ) . ') ORDER BY date_added DESC LIMIT %d,%d',
								...array_values( $mlevels ),
								...array_values( $q_limit )
							)
						);
				} else {
						return $wpdb->get_results(
							$wpdb->prepare(
								'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . ' WHERE mlevel IN (' . implode( ', ', array_fill( 0, count( $mlevels ), '%s' ) ) . ')',
								...array_values( $mlevels )
							)
						);
				}
			} elseif ( $limit > 0 ) {
					return $wpdb->get_results(
						$wpdb->prepare(
							'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . ' ORDER BY date_added DESC LIMIT %d,%d',
							...array_values( $q_limit )
						)
					);
			} else {
					return $wpdb->get_results( 'SELECT * FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . ' ORDER BY date_added DESC' );
			}
		}
		// delete post expiry date
		public function DeletePostExpiryDate( $post_id, $mlevel = '%' ) {
			global $wpdb;

			$mlevel   = wlm_or( wlm_trim( $mlevel ), '%' );
			$post_ids = (array) $post_id;

			$wpdb->query(
				$wpdb->prepare(
					'DELETE FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . ' WHERE `mlevel` LIKE %s AND `post_id` IN (' . implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) ) . ')',
					$mlevel,
					...array_values( $post_ids )
				)
			);
		}
		/**
		 * Function to get Protected|Expired|ALL Posts
		 * Return: Array()
		 */
		public function GetPosts( $show_post, $ptype, $show_level = '', $start = 0, $per_page = 0, $sort = 'ID', $asc = 1 ) {
			global $wpdb;
			$limit = $per_page < 1 ? array( 0, PHP_INT_MAX ) : array( $start, $per_page );
			$order = array( $sort, $asc ? 'ASC' : 'DESC' );
			if ( 'all' === $show_post || empty( $show_post ) ) {
					$query_result = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT ID,post_author,post_status,post_date,post_modified,post_title,post_content FROM ' . $wpdb->posts . " t1 WHERE post_type= %s AND post_status='publish' ORDER BY %0s %0s LIMIT %d,%d",
							$ptype,
							...array_values( $order ),
							...array_values( $limit )
						)
					);
			} elseif ( 'expiry' === $show_post ) {
				if ( empty( $show_level ) ) {
						$query_result = $wpdb->get_results(
							$wpdb->prepare(
								'SELECT DISTINCT t1.ID,t1.post_author,t1.post_status,t1.post_date,t1.post_modified,t1.post_title,t1.post_content FROM ' . $wpdb->posts . ' t1 INNER JOIN ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . " t2 ON t1.ID=t2.post_id AND t1.post_type=%s AND t1.post_status='publish' ORDER BY %0s %0s LIMIT %d,%d",
								$ptype,
								...array_values( $order ),
								...array_values( $limit )
							)
						);
				} else {
					$query_result = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT DISTINCT t1.ID,t1.post_author,t1.post_status,t1.post_date,t1.post_modified,t1.post_title,t1.post_content FROM ' . $wpdb->posts . ' t1 INNER JOIN ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . " t2 ON t1.ID=t2.post_id AND t1.post_type=%s AND t1.post_status='publish' AND t2.mlevel = %s ORDER BY %0s %0s LIMIT %d,%d",
							$ptype,
							$show_level,
							...array_values( $order ),
							...array_values( $limit )
						)
					);
				}
			} elseif ( 'noexpiry' === $show_post ) {
				if ( empty( $show_level ) ) {
					$query_result = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT DISTINCT t1.ID,t1.post_author,t1.post_status,t1.post_date,t1.post_modified,t1.post_title,t1.post_content FROM ' . $wpdb->posts . ' t1 WHERE t1.ID NOT IN (SELECT post_id FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . " t2) AND t1.post_type=%s AND t1.post_status='publish' ORDER BY %0s %0s LIMIT %d,%d",
							$ptype,
							...array_values( $order ),
							...array_values( $limit )
						)
					);
				} else {
					$query_result = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT DISTINCT t1.ID,t1.post_author,t1.post_status,t1.post_date,t1.post_modified,t1.post_title,t1.post_content FROM ' . $wpdb->posts . ' t1 WHERE t1.ID NOT IN (SELECT post_id FROM ' . esc_sql( $wpdb->prefix . 'wlcc_contentarchiver' ) . " t2 WHERE t2.mlevel=%s) AND t1.post_type=%s AND t1.post_status='publish' ORDER BY %0s %0s LIMIT %d,%d",
							$show_level,
							$ptype,
							...array_values( $order ),
							...array_values( $limit )
						)
					);
				}
			} elseif ( 'protected' === $show_post ) {
				// get users protected post  for this level
				// get users unprotected content for this user
				$wpm_levels     = wishlistmember_instance()->get_option( 'wpm_levels' );
				$ids            = array();
				$has_all_access = false;
				// check if the level has all access to post
				if ( $wpm_levels[ $show_level ]['allposts'] ) {
					$has_all_access = true;
				}
				if ( $has_all_access ) { // if the user has all access to posts
					$query_result = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT ID,post_author,post_date,post_status,post_modified,post_title,post_content FROM ' . $wpdb->posts . " WHERE post_type=%s AND post_status='publish' ORDER BY %0s %0s LIMIT %d,%d",
							$ptype,
							...array_values( $order ),
							...array_values( $limit )
						)
					);
				} else {
					$x            = wishlistmember_instance()->get_membership_content( $ptype, $show_level );
					$query_result = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT ID,post_author,post_date,post_status,post_modified,post_title,post_content FROM ' . $wpdb->posts . " WHERE post_type=%s AND post_status='publish' AND ID IN(" . implode( ', ', array_fill( 0, count( $x ), '%s' ) ) . ') ORDER BY %0s %0s LIMIT %d,%d',
							$ptype,
							...array_values( $x ),
							...array_values( $order ),
							...array_values( $limit )
						)
					);
				}
			}
			return $query_results;
		}

		// function to get the expired post for the member
		public function GetExpiredPost() {
			global $WishListMemberInstance;
			$date_today         = wlm_date( 'Y-m-d H:i:s' ); // get date today
			$wpm_current_user   = wp_get_current_user();
			$levels             = array();
			$user_direct_levels = array();   // user levels for levels without archive date.
			$post_levels        = array();  // post levels
			$pplevel            = array();
			$user_pp_posts      = array();
			$expired_posts      = array();
			$unexpired_posts    = array();

			if ( $wpm_current_user->ID > 0 ) {
				$levels = $this->get_users_level( $wpm_current_user->ID ); // get users membership levels
				// remove payper post membership level
				foreach ( (array) $levels as $id => $level ) {
					if ( false !== strpos( $level, 'U' ) ) {
						$pplevel[] = $level;
						unset( $levels[ $id ] );
					}
				}
				if ( method_exists( $WishListMemberInstance, 'get_user_pay_per_post' ) && count( $pplevel ) > 0 ) {
					$user_pp_posts = $WishListMemberInstance->get_user_pay_per_post( $pplevel, false, null, true );
				}
			}
			// get the post with expiration date
			if ( count( $levels ) > 0 ) {
				$mlevel_post = $this->GetPostExpiryDate( '', $levels ); // get all the post with expiry date
			} else {
				$mlevel_post = $this->GetPostExpiryDate(); // if not logged in or dont have membership level
			}

			// $user_direct_levels will contain user levels that has no archive date.
			$user_direct_levels = $levels;
			foreach ( (array) $mlevel_post as $lvl_post ) {
				array_splice( $user_direct_levels, array_search( $lvl_post->mlevel, $user_direct_levels ), 1 );
			}

			// start checking the posts with expiration date if the user has access
			foreach ( (array) $mlevel_post as $lvl_post ) {

				$postdate_diff = $this->date_diff( $lvl_post->exp_date, $date_today, 86400 ); // + result means expired
				if ( count( $levels ) <= 0 ) { // non users, or non members
					if ( $postdate_diff > 0 ) { // check if the post itself is expired.
						$expired_posts[] = $lvl_post->post_id;
					}
				} else {
					// get level registration date of the user
					// $user_leveldate = wlm_date('Y-m-d H:i:s',$WishListMemberInstance->UserLevelTimeStamp($wpm_current_user->ID,$lvl_post->mlevel));
					$user_leveldate = gmdate( 'Y-m-d H:i:s', $WishListMemberInstance->user_level_timestamp( $wpm_current_user->ID, $lvl_post->mlevel ) + $WishListMemberInstance->gmt );
					$leveldate_diff = $this->date_diff( $lvl_post->exp_date, $user_leveldate, 86400 ); // + result means user cannot access this post

					if ( $postdate_diff > 0 ) { // check if the post is expired and if the user has previous access to the post.
						if ( $leveldate_diff > 0 ) {
							$expired_posts[] = $lvl_post->post_id;
						} else {
							$unexpired_posts[] = $lvl_post->post_id;
						}
					} else {
						$unexpired_posts[] = $lvl_post->post_id;
					}
				}
			}

			// lets check if user has any levels that has no archive date.
			if ( count( $user_direct_levels ) > 0 ) {

				// lets see if if post is protected for any of user_direct_level.
				$post_levels             = wishlistmember_instance()->get_content_levels( 'posts', $lvl_post->post_id );
				$direct_access_by_levels = ! empty( array_intersect( $post_levels, $user_direct_levels ) );

				// we dont archive the post if the post and the user has level(s) without any archive date.
				if ( $direct_access_by_levels ) {
					$unexpired_posts[] = $lvl_post->post_id;
				}
			}

			$unexpired_posts = array_unique( $unexpired_posts ); // remove duplicate post id from unexpired post
			$expired_posts   = array_diff( $expired_posts, $unexpired_posts ); // take out post if the user still has access on it using different membership level
			$expired_posts   = array_unique( $expired_posts ); // remove duplicate post id from expired post

			// remove users pp post from the list
			if ( count( $user_pp_posts ) > 0 ) {
				$expired_posts = array_diff( $expired_posts, $user_pp_posts );
			}

			return $expired_posts;
		}

			// redirect user to error page if it is scheduled
		public function PreGetPost( $query ) {
			global $wpdb, $WishListMemberInstance;
			$wpm_current_user = wp_get_current_user();
			$is_single        = is_single() || is_page() ? true : false;
			// if this is not a single post or page or its in the admin area, dont try redirect
			if ( ! $is_single || current_user_can( 'manage_options' ) ) {
				return $query;
			}

			// retrieve the post id and post name (if needed)
			$pid  = false;
			$name = false;
			if ( is_page() ) {
				$pid   = isset( $query->query['page_id'] ) ? $query->query['page_id'] : false;
				$name  = ! $pid && isset( $query->query['pagename'] ) ? $query->query['pagename'] : '';
				$ptype = ! $pid && isset( $query->query['post_type'] ) ? $query->query['post_type'] : '';
				// check if WP queried_object have post_type if WP Query post_type is empty.
				if ( ! $ptype && isset( $name ) ) {
					$ptype = isset( $query->queried_object->post_type ) ? $query->queried_object->post_type : false;
				}
			} elseif ( is_single() ) {
				$pid   = isset( $query->query['p'] ) ? $query->query['p'] : false;
				$name  = isset( $query->query['name'] ) ? $query->query['name'] : '';
				$ptype = isset( $query->query['post_type'] ) ? $query->query['post_type'] : '';
			} else {
				$pid  = false;
				$name = '';
			}

			// get the post id based from the post name we got
			$name_array = explode( '/', $name );
			$name       = array_slice( $name_array, -1, 1 ); // get the last element
			$name       = $name[0];
			if ( $name ) {
				if ( $ptype ) {
					$pid = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM `$wpdb->posts` WHERE post_name=%s AND post_type=%s", $name, $ptype ) );
				} else {
					$pid = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM `$wpdb->posts` WHERE post_name=%s", $name ) );
				}
			} else {
				return $query;
			}

			// if theres a postid, lets redirect
			if ( $pid ) {
				$archived_content = $this->GetExpiredPost();

				if ( $wpm_current_user->ID > 0 ) {
					$levels = $this->get_users_level( $wpm_current_user->ID );

					// get the post with expiration date
					if ( count( $levels ) > 0 ) {
						$mlevel_post = $this->GetPostExpiryDate( $pid, $levels ); // get all the post with expiry date
					} else {
						$mlevel_post = $this->GetPostExpiryDate(); // if not logged in or dont have membership level
					}

					// levels that has archive date
					$post_arc_levels = array();
					foreach ( (array) $mlevel_post as $lvl_post ) {
						$post_arc_levels[ $lvl_post->post_id ][] = $lvl_post->mlevel;
					}

					// all levels ( with and without archive date)
					$post_levels = array();
					foreach ( (array) $post_arc_levels as $post_id => $arc_levels ) {
						$post_levels[ $post_id ] = wishlistmember_instance()->get_content_levels( 'posts', $post_id );
					}

					// post normal levels ( levels without archive date )
					$unexpired_posts = array();
					foreach ( (array) $post_arc_levels as $post_id => $arc_levels ) {

						$post_non_arc_levels = array_diff(
							$post_levels[ $post_id ],
							$post_arc_levels[ $post_id ]
						);

						$Protection_key = array_search( 'Protection', $post_non_arc_levels );
						if ( false !== $Protection_key ) {
							unset( $post_non_arc_levels[ $Protection_key ] );
						}

						// we dont archive the post if the post and the user has level(s) without any archive date.
						if ( ! empty( $post_non_arc_levels ) ) {
							$unexpired_posts[] = $post_id;
						}
					}

					if ( in_array( $pid, $unexpired_posts ) ) {
						// user had access to the post with normall level that has no archive date
						if ( $levels ) {
							foreach ( (array) $levels as $user_lvl ) {
								if ( in_array( $user_lvl, $post_non_arc_levels, true ) ) {
									return $query;
								}
							}
						}
					}
				}

				if ( in_array( $pid, $archived_content ) ) {
					// get settings
					$wlcc_archived_error_page = $WishListMemberInstance->get_option( 'archiver_error_page_type' );
					$wlcc_archived_error_page = $wlcc_archived_error_page ? $wlcc_archived_error_page : get_option( 'wlcc_archived_error_page' );
					$wlcc_archived_error_page = $wlcc_archived_error_page ? $wlcc_archived_error_page : 'text';

					if ( 'url' === $wlcc_archived_error_page ) {

						$wlcc_archived_error_page_url = $WishListMemberInstance->get_option( 'archiver_error_page_url' );
						$wlcc_archived_error_page_url = $wlcc_archived_error_page_url ? $wlcc_archived_error_page_url : get_option( 'wlcc_archived_error_page_url' );

						if ( ! empty( $wlcc_archived_error_page_url ) ) {
							$url   = wlm_trim( $wlcc_archived_error_page_url );
							$p_url = parse_url( $url );
							if ( ! isset( $p_url['scheme'] ) ) {
								$url = 'http://' . $url;
							}
						}
					} elseif ( 'internal' === $wlcc_archived_error_page ) {

						$wlcc_archived_error_page = $WishListMemberInstance->get_option( 'archiver_error_page_internal' );
						if ( ! $wlcc_archived_error_page ) {
							$wlcc_archived_error_page = $wlcc_archived_error_page && 'url' !== $wlcc_archived_error_page && 'internal' !== $wlcc_archived_error_page && 'text' !== $wlcc_archived_error_page ? $wlcc_archived_error_page : false;
						}
						$r_pid = (int) $wlcc_archived_error_page;
						if ( is_int( $r_pid ) && $r_pid > 0 && ! isset( $archived_content[ $r_pid ] ) ) {
							$url = get_permalink( $r_pid );
						}
					} else {
						$url = add_query_arg( 'sp', 'archiver_error_page', wishlistmember_instance()->magic_page() );
						// if not set, save the default
						$pages_text = $WishListMemberInstance->get_option( 'archiver_error_page_text' );
						if ( ! $pages_text ) {
							$f = $WishListMemberInstance->legacy_wlm_dir . '/resources/page_templates/archiver_internal.php';
							if ( file_exists( $f ) ) {
								include $f;
							}
							$pages_text = $content ? nl2br( $content ) : '';
							// lets save it
							if ( $pages_text ) {
								$WishListMemberInstance->save_option( 'archiver_error_page_text', $pages_text );
								$WishListMemberInstance->save_option( 'archiver_error_page_type', 'text' );
							}
						}
					}
					if ( ! $url ) {
						$url = add_query_arg( 'sp', 'archiver_error_page', $this->magic_page() );
					}
					wp_redirect( $url );
					exit( 0 );
				}
			}

			return $query;
		}
		/*
		FUNCTIONS FOR FILTERING POSTS
		*/
		// functions used to filter the posts
		public function PostExpirationWhere( $where ) {
			global $wpdb,$WishListMemberInstance;
			$wpm_current_user = wp_get_current_user();
			$table            = $wpdb->prefix . 'posts';
			$levels           = array();
			$utype            = 'non_users';
			$w                = $where;
			if ( $wpm_current_user->caps['administrator'] ) {
				return $w;
			}
			// determine the user type
			if ( $wpm_current_user->ID > 0 ) {
				$levels = $this->get_users_level( $wpm_current_user->ID ); // get users membership levels
				// remove payper post membership level
				foreach ( (array) $levels as $id => $level ) {
					if ( false !== strpos( $level, 'U' ) ) {
						unset( $levels[ $id ] );
					}
				}

				if ( count( $levels ) > 0 ) {
					$utype = 'members';
				} else {
					$utype = 'non_members';
				}
			}

			// get the post with expiration date
			if ( count( $levels ) > 0 ) {
				$mlevel_post = $this->GetPostExpiryDate( '', $levels ); // get all the post with expiry date
			} else {
				$mlevel_post = $this->GetPostExpiryDate(); // if not logged in or dont have membership level
			}

			// levels that has archive date
			$posts_arc_levels = array();
			foreach ( (array) $mlevel_post as $lvl_post ) {
				$posts_arc_levels[ $lvl_post->post_id ][] = $lvl_post->mlevel;
			}

			// all levels ( with and without archive date)
			$posts_levels = array();
			foreach ( (array) $posts_arc_levels as $post_id => $arc_levels ) {
				$posts_levels[ $post_id ] = wishlistmember_instance()->get_content_levels( 'posts', $post_id );

				// Removing Protection from levels array.
				$protection_key = array_search( 'Protection', $posts_levels[ $post_id ] );
				if ( false !== $protection_key ) {
					unset( $posts_levels[ $post_id ][ $protection_key ] );
				}
				// Removing PayPerPost from levels array.
				$pay_per_post_key = array_search( 'PayPerPost', $posts_levels[ $post_id ] );
				if ( false !== $pay_per_post_key ) {
					unset( $posts_levels[ $post_id ][ $pay_per_post_key ] );
				}
				// Removing U-xxxx from levels array.
				foreach ( $posts_levels[ $post_id ] as $key => $ll ) {
					if ( false !== stripos( $ll, 'U-' ) ) {
						unset( $posts_levels[ $post_id ][ $key ] );
					}
				}
			}
			// post normal levels ( levels without archive date )
			$unexpired_posts = array();
			foreach ( (array) $posts_arc_levels as $post_id => $arc_levels ) {
				$post_non_arc_levels = array_diff(
					$posts_levels[ $post_id ],
					$arc_levels
				);
				// Removing Protection from levels array.
				$protection_key = array_search( 'Protection', $post_non_arc_levels );
				if ( false !== $protection_key ) {
					unset( $post_non_arc_levels[ $protection_key ] );
				}
				// Removing PayPerPost from levels array.
				$pay_per_post_key = array_search( 'PayPerPost', $post_non_arc_levels );
				if ( false !== $pay_per_post_key ) {
					unset( $post_non_arc_levels[ $pay_per_post_key ] );
				}
				// Removing U-xxxx from levels array.
				foreach ( $post_non_arc_levels as $key => $ll ) {
					if ( false !== stripos( $ll, 'U-' ) ) {
						unset( $post_non_arc_levels[ $key ] );
					}
				}
				// we dont archive the post if the post and the user has level(s) without any archive date.
				if ( ! empty( $post_non_arc_levels ) ) {
					$unexpired_posts[] = $post_id;
				}
			}
			$is_single = is_single() || is_page() ? true : false;
			if ( ! $is_single ) {
				$archiver_hide_post_listing = $WishListMemberInstance->get_option( 'archiver_hide_post_listing' );
				if ( $archiver_hide_post_listing ) {
					$expired_posts = $this->GetExpiredPost();
				} else {
					$expired_posts = array();
				}
			} else {
				$expired_posts = $this->GetExpiredPost();
			}
			$expired_posts = array_diff( $expired_posts, $unexpired_posts );
			// filter the post thats not to be shown
			if ( count( $expired_posts ) > 0 ) {
				$w .= " AND $table.ID NOT IN (" . implode( ',', $expired_posts ) . ')';
			}
			return $w;
		}
		// functions used to filter the next and previous links
		public function PostExpirationAdjacentWhere( $where ) {
			global $wpdb,$WishListMemberInstance,$post;
			$wpm_current_user  = wp_get_current_user();
			$current_post_date = $post->post_date;
			$w                 = $where;
			if ( ! $wpm_current_user->caps['administrator'] ) { // disregard content expiry for admin
				$expired_posts = $this->GetExpiredPost();
				// filter the post thats not to be shown
				if ( count( $expired_posts ) > 0 ) {
					$postids = implode( ',', $expired_posts ) . ',' . $post->ID;
					$w      .= ' AND p.ID NOT IN (' . $postids . ') ';
				}
			}
			return $w;
		}
		// functions used to filter the get_pages function
		public function GetPages( $pages, $args ) {
			global $wpdb, $WishListMemberInstance;
			if ( count( (array) $pages ) <= 0 ) {
				return $pages;
			}
			$wpm_current_user = wp_get_current_user();
			$levels           = array();
			$utype            = 'non_users';
			if ( ! $wpm_current_user->caps['administrator'] ) { // disregard archive content for admin

				// determine the user type
				if ( $wpm_current_user->ID > 0 ) {
					$levels = $this->get_users_level( $wpm_current_user->ID ); // get users membership levels
					// remove payper post membership level
					foreach ( (array) $levels as $id => $level ) {
						if ( false !== strpos( $level, 'U' ) ) {
							unset( $levels[ $id ] );
						}
					}

					if ( count( $levels ) > 0 ) {
						$utype = 'members';
					} else {
						$utype = 'non_members';
					}
				}

				$is_single     = false; // post listing always
				$expired_posts = array();
				if ( ! $is_single ) {
					$archiver_hide_post_listing = $WishListMemberInstance->get_option( 'archiver_hide_post_listing' );
					if ( $archiver_hide_post_listing ) {
						$expired_posts = $this->GetExpiredPost();
					}
				}

				if ( count( $expired_posts ) > 0 ) {
					foreach ( $pages as $pid => $page ) {
						if ( in_array( $page->ID, $expired_posts ) ) {
							unset( $pages[ $pid ] );
						}
					}
				}
			}
			return $pages;
		}
		/*
		OTHER FUNCTIONS NOT CORE OF CONTENT ARCHIVER GOES HERE
		*/
		/*
		 * FUNCTION to users membership levels
		*/
		public function get_users_level( $uid ) {
			global $WishListMemberInstance;
			static $levels  = false;
			static $user_id = false;
			if ( $user_id && $user_id == $uid && is_array( $levels ) ) {
				return $levels;
			}

			$user_id = $uid;
			if ( $user_id > 0 ) {
				if ( method_exists( $WishListMemberInstance, 'get_member_active_levels' ) ) {
					$levels = $WishListMemberInstance->get_member_active_levels( $user_id ); // get users membership levels
				} else {
					$levels = $WishListMemberInstance->get_membership_levels( $user_id, false, true ); // get users membership levels
				}
			} else {
				$levels = array();
			}

			return $levels;
		}
		/*
		 * FUNCTION to Save The current selection
		 * on the filter at the WL Content Archiver Dashboard
		*/
		public function SaveView() {
			$wpm_current_user = wp_get_current_user();
			if ( ! session_id() ) {
				session_start();
			}
			if ( $wpm_current_user->caps['administrator'] ) {
				if ( isset( wlm_post_data()['frmsubmit'] ) ) {
					$show_level                 = isset( wlm_post_data()['show_level'] ) ? wlm_post_data()['show_level'] : wlm_get_data()['show_level'];
					$show_post                  = isset( wlm_post_data()['show_post'] ) ? wlm_post_data()['show_post'] : wlm_get_data()['show_post'];
					$_SESSION['wlcceshowlevel'] = $show_level;
					$_SESSION['wlcceshowpost']  = $show_post;
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
		// function to format the date
		public function format_date( $date, $format = 'M j, Y g:i a' ) {
			$d1    = date_parse( $date );
			$pdate = mktime( $d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], $d1['year'] );
			$date  = wlm_date( $format, $pdate );
			return $date;
		}
		// function to get date difference needs php5.2
		public function isvalid_date( $date, $pid = 0 ) {
			$ret = false;
			if ( $pid <= 0 ) {
				if ( $date > wlm_date( 'Y-m-d H:i:s' ) ) {
					$ret = true;
				}
			} elseif ( $this->validateint( $pid ) ) {
				$post_details  = get_post( $pid );
				$post_date     = $post_details->post_date;
				$post_date_arr = date_parse( $post_date );
				$pdate         = wlm_date( 'Y-m-d H:i:s', mktime( (int) $post_date_arr['hour'], (int) $post_date_arr['minute'], 0, (int) $post_date_arr['month'], (int) $post_date_arr['day'], (int) $post_date_arr['year'] ) );
				if ( $date > $pdate ) {
					$ret = true;
				}
			}
			return $ret;
		}
		/*
		 * FUNCTION to Sort Multidimensional Arrays
		*/
		public function subval_sort( $a, $subkey, $sort = true, $asc = true ) {
			// sort the multidimensional array by key
			global $WishListMemberInstance;
			$c = array();
			if ( count( (array) $a ) > 0 ) {
				foreach ( $a as $k => $v ) {
					   $b[ $k ] = $v->$subkey;
				}
				if ( $asc ) {
					arsort( $b );
				} else {
					asort( $b );
				}
				foreach ( $b as $key => $val ) {
						$c[] = $a[ $key ];
						// save the post arrangement
						$d[] = $a[ $key ]->ID;
				}
				// save this if viewing post
				if ( ! is_single() && $sort ) {
					$WishListMemberInstance->save_option( 'wlcc_post_arr', $d );
				}
			}
				return $c;
		}
		// function to get date difference needs php5.2
		public function date_diff( $start, $end, $divisor = 0 ) {
			$d1        = date_parse( $start );
			$sdate     = mktime( $d1['hour'], $d1['minute'], $d1['second'], $d1['month'], $d1['day'], $d1['year'] );
			$d2        = date_parse( $end );
			$edate     = mktime( $d2['hour'], $d2['minute'], $d2['second'], $d2['month'], $d2['day'], $d2['year'] );
			$time_diff = $edate - $sdate;
			return $time_diff / $divisor;
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
