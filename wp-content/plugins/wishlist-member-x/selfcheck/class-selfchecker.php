<?php
/**
 * SelfChecker class file
 *
 * @package WishListMember/SelfCheck
 */

/**
 * SelfChecker class
 */
class SelfChecker {

	/**
	 * Report pretty printer
	 *
	 * @param array $data Report data.
	 */
	public function pp_report( $data ) {
		$report = '<table class="table" id="selfchecktable">';
		foreach ( $data as $item ) {
			if ( is_array( $item['result'] ) ) {
				list($res, $msg) = $item['result'];
			} else {
				$res = $item['result'];
			}

			if ( true !== $res ) {
				$result = 'images/close-black-48dp.svg';
			} else {
				$result = 'images/check-black-48dp.svg';
			}
			$report .= '<tr>';
			$report .= '<td class="info py-5"><h3 class="mb-2 font-weight-bold">' . $item['name'] . '<a class="btn btn-sm ml-3 mt-n1 btn-outline-primary read-more" target="_blank" href="' . $item['kb'] . '">Read more &#8594;</a></h3><p>' . $item['description'];
			if ( true !== $res ) {
				if ( ! empty( $msg['link'] ) ) {
					$report .= '<span class="error text-danger mt-3 d-block"><strong>Error:</strong> <a target="_blank" class="text-danger" href="' . $msg['link'] . '">' . $msg['msg'] . '</a></span>';
				} else {
					$report .= '<br /><span class="error text-danger mt-3 d-block"><strong>Error:</strong> ' . $msg['msg'] . '</span>';
				}
			}
			$report .= '</p></td>';
			$report .= '<td class="result align-middle"><p><img src="' . esc_attr( $result ) . '" alt="" /></p></td>';
			$report .= '</tr>';
		}
		$report .= '</table>';

		return $report;
	}

	/*
	 * Starts wlm self-check
	 */

	/**
	 * Generate selfcheck report data
	 *
	 * @return array Report data.
	 */
	public function check() {
		$report = array();
		/** File Consistency Check * */
		$file_hash_check                            = $this->check_file_hashes();
		$report['check_file_hashes']['name']        = 'Files Consistency Check';
		$report['check_file_hashes']['description'] = 'This check ensures that all files within the WishList Member install are consistent and will detect any possible corrupt files that can occasionally occur during an FTP upload.';
		$report['check_file_hashes']['kb']          = 'https://customers.wishlistproducts.com/article/wishlist-member-self-check-consistency-check/';
		$report['check_file_hashes']['result']      = $file_hash_check;
		/** PHP Version Check * */
		$report['php_ver_check']['name']        = 'PHP Version Check';
		$report['php_ver_check']['description'] = 'This check ensures that a compatible version of PHP is installed and running on your server.  WishList Member requires PHP 5.6.0 or greater in order be installed and function correctly.';
		$report['php_ver_check']['kb']          = 'https://customers.wishlistproducts.com/article/required-php-version-for-wishlist-member/';
		$report['php_ver_check']['result']      = $this->php_ver_check();
		/** WordPress Version Check * */
		$report['wp_ver_check']['name']        = 'WordPress Version Check';
		$report['wp_ver_check']['description'] = 'This check ensures that the installed version of WordPress is recent enough to support the current version of WishList Member. WordPress 5.0.0 or greater is required in order to install WishList Member.';
		$report['wp_ver_check']['description'] = 'This check ensures that the installed version of WordPress is recent enough to support the current version of WishList Member. WordPress 5.0.0 or greater is required in order to install WishList Member.';
		$report['wp_ver_check']['kb']          = 'https://customers.wishlistproducts.com/article/required-wordpress-version/';
		$report['wp_ver_check']['result']      = $this->wp_ver_check();
		/** Activation connectivity test * */
		$report['connectivity_check']['name']        = 'Activation & Updates Connectivity';
		$report['connectivity_check']['description'] = 'This check ensures that your server will allow your site to connect with the WishList Member Activation and Update Center. This enables license activation and the ability to display notifications of WishList Member version updates on your WL Dashboard.';
		$report['connectivity_check']['kb']          = 'https://customers.wishlistproducts.com/article/activation-and-updates-connectivity/';
		$report['connectivity_check']['result']      = $this->connectivity_check();
		/** As requested by jen * */
		$report['magic_page_check']['name']        = 'Magic Page Check';
		$report['magic_page_check']['description'] = 'This check ensures that the WishList Member "Magic Page" is published. This mandatory page which must exist in order to process member registrations will appear as "WishList Member" in the WordPress Pages section and should not be deleted or edited. Note that this check is only applicable when WishList Member is activated.';
		$report['magic_page_check']['kb']          = 'https://customers.wishlistproducts.com/article/wishlist-member-magic-page/';
		$report['magic_page_check']['result']      = $this->magic_page_check();
		/** As requested by andy * */
		$report['memory_limit_check']['name']        = 'Memory Limit Check';
		$report['memory_limit_check']['description'] = 'This check ensures that the system has enough memory allocated to run WishList Member';
		$report['memory_limit_check']['kb']          = 'https://customers.wishlistproducts.com/article/memory-error-message-increase-wordpress-memory/';
		$report['memory_limit_check']['result']      = $this->memory_check();

		return $report;
	}

	/**
	 * PHP version check
	 *
	 * @return array
	 */
	public function php_ver_check() {
		$min_version = '5.6.0';
		$status      = strnatcmp( phpversion(), $min_version ) >= 0;
		$msg         = array(
			'msg'  => "Server is required to have PHP version $min_version at a minimum for WishList Member. You currently have PHP version " . phpversion() . ' installed.',
			'link' => 'https://support.wishlistproducts.com/helpdesk.htm?article=727',
		);
		if ( $status ) {
			return true;
		}
		return array( $status, $msg );
	}

	/**
	 * WordPress version check
	 *
	 * @return array
	 */
	public function wp_ver_check() {
		/** Include wp's version file * */
		$wp_include_dir = WP_BASE_PATH . '/wp-includes/version.php';
		include_once $wp_include_dir;
		if ( ! isset( $wp_version ) ) {
			$msg = array(
				'msg'  => "Unreliable. WordPress' version file is not in the typical location and could not be found.",
				'link' => 'https://support.wishlistproducts.com/helpdesk.htm?article=720',
			);
			return array( false, $msg );
		}
		$min_version = '5.0.0';
		$status      = strnatcmp( $wp_version, $min_version ) >= 0;
		$msg         = array(
			'msg'  => "WordPress version must be $min_version at a minimum to allow WishList Member to be installed and run but the WordPress version is currently $wp_version",
			'link' => 'https://support.wishlistproducts.com/helpdesk.htm?article=729',
		);
		if ( $status ) {
			return true;
		}
		return array( $status, $msg );
	}

	/**
	 * Check file hashes
	 *
	 * @return array
	 */
	public function check_file_hashes() {
		$passed    = false;
		$base_path = dirname( __DIR__ ) . '/';
		$hash_file = __DIR__ . '/hashes.txt';

		if ( ! is_readable( $hash_file ) ) {
			$msg = array(
				'msg'  => '"Unreliable, hash file was not in its typical location and could not be found"',
				'link' => 'https://support.wishlistproducts.com/helpdesk.htm?article=730',
			);
			return array( false, $msg );
		}
		$hashes = file_get_contents( $hash_file );

		foreach ( explode( "\n", $hashes ) as $h ) {
			if ( ! empty( $h ) ) {
				list($hash, $file) = preg_split( '/\s+/', $h );
				$test              = $base_path . $file;

				if ( ! is_readable( $test ) ) {
					$msg = array(
						'msg'  => '"Unreliable, hash file was not in its typical location and could not be found"',
						'link' => 'https://support.wishlistproducts.com/helpdesk.htm?article=730',
					);
					return array( false, $msg );
				}
				if ( md5( file_get_contents( $test ) ) !== $hash ) {
					$msg = array(
						'msg'  => 'The WishList Member Self Check found inconsistencies in some of the WishList Member files. Please re-upload WishList Member preferably using the WordPress plugin uploader.',
						'link' => 'https://support.wishlistproducts.com/helpdesk.htm?article=733',
					);
					return array( false, $msg );
				}
			}
		}
		return true;
	}

	/**
	 * Server connectivity check
	 *
	 * @return array
	 */
	public function connectivity_check() {
		$wp_load_dir = WP_BASE_PATH . '/wp-load.php';
		include_once $wp_load_dir;

		$wp_include_dir = WP_BASE_PATH . '/wp-includes/class-http.php';
		include_once $wp_include_dir;
		$uris           = array(
			'https://wishlistproducts.com/download/ver.php?wlm',
			'https://wishlistactivation.com/versioncheck/?wlm',
		);
		$remote_allowed = ini_get( 'allow_url_fopen' );
		if ( ! $remote_allowed ) {
			$msg = array(
				'msg'  => 'Remote connection not allowed by host.',
				'link' => 'https://customers.wishlistproducts.com/article/activation-and-updates-connectivity/',
			);
			return array( false, $msg );
		}

		foreach ( $uris as $u ) {
			$doc = file_get_contents( $u );
			if ( false === $doc ) {
				$msg = array(
					'msg'  => "Connection to \"$u\" failed",
					'link' => 'https://customers.wishlistproducts.com/article/connection-to-xxxx-url-failed/',
				);
				return array( false, $msg );
			}

			if ( 0 === preg_match( '/\d+\.\d+\.\d+/', $doc ) ) {
				$msg = array(
					'msg'  => "\"$u\" gave an unexpected response: " . htmlspecialchars( $doc ),
					'link' => 'https://customers.wishlistproducts.com/article/xxxx-url-gave-an-unexpected-response/',
				);
				return array( false, $msg );
			}
		}
		return true;
	}

	/**
	 * Check for existence of WishList Member magic page
	 *
	 * @return array
	 */
	public function magic_page_check() {
		// short circuit WordPress XD.
		defined( 'SHORTINIT' ) || define( 'SHORTINIT', true );
		$wp_include_dir = WP_BASE_PATH . '/wp-config.php';
		include_once $wp_include_dir;
		global $wpdb;

		$res = $wpdb->get_results( "SELECT * FROM `$wpdb->posts` WHERE `post_date` = '2000-01-01 00:00:00' AND `post_type` = 'page' AND post_status='publish'" );

		if ( count( $res ) <= 0 ) {
			$msg = array(
				'msg'  => 'The "Magic Page" has been deleted',
				'link' => 'https://support.wishlistproducts.com/helpdesk.htm?article=721',
			);
			return array( false, $msg );
		}
		if ( count( $res ) > 1 ) {
			$msg = array(
				'msg'  => 'There are multiple "Magic Pages" published.',
				'link' => 'https://support.wishlistproducts.com/helpdesk.htm?article=731',
			);
			return array( false, $msg );
		}

		return true;
	}

	/**
	 * Memory limit check
	 *
	 * @return array
	 */
	public function memory_check() {
		$recommended_memory_limit = '64M';
		$actual_memory_limit      = ini_get( 'memory_limit' );

		if ( empty( $actual_memory_limit ) ) {
			return array(
				false,
				array( 'msg' => 'Unreliable. The memory limit value has not been set' ),
			);
		}

		if ( $this->return_bytes( $actual_memory_limit ) < $this->return_bytes( $recommended_memory_limit ) ) {
			$message = array( 'msg' => sprintf( 'The recommended memory size is %s, but the actual memory limit allocated is only %s', $recommended_memory_limit, $actual_memory_limit ) );
			return array( false, $message );
		}
		return true;
	}

	/**
	 * Convert size string to bytes
	 *
	 * @param  string $size_str Size string (ie. 32M).
	 * @return integer
	 */
	public function return_bytes( $size_str ) {
		switch ( substr( $size_str, -1 ) ) {
			case 'M':
			case 'm':
				return (int) $size_str * 1048576;
			case 'K':
			case 'k':
				return (int) $size_str * 1024;
			case 'G':
			case 'g':
				return (int) $size_str * 1073741824;
			default:
				return $size_str;
		}
	}
}
