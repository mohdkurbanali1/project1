<?php
/**
 * Protection Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Protection Methods trait
 */
trait Protection_Methods {
	// -----------------------------------------
	// The Heart of It All
	public function process() {
		global $wp_query;

		// get current user
		$wpm_current_user = wp_get_current_user();

		// give everything if user is admin or super admin
		if ( ( isset( $wpm_current_user->caps['administrator'] ) && $wpm_current_user->caps['administrator'] ) || array_intersect( array( 'administrator' ), (array) $wpm_current_user->roles ) || current_user_can( 'manage_sites' ) ) {
			return;
		}

		// no protection for tag pages
		if ( is_tag() ) {
			return;
		}

		$wlm_is_category = $this->wlm_is_category();

		// ensure that the requested URL is the canonical URL
		redirect_canonical();

		// Construct Full Request URL
		$wpm_request_url = $this->request_url();

		/**
		 * Filters the redirect URL before it is set
		 *
		 * Allow others to hook at the very beginning of the protection
		 * process and let them do their own protection checks
		 *
		 * - (string) "STOP" - do not proceed with an further protection checking
		 * - (string) "NOACCESS", "CANCELLED", "EXPIRED", "FORCONFIRMATION", "FORAPPROVAL" - redirect to appropriate WLM error page
			 * - (string) Valid URL - redirect to this URL if post ID does not match post ID of $wpm_request_url
		 *
		 * @since 3.7
		 *
		 * @param string $redirect_url    The redirect URL
		 * @param string $wpm_request_url The currently requested URL
		 */
		$redirect_url = apply_filters( 'wishlistmember_process_protection', '', $wpm_request_url );
		/**
		 * Generate corresponding URL if $redirect_url is any of:
		 * NOACCESS, CANCELLED, EXPIRED, FORCONFIRMATION, FORAPPROVAL
		*/

		$is_error_page = false;
		if ( in_array( $redirect_url, array( 'NOACCESS', 'CANCELLED', 'EXPIRED', 'FORCONFIRMATION', 'FORAPPROVAL' ) ) ) {
			$is_error_page = true;
		}
		switch ( $redirect_url ) {
			case 'STOP': // don't proceed further with protection checking
				return;
				break;
			case 'NOACCESS':
				$redirect_url = is_user_logged_in() ? $this->wrong_level_url() : $this->non_members_url();
				break;
			case 'CANCELLED':
				$redirect_url = $this->cancelled_url();
				break;
			case 'EXPIRED':
				$redirect_url = $this->expired_url();
				break;
			case 'FORCONFIRMATION':
				$redirect_url = $this->for_confirmation_url();
				break;
			case 'FORAPPROVAL':
				$redirect_url = $this->for_approval_url();
				break;
		}
		if ( filter_var( $redirect_url, FILTER_VALIDATE_URL ) ) {

			// If the wishlistmember_process_protection filter returned an error page then just redirect it.
			if ( $is_error_page ) {
				wp_redirect( $redirect_url, 302, 'WishList Member' );
				exit;
			}

			/**
			 * To prevent a redirect loop, we Stop processing if Post IDs of $redirect_url
			 * and $wpm_request_url are the same. Otherwise, we redirect to $redirect_url.
			 */
			if ( url_to_postid( $redirect_url ) == url_to_postid( $wpm_request_url ) ) {
				return;
			} else {
				wp_redirect( $redirect_url, 302, 'WishList Member' );
			}
			exit;
		}

		// we're in a 404, no need to check for protection
		if ( is_404() ) {
			return;
		}

		// get all levels
		$wpm_levels = (array) $this->get_option( 'wpm_levels' );

		// process attachments
		if ( is_attachment() ) {
			$aid = $wp_query->query_vars['attachment_id'];
			if ( ! $aid && 'attachment' == $wp_query->post->post_type ) {
				$aid = $wp_query->post->ID;
			}
			$attachment = get_post( $aid );
			// no protection for attachment pages with no parent pages
			if ( ! $attachment->post_parent ) {
				// grant access, unprotected
				return;
			}

			/*
			 * check for protection inheritance from parent post and clone
			 * protection from the parent if inheritance is enabled
			 */
			$inherit = $this->special_content_level( $aid, 'Inherit' ) ? 'Y' : 'N';
			if ( 'Y' === $inherit ) {
				$type = 'page' == get_post_type( $attachment->post_parent ) ? 'pages' : 'posts';
				$this->clone_protection( $attachment->post_parent, $aid, $type, 'posts' );
			}
		}

		// process pages and posts
		if ( is_page() || is_single() ) {
			// grant access, WishList Member special page
			if ( in_array( $wp_query->post->ID, $this->exclude_pages( array() ) ) ) {
				return;
			}

			// grant access, special URL
			$regurl = $this->make_thankyou_url( '' );
			foreach ( (array) $wpm_levels as $wpml ) {
				if ( $regurl . $wpml['url'] == $wpm_request_url ) {
					return;
				}
			}

			// grant access, payperpost and user owns it
			if ( in_array( $wp_query->post->ID, $this->get_membership_content( $wp_query->post->post_type, 'U-' . $wpm_current_user->ID ) ) ) {
				return;
			}

			// Check if comment is protected
			$comment_protected = $this->special_content_level( $wp_query->post->ID, 'Protection', null, '~COMMENT' );

			// Hide comments if comment is protected and the user is not Logged in
			if ( $comment_protected ) {
				if ( ! $wpm_current_user->ID ) {
					add_filter( 'comments_template', array( $this, 'no_comments' ) );
				} else {

					$l_user_levels      = $this->get_membership_levels( $wpm_current_user->ID, $names, true );
					$protected_comments = $this->get_membership_content( 'comments', $l_user_levels );

					// If user doesn't have access to protected comment, hide it.
					if ( ! in_array( $wp_query->post->ID, $protected_comments ) ) {
						add_filter( 'comments_template', array( $this, 'no_comments' ) );
					}
				}
			}

			/*
			 * grant access, not protected
			 * note: post becomes protected if any of the following is true
			 * - it has protect_after_more is enabled and there is a more tag in the post content
			 * - it is marked as protected
			 */
			if ( ! ( $this->get_option( 'protect_after_more' ) && false !== strpos( $wp_query->post->post_content, '<!--more-->' ) ) && ! $this->protect( $wp_query->post->ID ) ) {
				// grant access, unprotected
				return;
			}
		}

		// process categories
		if ( $wlm_is_category || is_tax() ) {
			if ( $wlm_is_category ) { // category
				$cat_ID = get_query_var( 'cat' );
				// Following may happen when "Only show content for each membership level" is set to "yes" and catagory is protected and user have no access to this catagory.
				if ( empty( $cat_ID ) ) {
					$redirect = is_user_logged_in() ? $this->wrong_level_url() : $this->non_members_url();
						wp_redirect( $redirect, 302, 'WishList Member' );
					exit;
				}
			} else { // other taxonomy
				$cat_ID = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
				$cat_ID = $cat_ID->term_id;
			}

			// grant access, in a category but no $cat_ID or category is not protected at all
			if ( ! $cat_ID || ! $this->cat_protected( $cat_ID ) ) {
				return;
			}
		}

		/*
		 * If the page being viewed is not on the list of types we protect then return it.
		 * This fixes the issue where the homepage and 404 pages are being set as protected.
		 */
		if ( ! $wlm_is_category && ! is_tax() && ! is_attachment() && ! is_page() && ! is_single() ) {
			if ( apply_filters( 'wishlistmember_content_not_protected', true ) ) {
				return;
			}
		}

		/*
		 * At this point we know that protection is required for the content being requsted.
		 * All we're doing at this point is checking if the user has access to the content being
		 * requsted and performing the proper redirects.
		 */

		// non member URL is default redirect URL
		$redirect_url = $this->non_members_url();

		if ( $wpm_current_user->ID ) {
			$content_levels  = array();
			$comments_levels = array();

			// get required levels for content
			if ( $wlm_is_category || is_tax() ) {
				// get required levels for categories / taxonomies
				$content_levels = $this->get_content_levels( 'categories', $cat_ID );
			} else {
				// get required levels for posts, pages and attachments

				// get protected custom post types
				$protected_types = $this->get_option( 'protected_custom_post_types' );
				if ( ! is_array( $protected_types ) ) {
					$protected_types = array();
				}
				// add pages, posts and attachments to protected_post_types as we protect them by default
				$protected_types[] = 'pages';
				$protected_types[] = 'posts';
				$protected_types[] = 'attachment';

				// get current post type
				$post_type = get_post_type( $wp_query->post );

				// get required levels if we protect the post type
				if ( in_array( $post_type, array_merge( $protected_types, array( 'post', 'page' ) ) ) ) {
					$content_levels = $this->get_content_levels( $post_type, $wp_query->post->ID );
				}
				// get required levels for comments on this post
				$comments_levels = $this->get_content_levels( 'comments', $wp_query->post->ID );
			}
			// make sure $content_levels is an array
			if ( ! is_array( $content_levels ) ) {
				$content_levels = array();
			}
			// make sure $comments_levels is an array
			if ( ! is_array( $comments_levels ) ) {
				$comments_levels = array();
			}

			// get comment's protection status
			$comments_protected = in_array( 'Protection', $comments_levels );

			// get all of the current user's levels
			$the_user_levels = new \WishListMember\User( $wpm_current_user->ID );
			if ( $the_user_levels->ID ) {
				// set defaults
				$expired_levels     = array();
				$pending_levels     = array();
				$unconfirmed_levels = array();
				$cancelled_levels   = array();
				$active_levels      = array();

				$the_user_levels = $the_user_levels->Levels;

				$wlm_is_category = $wlm_is_category || is_tax();
				$is_page_post    = is_singular( array( 'page', 'post' ) );
				$is_page         = is_page();

				// go through each of the user's levels that match our required levels
				foreach ( $the_user_levels as &$level ) {
					if ( $level->Active ) {
						// allcategories checking
						if ( isset( $wpm_levels[ $level->Level_ID ]['allcategories'] ) && $wlm_is_category ) {
							// grant access, user has access to all categories and we're in a category so no further processing required
							return;
						}

						// keep track of active levels
						$active_levels[] = $level->Level_ID;

						if ( $is_page_post ) {
							if ( $is_page ) {
								// add level with allpages to $content_levels
								if ( isset( $wpm_levels[ $level->Level_ID ]['allpages'] ) ) {
									$content_levels[] = $level->Level_ID;
								}
							} else {
								// add level with allposts to $content_levels
								if ( isset( $wpm_levels[ $level->Level_ID ]['allposts'] ) ) {
									$content_levels[] = $level->Level_ID;
								}
							}
							// add level with allcomments to $comments_levels
							if ( isset( $wpm_levels[ $level->Level_ID ]['allcomments'] ) ) {
								$comments_levels[] = $level->Level_ID;
							}
						}
					}

					// status checking
					switch ( true ) {
						case $level->Cancelled:
							$cancelled_levels[] = $level->Level_ID;
							break;
						case $level->Expired:
							$expired_levels[] = $level->Level_ID;
							break;
						case $level->Pending:
							$pending_levels[] = $level->Level_ID;
							break;
						case $level->UnConfirmed:
							$unconfirmed_levels[] = $level->Level_ID;
							break;
					}
				}
				unset( $level );

				// remove active levels that are not required
				$active_levels = array_intersect( $active_levels, $content_levels );

				// remove active levels that are not required for comments
				$comments_levels = array_intersect( $active_levels, (array) $comments_levels );

				if ( $active_levels || ( is_page() && $allpages ) || ( is_singular( 'post' ) && $allposts ) ) {
					// grant access, we still have active levels left ( categories, posts, pages, custom post types )
					// but first we check for comment access if we're in a singular page
					if ( is_singular() ) {
						if ( $comments_protected && ! $comments_levels && ! $allcomments ) {
							// deny access to comments
							add_filter( 'comments_template', array( $this, 'no_comments' ) );
						}
					}
					// grant access
					return;
				} else {
					// deny access, no levels left. get $redirect_url prioritized as listed below
					switch ( true ) {
						case array_intersect( $unconfirmed_levels, $content_levels ):
							$redirect_url = $this->for_confirmation_url( $redirect_url );
							break;
						case array_intersect( $pending_levels, $content_levels ):
							$redirect_url = $this->for_approval_url( $redirect_url );
							break;
						case array_intersect( $expired_levels, $content_levels ):
							$redirect_url = $this->expired_url( $redirect_url );
							break;
						case array_intersect( $cancelled_levels, $content_levels ):
							$redirect_url = $this->cancelled_url( $redirect_url );
							break;
						default:
							$redirect_url = $this->wrong_level_url( $redirect_url );
					}
				}
			}
		}
		// still here? deny access
		wp_redirect( $redirect_url, 302, 'WishList Member' );
		exit;
	}

	/**
	 * Don't show comments.
	 * Called by 'comments_template' hook.
	 *
	 * @return string
	 */
	public function no_comments() {
		return ( WLM_PLUGIN_DIR . '/legacy/comments.php' );
	}

}

// register hooks.
add_action(
	'wishlistmember_register_hooks',
	function( $wlm ) {
		add_action( 'template_redirect', array( $wlm, 'process' ), 1 );
	}
);
