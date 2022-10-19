<?php
/**
 * Plugin Update Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Plugin Update Methods trait
*/
trait Plugin_Update_Methods {
	/**
	 * Get plugin download URL
	 *
	 * @param  string $version Plugin version.
	 * @return string
	 */
	public function plugin_download_url( $version = '' ) {
		static $url;

		if ( empty( $version ) && wlm_arrval( $_REQUEST, 'wlm3_rollback' ) ) {
			$version = wlm_arrval( $_REQUEST, 'wlm3_rollback' );
		}
		if ( 1 != $this->get_option( 'LicenseStatus' ) ) {
			return false;
		}

		$license_key = wlm_trim( $this->get_option( 'LicenseKey' ) );
		if ( empty( $license_key ) ) {
			return 'WLMNOLICENSEKEY';
		}

		if ( ! $url ) {
			$url = 'http://wishlistproducts.com/download/' . $license_key . '/==' . base64_encode( pack( 'i', $this->ProductSKU ) );
		}
		if ( $version ) {
			return add_query_arg( 'version', $version, $url );
		}
		return $url;
	}

	/**
	 * Get WordPress plugin update URL
	 *
	 * @return string
	 */
	public function plugin_update_url() {
		return wp_nonce_url( 'update.php?action=upgrade-plugin&plugin=' . $this->PluginFile, 'upgrade-plugin_' . $this->PluginFile );
	}

	/**
	 * Get plugin latest verison
	 *
	 * @return string
	 */
	public function plugin_latest_version() {
		static $latest_wpm_ver;
		$varname = 'WishListMember_Latest_Plugin_Version';
		if ( empty( $latest_wpm_ver ) || isset( wlm_get_data()['checkversion'] ) ) {
			$latest_wpm_ver = get_transient( $varname );
			if ( empty( $latest_wpm_ver ) || isset( wlm_get_data()['checkversion'] ) ) {
				$latest_wpm_ver = $this->ReadURL(
					array(
						sprintf( 'http://wishlistproducts.com/download/ver.php?%s/%s', $this->ProductSKU, $this->Version ),
						sprintf( 'http://wishlistactivation.com/versioncheck/?%s/%s', $this->ProductSKU, $this->Version ),
					),
					3
				);
				if ( empty( $latest_wpm_ver ) ) {
					// we failed, set the latest version to this one so that we won't keep checking again for today.
					$latest_wpm_ver = $this->Version;
				}
				// even if we fail never try again for this day.
				set_transient( $varname, $latest_wpm_ver, 60 * 60 * 24 );
			}
		}
		return $latest_wpm_ver;
	}

	/**
	 * Check if plugin is latest version.
	 *
	 * @return boolean
	 */
	public function plugin_is_latest() {
		$latest_ver = $this->plugin_latest_version();
		$ver        = $this->Version;
		if ( preg_match( implode( '', array( '/^(\d+\.\d+)\.{', 'GLOBALREV}/' ) ), $this->Version, $match ) ) {
			$ver = $match[1];
			preg_match( '/^(\d+\.\d+)\.[^\.]*/', $latest_ver, $match );
			$latest_ver = $match[1];
		}
		return version_compare( $latest_ver, $ver, '<=' );
	}
}
