<?php
/**
 * Folder Protection Methods Feature
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Folder Protection Methods trait
*/
trait Folder_Protection_Methods {
	/**
	 * Adds/removes .htaccess in protected folders
	 *
	 * @param boolean $install True to install. False to remove. Default true.
	 */
	public function folder_protection_htaccess( $install = true ) {
		$parent_folder = $this->folder_protection_full_path( $this->get_option( 'rootOfFolders' ) );
		if ( ! is_dir( $parent_folder ) ) {
			return;
		}

		$folders = glob( $parent_folder . '/*', GLOB_ONLYDIR );
		if ( empty( $folders ) ) {
			return;
		}
		foreach ( $folders as $folder ) {
			$folder_id = $this->folder_id( $folder );
			$this->folder_protect_htaccess( $parent_folder . '/' . basename( $folder ), $install );
		}
	}

	/**
	 * Adds .htaccess to protected folders
	 */
	public function add_htaccess_to_protected_folders() {
		$this->folder_protection_htaccess( true );
	}

	/**
	 * Removes .htaccess from protected folders
	 */
	public function remove_all_htaccess_from_protected_folders() {
		$this->folder_protection_htaccess( false );
	}

	/**
	 * Get folder's "force download" status.
	 *
	 * @param string $folder Folder Path.
	 * @return boolean
	 */
	public function get_folder_protect_force_download( $folder ) {
		return $this->folder_force_download( $this->folder_id( $folder ) );
	}

	/**
	 * Get all levels of a folder in an array
	 *
	 * @param string $folder Folder Path.
	 * @return array Membership Levels
	 */
	public function get_folder_levels( $folder ) {
		return $this->get_content_levels( 'folders', $this->folder_id( $folder ), false, false );
	}

	/**
	 * Processes Folder protection
	 *
	 * @param string $wlm_folder Folder to process (relative to the Root of folders option).
	 * @param string $wlm_file   File to download.
	 */
	public function folder_protect( $wlm_folder, $wlm_file ) {

		$folder_id = $this->folder_id( $wlm_folder );

		$wlm_file = $this->get_option( 'rootOfFolders' ) . '/' . $wlm_folder . '/' . $wlm_file;
		if ( ! file_exists( $wlm_file ) ) {
			// file does not exist.
			header( 'HTTP/1.0 404 Not Found' );
			print( '404 - File Not Found' );
			exit;
		}

		$force_download = $this->folder_force_download( $folder_id );
		$user           = wp_get_current_user();

		if ( ! $this->folder_protected( $folder_id ) || $user->caps['administrator'] ) {
			// folder not protected or user is admin.
			$this->download( $wlm_file, $force_download );
			exit;
		}

		$redirect = false;

		if ( ! $user->ID ) {
			// not logged in.
			header( sprintf( 'Location:%s', $this->non_members_url() ) );
			exit;
		}

		$ulevels = $this->get_membership_levels( $user->ID, null, null, null, true );
		$levels  = array_intersect( $this->get_folder_levels( $wlm_folder ), $ulevels );

		if ( ! count( $levels ) ) {
			// no valid levels.
			header( sprintf( 'Location:%s', $this->wrong_level_url() ) );
			exit;
		}

		// remove expired levels.
		foreach ( (array) $levels as $key => $level ) {
			if ( $this->level_expired( $level, $user->ID ) ) {
				unset( $levels[ $key ] );
			}
		}
		if ( ! count( $levels ) ) {
			header( sprintf( 'Location:%s', $this->expired_url() ) );
			exit;
		}

		// remove unconfirmed levels.
		foreach ( (array) $levels as $key => $level ) {
			if ( $this->level_unconfirmed( $level, $user->ID ) ) {
				unset( $levels[ $key ] );
			}
		}
		if ( ! count( $levels ) ) {
			header( sprintf( 'Location:%s', $this->for_confirmation_url() ) );
			exit;
		}

		// remove forapproval levels.
		foreach ( (array) $levels as $key => $level ) {
			if ( $this->level_for_approval( $level, $user->ID ) ) {
				unset( $levels[ $key ] );
			}
		}
		if ( ! count( $levels ) ) {
			header( sprintf( 'Location:%s', $this->for_approval_url() ) );
			exit;
		}

		// remove cancelled levels.
		foreach ( (array) $levels as $key => $level ) {
			if ( $this->level_cancelled( $level, $user->ID ) ) {
				unset( $levels[ $key ] );
			}
		}
		if ( ! count( $levels ) ) {
			header( sprintf( 'Location:%s', $this->cancelled_url() ) );
			exit;
		}

		// all is well. release the kraken!
		$this->download( $wlm_file, $force_download );
		exit;
	}

	/**
	 * Adds/Removes .htaccess code to the protected upload folders
	 *
	 * @param  string  $folder_full_path Full path to protected folder.
	 * @param  boolean $install          True to install. False to remove. Default true.
	 */
	public function folder_protect_htaccess( $folder_full_path, $install = true ) {

		if ( empty( $this->get_option( 'rootOfFolders' ) ) ) {
			return false;
		}
		$folder = basename( $folder_full_path );

		if ( ! $this->get_option( 'folder_protection' ) ) {
			$install = false;
		}

		if ( is_dir( $folder_full_path ) ) {
			// Folder protection code markers.
			$htaccess_start = '# BEGIN WishList Member Folder Protection';
			$htaccess_end   = '# END WishList Member Folder Protection';

			// Apache - read .htaccess.
			$htaccess_file = $folder_full_path . '/.htaccess';
			$htaccess      = file_exists( $htaccess_file ) ? file_get_contents( $htaccess_file ) : '';
			// Apache - remove our .htaccess code.
			list($start)   = explode( $htaccess_start, $htaccess );
			list($x, $end) = explode( $htaccess_end, $htaccess );
			$htaccess      = trim( wlm_trim( $start ) . "\n" . wlm_trim( $end ) );

			// Nginx - read config.
			$nginx_file = $this->wp_upload_path . '/wlm_file_protect_nginx.conf';
			$nginx      = file_exists( $nginx_file ) ? trim( file_get_contents( $nginx_file ) ) : '';
			// Apache - remove our config code.
			list($start)   = explode( $htaccess_start, $nginx );
			list($x, $end) = explode( $htaccess_end, $nginx );
			$nginx         = trim( wlm_trim( $start ) . "\n" . wlm_trim( $end ) );

			if ( $install ) {
				/*
				* Apache - prepare htaccess code
				*/
				$siteurl   = parse_url( get_option( 'home' ) );
				$siteurl   = $siteurl['path'] . '/index.php';
				$htaccess .= "\n{$htaccess_start}";
				$htaccess .= "\nOptions FollowSymLinks";
				$htaccess .= "\nRewriteEngine on";
				$htaccess .= "\nRewriteRule ^(.*)$ {$siteurl}?wlmfolder={$folder}&restoffolder=$1 [L]";
				$htaccess .= "\n{$htaccess_end}";

				/*
				* Nginx - prepare config code
				*/
				$nginx_header = "# Include this file in your site configuration's server {} block";
				$nginx        = "{$nginx_header}\n\n" . trim( str_replace( $nginx_header, '', $nginx ) );

				$base_url = site_url( $this->get_option( 'parentFolder' ), 'relative' );
				$full_url = site_url();
				$nginx   .= "\n$htaccess_start\n";
				$nginx   .= "location {$base_url} {\n";
				$nginx   .= "\trewrite ^{$base_url}/(.+?)/(.+)$ {$full_url}?wlmfolder=$1&restoffolder=$2 break;\n";
				$nginx   .= "}\n";
				$nginx   .= "$htaccess_end\n";
			}
			// Apache - write .htaccess.
			file_put_contents( $htaccess_file, wlm_trim( $htaccess ) . "\n" );

			// Nginx - write config.
			file_put_contents( $nginx_file, wlm_trim( $nginx ) . "\n" );
		}
	}

	/**
	 * Convert string to slug
	 *
	 * @param  string $string String to convert.
	 * @return string         Slug.
	 */
	public function string_to_slug( $string ) {
		$utf8 = array(
			'/[áàâãªä]/u' => 'a',
			'/[ÁÀÂÃÄ]/u'  => 'A',
			'/[ÍÌÎÏ]/u'   => 'I',
			'/[íìîï]/u'   => 'i',
			'/[éèêë]/u'   => 'e',
			'/[ÉÈÊË]/u'   => 'E',
			'/[óòôõºö]/u' => 'o',
			'/[ÓÒÔÕÖ]/u'  => 'O',
			'/[úùûü]/u'   => 'u',
			'/[ÚÙÛÜ]/u'   => 'U',
			'/ç/'         => 'c',
			'/Ç/'         => 'C',
			'/ñ/'         => 'n',
			'/Ñ/'         => 'N',
			'/–/'         => '-', // UTF-8 hyphen to "normal" hyphen.
			'/[’‘‹›‚]/u'  => ' ', // Literally a single quote.
			'/[“”«»„]/u'  => ' ', // Double quote.
			'/ /'         => ' ', // nonbreaking space (equiv. to 0x160).
		);
		$slug = preg_replace( array_keys( $utf8 ), array_values( $utf8 ), is_null( $string ) ? '' : $string );
		$slug = sanitize_title_with_dashes( $slug );
		return $slug;
	}

	/**
	 * Setup Easy Folder Protection
	 */
	public function easy_folder_protection() {
		global $wpdb;

		// reset.
		$wpdb->query( 'DELETE FROM `' . esc_sql( $this->table_names->contentlevels ) . '` WHERE `type`="~FOLDER"' );

		// some clean up.
		$default_parent_folder_name = 'files';

		$root_of_folders = ABSPATH . '/' . $default_parent_folder_name;
		$this->save_option( 'rootOfFolders', $root_of_folders );

		if ( ! is_dir( $root_of_folders ) ) {
			// if folder does not exist, we create it.
			if ( ! mkdir( $root_of_folders ) ) {
				$this->err = __( '<b>Could not create folder.</b><br>', 'wishlist-member' );
				return false;
			}
		}

		$this->save_option( 'parentFolder', $default_parent_folder_name );

		$wpm_levels = $this->get_option( 'wpm_levels' );

		foreach ( (array) $wpm_levels as $level_id => $level ) {
			$level_name = $level['name'];
			$subfolder  = $root_of_folders . '/' . $this->string_to_slug( $level_name );
			if ( ! is_dir( $subfolder ) ) {
				mkdir( $subfolder );
			}
			$folder_id = $this->folder_id( $subfolder );
			$this->folder_protected( $folder_id, true );
			$this->set_content_levels( 'folders', $folder_id, $level_id );
		}

		$this->remove_all_htaccess_from_protected_folders();
		$this->add_htaccess_to_protected_folders();
		$this->save_option( 'folder_protection', 1 );

		$this->msg = sprintf( 'Folder protection successfully auto-configured at <b>%s</b>', $root_of_folders );
		return true;
	}

	/**
	 * Setup folder protection parent folder.
	 */
	public function folder_protection_parent_folder() {

		$parent_folder = wlm_post_data()['parentFolder'];

		if ( in_array( $parent_folder, array( '', 'wp-content', 'wp-includes', 'wp-admin', 'uploads', 'themes', 'plugins' ), true ) ) {
			$this->err = __( 'Parent Folder can not be one of WordPress default folders such as wp-content, wp-includes, wp-admin, uploads, themes or plugins folder.<br /><br />Try to create a folder inside your WordPress instalation path and set it as Parent Folder.', 'wishlist-member' );
			return false;
		}

		$root_of_folders = addslashes( ABSPATH . $parent_folder );

		if ( ! is_dir( $root_of_folders ) ) {
			$this->err = __( 'Folder not found. Please create it first.', 'wishlist-member' );
			return false;
		}

		$this->remove_all_htaccess_from_protected_folders();

		$this->save_option( 'parentFolder', $parent_folder );
		$this->save_option( 'rootOfFolders', $root_of_folders );

		$this->add_htaccess_to_protected_folders();

		$this->msg = __( '<b>Parent Folder Updated.</b><br>', 'wishlist-member' );
		return true;
	}

	/**
	 * Migrate folder protection data.
	 */
	public function folder_protection_migrate() {
		$need_migrate = $this->get_option( $this->PluginOptionName . '_MigrateFolderProtectionData' );

		if ( 1 !== (int) $need_migrate ) {
			$parent_folder = $this->folder_protection_relative_path( $this->get_option( 'rootOfFolders' ) );
			$this->save_option( 'parentFolder', $parent_folder );
			$this->save_option( $this->PluginOptionName . '_MigrateFolderProtectionData', '1' );
		}
	}

	/**
	 * Migrate folder protection settings to wlm_contentlevels
	 */
	public function migrate_folder_protection() {
		if ( 1 != $this->get_option( 'folder_protection_migrated' ) ) {
			$folder_levels = (array) $this->get_option( 'FolderProtect' );

			$x = array();
			foreach ( $folder_levels as $level => $folders ) {
				if ( is_array( $folders ) ) {
					foreach ( $folders as $folder ) {
						if ( 'Protection' === $level ) {
							$this->folder_protected( $this->folder_id( $folder ), 'Y' );
						} else {
							$x[ $folder ][] = $level;
						}
					}
				}
			}

			foreach ( $x as $folder => $levels ) {
				$this->set_content_levels( '~FOLDER', $this->folder_id( $folder ), $levels );
			}

			$force_download = (array) $this->get_option( 'FolderForceDownload' );
			foreach ( $force_download as $level => $folders ) {
				if ( is_array( $folders ) ) {
					foreach ( array_keys( $folders ) as $folder ) {
						$this->folder_force_download( $this->folder_id( $folder ), 'Y' );
					}
				}
			}
			$this->save_option( 'folder_protection_migrated', 1 );
		}
	}

	/**
	 * Set Folder Protection
	 *
	 * @param integer        $folder_id Folder ID.
	 * @param boolean|string $status    Boolean value or Y/N.
	 * @return boolean
	 */
	public function folder_protected( $folder_id, $status = null ) {
		if ( ! is_null( $status ) ) {
			$this->folder_protection_htaccess( true );
			$this->special_content_level( $folder_id, 'Protection', $status, '~FOLDER' );
		}
		return $this->special_content_level( $folder_id, 'Protection', null, '~FOLDER' );
	}

	/**
	 * Set "force download" status for folders
	 *
	 * @param  integer        $folder_id Folder ID.
	 * @param  boolean|string $status    Boolean value or Y/N.
	 * @return boolean
	 */
	public function folder_force_download( $folder_id, $status = null ) {
		if ( ! is_null( $status ) ) {
			$this->special_content_level( $folder_id, 'ForceDownload', $status, '~FOLDER' );
		}
		return $this->special_content_level( $folder_id, 'ForceDownload', null, '~FOLDER' );
	}

	/**
	 * Returns relative path of folder protection parent folder
	 *
	 * @param string $folder_name Folder name.
	 * @return string
	 */
	public function folder_protection_relative_path( $folder_name ) {
		$folder_name = explode( ABSPATH, $folder_name ); // to fix the Strict Standards: Only variables should be passed by reference
		return preg_replace( array( '/^\/*/', '/\/*$/' ), '', array_pop( $folder_name ) );
	}

	/**
	 * Returns full path of folder protection parent folder
	 *
	 * @param string $folder_name Folder name.
	 * @return string
	 */
	public function folder_protection_full_path( $folder_name ) {
		return ABSPATH . $this->folder_protection_relative_path( $folder_name );
	}

	/**
	 * Computes unsigned crc32 of base folder name and returns it as ID
	 *
	 * @param string $folder_name Folder name.
	 * @return integer
	 */
	public function folder_id( $folder_name ) {
		return crc32( basename( $folder_name ) );
	}
}
