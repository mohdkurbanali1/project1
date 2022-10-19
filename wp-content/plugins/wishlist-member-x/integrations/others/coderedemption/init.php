<?php
$class = '\WishListMember\Integrations\Others\CodeRedemption';

// admin interface actions
add_action( 'wp_ajax_wlm_coderedemption_save_campaign', array( $class, 'save_campaign' ) );
add_action( 'wp_ajax_wlm_coderedemption_delete_campaign', array( $class, 'delete_campaign' ) );
add_action( 'wp_ajax_wlm_coderedemption_generate_codes', array( $class, 'generate_codes' ) );
add_action( 'wp_ajax_wlm_coderedemption_search_codes', array( $class, 'search_codes' ) );
add_action( 'wp_ajax_wlm_coderedemption_import_codes', array( $class, 'import_codes' ) );
add_action( 'wp_ajax_wlm_coderedemption_export_codes', array( $class, 'export_codes' ) );
add_action( 'wp_ajax_wlm_coderedemption_delete_code', array( $class, 'delete_code' ) );
add_action( 'wp_ajax_wlm_coderedemption_cancel_code', array( $class, 'cancel_code' ) );
add_action( 'wp_ajax_wlm_coderedemption_uncancel_code', array( $class, 'uncancel_code' ) );
add_filter( 'wishlistmember_integration_shortcodes', array( $class, 'add_shortcode_to_manifest' ) );
add_filter( 'wlm_after_login_redirect', array( $class, 'login_redirect' ), 99999 );

// claim form
add_shortcode( 'wlm_coderedemption', array( $class, 'shortcode_coderedemption' ) );
add_action( 'wp_loaded', array( $class, 'claim_code_from_form' ) );

// create table
add_action(
	'wp_loaded',
	function() {
		$settings = wishlistmember_instance()->get_option( 'coderedemption_settings' );
		if ( wishlistmember_instance()->Version != wlm_arrval( $settings, 'table_version' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$table     = wishlistmember_instance()->TablePrefix . 'coderedemption';
			$structure = "CREATE TABLE $table (
      ID bigint(20) NOT NULL AUTO_INCREMENT,
      campaign_id bigint(20) NOT NULL,
      code varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
      status tinyint(4) NOT NULL DEFAULT 0,
      claimed datetime DEFAULT NULL,
      cancelled datetime DEFAULT NULL,
      user_id bigint(20) DEFAULT NULL,
      PRIMARY KEY  (ID),
      UNIQUE KEY campaign_id_code (campaign_id,code),
      KEY campaign_id (campaign_id),
      KEY status (status),
      KEY user_id (user_id)
    ) {$charset_collate};";
			dbDelta( $structure );
			wishlistmember_instance()->table_names->$table = $table;
			if ( ! is_array( $settings ) ) {
				$settings = array();
			}
			$settings['table_version'] = wishlistmember_instance()->Version;
			wishlistmember_instance()->save_option( 'coderedemption_settings', $settings );
		}
	}
);
