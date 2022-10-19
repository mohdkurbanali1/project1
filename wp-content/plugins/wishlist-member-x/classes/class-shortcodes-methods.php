<?php
/**
 * Shortcodes Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Shortcodes Methods trait
*/
trait Shortcodes_Methods {
	public function shortcodes_init() {
		// get levels
		$wpm_levels = $this->get_option( 'wpm_levels' );

		// shortcodes array
		$wlm_shortcodes = array(
			'Member'  => array(
				array(
					'title' => 'First Name',
					'value' => '[wlm_firstname]',
				),
				array(
					'title' => 'Last Name',
					'value' => '[wlm_lastname]',
				),
				array(
					'title' => 'Email',
					'value' => '[wlm_email]',
				),
				array(
					'title' => 'Username',
					'value' => '[wlm_username]',
				),
			),
			'Access'  => array(
				array(
					'title' => 'Membership Levels',
					'value' => '[wlm_memberlevel]',
				),
				array(
					'title' => 'Pay Per Posts',
					'value' => '[wlm_userpayperpost sort="ascending"]',
				),
				array(
					'title' => 'RSS Feed',
					'value' => '[wlm_rss]',
				),
				array(
					'title' => 'Content Levels',
					'value' => '[wlm_contentlevels type="comma" link_target="_blank" class="wlm_contentlevels" show_link="1" salespage_only="1"]',
				),
			),
			'Login'   => array(
				array(
					'title' => 'Login Form',
					'value' => '[wlm_loginform]',
				),
				array(
					'title' => 'Login URL',
					'value' => '[wlm_loginurl]',
				),
				array(
					'title' => 'Log out URL',
					'value' => '[wlm_logouturl]',
				),
			),
			'Profile' => array(
				array(
					'title' => 'Profile Form',
					'value' => '[wlm_profileform hide_mailinglist=no]',
				),
				array(
					'title' => 'Profile URL',
					'value' => '[wlm_profileurl]',
				),
			),
		);

		if ( $wpm_levels ) {
			$wlm_shortcodes['Join Date']       = array();
			$wlm_shortcodes['Expiration Date'] = array();
			foreach ( (array) $wpm_levels as $level ) {
				if ( false === strpos( $level['name'], '/' ) ) {
					$wlm_shortcodes['Join Date'][]       = array(
						'title' => "{$level['name']}",
						'value' => "[wlm_joindate {$level['name']}]",
					);
					$wlm_shortcodes['Expiration Date'][] = array(
						'title' => "{$level['name']}",
						'value' => "[wlm_expiration {$level['name']}]",
					);
				}
			}
		}

		$wlm_shortcodes['Address'] = array(
			array(
				'title' => 'Company',
				'value' => '[wlm_company]',
			),
			array(
				'title' => 'Address',
				'value' => '[wlm_address]',
			),
			array(
				'title' => 'Address 1',
				'value' => '[wlm_address1]',
			),
			array(
				'title' => 'Address 2',
				'value' => '[wlm_address2]',
			),
			array(
				'title' => 'City',
				'value' => '[wlm_city]',
			),
			array(
				'title' => 'State',
				'value' => '[wlm_state]',
			),
			array(
				'title' => 'Zip',
				'value' => '[wlm_zip]',
			),
			array(
				'title' => 'Country',
				'value' => '[wlm_country]',
			),
		);

		// custom fields shortcode
		$custom_fields                   = $this->get_custom_fields_merge_codes();
		$this->custom_fields_merge_codes = $custom_fields ? $custom_fields : array();
		if ( count( $custom_fields ) ) {
			$wlm_shortcodes['Custom Fields'] = array();
			foreach ( $custom_fields as $custom_field ) {
				$wlm_shortcodes['Custom Fields'] = array(
					'title' => $custom_field,
					'value' => $custom_field,
				);
			}
		}

		$wlm_shortcodes['Other'] = array(
			array(
				'title' => 'Website',
				'value' => '[wlm_website]',
			),
			array(
				'title' => 'AOL Instant Messenger',
				'value' => '[wlm_aim]',
			),
			array(
				'title' => 'Yahoo Instant Messenger',
				'value' => '[wlm_yim]',
			),
			array(
				'title' => 'Jabber',
				'value' => '[wlm_jabber]',
			),
			array(
				'title' => 'Biography',
				'value' => '[wlm_biography]',
			),
		);

		if ( \WishListMember\Level::any_can_autocreate_account_for_integration() ) {
			$wlm_shortcodes[] = array(
				'title' => 'Auto-generated Password',
				'value' => '[wlm_autogen_password]',
			);
		}

		// mergecodes array
		$wlm_mergecodes[] = array(
			'title' => 'Is Member',
			'value' => '[wlm_ismember]',
			'type'  => 'merge',
		);
		$wlm_mergecodes[] = array(
			'title' => 'Non-Member',
			'value' => '[wlm_nonmember]',
			'type'  => 'merge',
		);
		$wlm_mergecodes[] = array(
			'title'  => 'Private Tags',
			'value'  => '',
			'jsfunc' => 'wlmtnmcelbox_vars.show_private_tags_lightbox',
		);

		// reg form shortcodes
		$wlm_mergecodes[] = array(
			'title'  => 'Registration Forms',
			'value'  => '',
			'jsfunc' => 'wlmtnmcelbox_vars.show_reg_form_lightbox',
		);

		// $wlm_mergecodes are actually called Shortcodes
		$wlm_mergecodes    = apply_filters( 'wlm_mergecodes', $wlm_mergecodes );
		$this->short_codes = $wlm_mergecodes;
		// $wlm_shortcodes are actually called Mergecodes
		$wlm_shortcodes    = apply_filters( 'wlm_shortcodes', $wlm_shortcodes );
		$this->merge_codes = $wlm_shortcodes; 

		$wlmshortcode_role_access = $this->get_option( 'wlmshortcode_role_access' );
		$wlmshortcode_role_access = false === $wlmshortcode_role_access ? false : $wlmshortcode_role_access;
		$wlmshortcode_role_access = is_string( $wlmshortcode_role_access ) ? array() : $wlmshortcode_role_access;
		if ( is_array( $wlmshortcode_role_access ) ) {
			$wlmshortcode_role_access[] = 'administrator';
			$wlmshortcode_role_access   = array_unique( $wlmshortcode_role_access );
		} else {
			$wlmshortcode_role_access = false;
		}

		if ( ! isset( wlm_get_data()['page'] ) || wlm_get_data()['page'] != $this->MenuID ) {
			// Don't initiate tinymce (shortcode inserter) on admin-ajax.php to avoid conflicts with profile builders
			if ( 'admin-ajax.php' != basename( wlm_server_data()['PHP_SELF'] ) ) {
				global $WLMTinyMCEPluginInstanceOnly;
				if ( ! isset( $WLMTinyMCEPluginInstanceOnly ) ) { // instantiate the class only once
					$WLMTinyMCEPluginInstanceOnly = new \WLMTinyMCEPluginOnly( $wlmshortcode_role_access );
					add_action( 'admin_init', array( &$WLMTinyMCEPluginInstanceOnly, 'TNMCE_PluginJS' ), 1 );
				}
				$WLMTinyMCEPluginInstanceOnly->RegisterShortcodes( 'Mergecodes', array(), array(), 0, null, $wlm_shortcodes );
				$WLMTinyMCEPluginInstanceOnly->RegisterShortcodes( 'Shortcodes', array(), array(), 0, null, $wlm_mergecodes );
				if ( count( $this->IntegrationShortcodes ) > 0 ) {
					$WLMTinyMCEPluginInstanceOnly->RegisterShortcodes( 'Integrations', array(), array(), 0, null, $this->IntegrationShortcodes );
				}
			}
		}

		// $this->integration_shortcodes(); //lets try to load it above
	}
}

// register hooks.
add_action(
	'wishlistmember_register_hooks',
	function( $wlm ) {
		add_action( 'plugins_loaded', array( $wlm, 'shortcodes_init' ) );
	}
);
