<?php
/*
 * Gutenberg Integration File
 * Original Integration Author : Ronaldo Reymundo
 * Version: $Id$
 */

if ( ! class_exists( 'WLM_OTHER_INTEGRATION_GUTENBERG' ) ) {

	class WLM_OTHER_INTEGRATION_GUTENBERG {
		public $plugin_dir;

		public function __construct() {
			$this->plugin_dir = plugin_dir_url( __FILE__ );
		}

		public function load_hooks() {
			add_action( 'admin_init', array( $this, 'admin_enqueue_scripts' ), 9999, 2 );
			add_filter( 'render_block', array( $this, 'process_blocks_restriction' ), 10, 2 );
		}

		public function admin_enqueue_scripts() {
			global $wp_version, $current_screen;
			if ( version_compare( $wp_version, '5.0', '>=' ) ) {
				global $pagenow;
				if ( ( 'post.php' === $pagenow ) || ( 'post' == get_post_type() ) ) {
					wp_register_style( 'wlm_guten_block', $this->plugin_dir . 'wlm-blocks.css', array(), WLM_PLUGIN_VERSION );
					wp_enqueue_style( 'wlm_guten_block' );
					$this->load_gutenberg_block_js();
				}
			}
		}

		public function load_gutenberg_block_js() {

			// filter if we want to disable this feature.
			$disable_script = apply_filters( 'wlm_disable_blocks_script', false );
			if ( $disable_script ) {
				return;
			}

			wp_register_script( 'wlm_block_js', $this->plugin_dir . 'wlm-blocks.js', array( 'wp-editor', 'wp-i18n', 'wp-blocks', 'wp-components', 'wp-hooks' ), WLM_PLUGIN_VERSION, true );
			wp_set_script_translations( 'wlm_block_js', 'wishlist-member' );

			$wpm_levels = wishlistmember_instance()->get_option( 'wpm_levels' );
			if ( ! empty( $wpm_levels ) ) {
				foreach ( $wpm_levels as $key => $val ) {
					$m_levels[] = array(
						'label' => $val['name'],
						'value' => $key,
					);
				}
			}
			wp_localize_script( 'wlm_block_js', 'wlm_restrict_blocks', $m_levels );
			wp_enqueue_script( 'wlm_block_js' );
		}

		public function process_blocks_restriction( $block_content, $block ) {

			if ( is_admin() ) {
				return $block_content;
			}

			if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
				return $block_content;
			}

			if ( ! isset( $block['attrs']['wlm_block_restriction'] ) || true !== $block['attrs']['wlm_block_restriction'] ) {
				return $block_content;
			}

			$wlm_block_access = $block['attrs']['wlm_block_access'];

			if ( empty( $wlm_block_access ) ) {
				return $block_content;
			}

			$message_type    = $block['attrs']['wlm_message_type'];
			$message_content = $block['attrs']['wlm_message_content'];

			// $wlm_block_access (Values are 1. Everyone, 2. Members of a level, 3. Non-Member, 4. Logged in, 5. Logged Out)
			switch ( $wlm_block_access ) {
				case '1':
				case '2':
					// If user is not logged in then just hide block or show the restrict message.
					if ( ! is_user_logged_in() ) {
						$block_content = '';
						if ( isset( $message_type ) ) {
							if ( '2' == $message_type ) {
								$block_content = $message_content;
							}
						}
						$block_content = $message_content;
					} else {
						// Logged in so get the User's levels and then match it with the the level access saved in the block.
						$wpm_current_user   = wp_get_current_user();
						$user_levels        = new \WishListMember\User( $wpm_current_user->ID );
						$user_active_levels = array();
						foreach ( $user_levels->Levels as $user_level ) {
							if ( $user_level->Active ) {
								$user_active_levels[] = $user_level->Level_ID; // Only get Active Levels
							}
						}

						$match = array_intersect( $user_active_levels, $block['attrs']['wlm_level_access'] );

						if ( 1 === (int) $wlm_block_access ) {
							if ( ! $match ) {
								$block_content = '';
								if ( isset( $message_type ) ) {
									if ( '2' == $message_type ) {
										$block_content = $message_content;
									}
								}
								$block_content = $message_content;
							}
						} elseif ( 2 === (int) $wlm_block_access ) {
							if ( $match ) {
								$block_content = '';
								if ( isset( $message_type ) ) {
									if ( '2' == $message_type ) {
										$block_content = $message_content;
									}
								}
								$block_content = $message_content;
							}
						}
					}
					break;
				case '3':  // Logged in Users.
					if ( ! is_user_logged_in() ) {
						$block_content = '';
						if ( isset( $message_type ) ) {
							if ( '2' == $message_type ) {
								$block_content = $message_content;
							}
						}
					}
					break;
				case '4':  // Logged out users.
					if ( is_user_logged_in() ) {
						$block_content = '';
						if ( isset( $message_type ) ) {
							if ( '1' == $message_type ) {
								$block_content = $default_message;
							} elseif ( '2' == $message_type ) {
								$block_content = $message_content;
							}
						}
					}
					break;
			}
			return $block_content;
		}
	}
}

$WLMElementorInstance = new WLM_OTHER_INTEGRATION_GUTENBERG();
$WLMElementorInstance->load_hooks();
