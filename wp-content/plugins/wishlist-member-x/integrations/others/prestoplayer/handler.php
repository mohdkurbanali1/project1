<?php
/*
 * PrestoPlayer Integration File
 * PrestoPlayer Site: https://prestoplayer.com/
 * Original Integration Author : Fel Jun Palawan
 * Version: $Id$
 */
if ( ! class_exists( 'WLM_OTHER_INTEGRATION_PRESTOPLAYER' ) ) {

	class WLM_OTHER_INTEGRATION_PRESTOPLAYER {
		private $settings            = array();
		public $prestoplayer_active  = false;
		private $presto_videos_table = 'presto_player_videos';

		public function __construct() {
			// check if PrestoPlayer LMS is active
			$active_plugins = wlm_get_active_plugins();
			if ( in_array( 'Presto Player', $active_plugins ) || isset( $active_plugins['presto-player/presto-player.php'] ) || is_plugin_active( 'presto-player/presto-player.php' ) ) {
				$this->prestoplayer_active = true;
			}
		}

		public function load_hooks() {
			if ( $this->prestoplayer_active ) {
				add_filter( 'wishlistmember_member_edit_tabs', array( $this, 'add_wlm_edit_member_tab' ), 10, 1 );
				add_action( 'wishlistmember_member_edit_tab_pane-presto_player', array( $this, 'wlm_edit_member_tab_content' ), 99, 1 );

				add_action( 'presto_player_progress', array( $this, 'record_progress' ), 10, 3 );
			}
		}

		public function add_wlm_edit_member_tab( $tabs ) {
			$tabs['presto_player'] = 'Presto Player';
			return $tabs;
		}


		public function record_progress( $video_id, $percent, $visit_time ) {
			global $wpdb;

			$table = $wpdb->prefix . 'wlm_presto_player_visits';

			$user_id = get_current_user_id();
			if ( ! $user_id ) {
				return;
			}

			$progress = $this->get_progress( $user_id, $video_id, $visit_time );
			if ( $progress && is_array( $progress ) && count( $progress ) > 0 ) {
				$progress = $progress[0];
				if ( $progress->percent < $percent ) {
					$this->update_progress( $progress->id, $percent );
				}
			} else {
				$this->add_progress( $user_id, $video_id, $percent, $visit_time );
			}
		}

		private function add_progress( $user_id, $video_id, $percent, $visit_time ) {
			global $wpdb;
			$table  = $wpdb->prefix . 'wlm_presto_player_visits';
			$data   = array(
				'user_id'    => $user_id,
				'video_id'   => $video_id,
				'percent'    => $percent,
				'visit_time' => $visit_time,
			);
			$format = array( '%d', '%d', '%d', '%d' );
			$wpdb->insert( $table, $data, $format );
		}

		private function get_progress( $user_id, $video_id, $visit_time ) {
			global $wpdb;
			$where = array();
			$table = $wpdb->prefix . 'wlm_presto_player_visits';

			return $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %0s WHERE user_id=%d AND visit_time=%d AND video_id=%d ORDER BY created_at',
					$table,
					$user_id,
					$visit_time,
					$video_id
				)
			);
		}

		private function update_progress( $id, $percent ) {
			global $wpdb;
			$table = $wpdb->prefix . 'wlm_presto_player_visits';
			$wpdb->query( $wpdb->prepare( 'UPDATE %0s SET percent=%d WHERE id=%d', $table, $percent, $id ) );
		}


		public function wlm_edit_member_tab_content( $userid ) {
			global $wpdb;
			$visits = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT v.id, v.visit_time, v.video_id, p.title, wp.post_title, p.post_id, v.user_id, v.percent, v.created_at, v.updated_at
	                FROM {$wpdb->prefix}wlm_presto_player_visits AS v
	                LEFT JOIN {$wpdb->prefix}%0s AS p ON v.video_id = p.id
	                LEFT JOIN {$wpdb->prefix}posts AS wp ON wp.ID = p.post_id
	                WHERE v.user_id = %d",
					$this->presto_videos_table,
					$userid
				)
			);

			include 'edit-tab.php';
		}

	}
}

$WLMPrestoPlayerInstance = new WLM_OTHER_INTEGRATION_PRESTOPLAYER();
$WLMPrestoPlayerInstance->load_hooks();
