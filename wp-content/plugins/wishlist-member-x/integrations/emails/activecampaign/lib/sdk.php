<?php
namespace WishListMember\Autoresponders\ActiveCampaign;

/**
 * ActiveCampaign SDK
 * API Docs: https://www.activecampaign.com/api/overview.php
 */
class SDK {

	/**
	 * API URL
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * API Key
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor
	 *
	 * @param string $api_url API URL
	 * @param string $api_key API Key
	 */
	public function __construct( $api_url, $api_key ) {
		$this->api_url = $api_url;
		$this->api_key = $api_key;
	}

	/**
	 * Make request to ActiveCampaign API (version 1)
	 * https://www.activecampaign.com/api/overview.php
	 *
	 * @param  string     $action          API Action. Accepted values are:
	 *                                     'list_list'
	 *                                     'contact_view_email'
	 *                                     'tags_list'
	 *                                     'contact_add'
	 *                                     'contact_edit'
	 *                                     'contact_tag_add'
	 *                                     'contact_tag_remove'
	 * @param  array|null $data            Data to pass to API
	 * @param  array      $request_options Request options based on https://developer.wordpress.org/reference/classes/WP_Http/request/
	 * @return object                      API Response
	 */
	public function request( $action, $data = null, $request_options = array() ) {
		// ensure $request_options is an array
		if ( ! is_array( $request_options ) ) {
			$request_options = array();
		}
		// ensure $data is array
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		// accepted API actions and their request methods
		$actions = array(
			'list_list'          => array( 'type' => 'GET' ),
			'contact_view_email' => array( 'type' => 'GET' ),
			'tags_list'          => array( 'type' => 'GET' ),
			'contact_add'        => array( 'type' => 'POST' ),
			'contact_edit'       => array( 'type' => 'POST' ),
			'contact_tag_add'    => array( 'type' => 'POST' ),
			'contact_tag_remove' => array( 'type' => 'POST' ),
			'webhook_list'       => array( 'type' => 'GET' ),
			'webhook_add'        => array( 'type' => 'POST' ),
			'webhook_delete'     => array( 'type' => 'GET' ),
		);

		// Create API URL
		$url = $this->api_url . '/admin/api.php';

		// default data
		$default_data = array(
			'api_key'    => $this->api_key,
			'api_action' => $action,
			'api_output' => 'json',
		);

		switch ( $actions[ $action ]['type'] ) {
			case 'GET':
				// get request
				$data = array_merge( $default_data, $data );
				$url .= '?' . http_build_query( $data );
				$resp = wp_remote_get(
					$url,
					array(
						'timeout' => 5,
					) + $request_options
				);
				if ( is_wp_error( $resp ) ) {
					throw new \Exception( $resp->get_error_message() );
				}
				$resp = json_decode( $resp['body'] );

				if ( isset( $resp->result_code ) && 0 == $resp->result_code ) {
					throw new \Exception( $resp->result_message );
				}
				return $resp;
				break;
			case 'POST':
				// post request
				$url .= '?' . http_build_query( $default_data );
				$resp = wp_remote_post(
					$url,
					array(
						'timeout' => 5,
						'body'    => http_build_query( $data ),
					) + $request_options
				);
				if ( is_wp_error( $resp ) ) {
					throw new \Exception( $resp->get_error_message() );
				}
				$resp = json_decode( $resp['body'] );

				if ( 0 == $resp->result_code ) {
					throw new \Exception( $resp->result_message );
				}
				return $resp;
				break;
			default:
				// invalid api action
				// translators: 1: API Action
				throw new \Exception( sprintf( __( 'SDK does not support the API action: %s', 'wishlist-member' ), $action ) );
		}
	}

	/**
	 * Get a list of tags from ActiveCampaign
	 *
	 * @return object|false API result or false on error
	 */
	public function get_tags() {
		try {
			return $this->request( 'tags_list' );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Add tags to a contact on ActioveCampaign
	 *
	 * @param string $email Email address
	 * @param array  $tags  Array of tags to add
	 *
	 * @return object|false API result or false on error
	 */
	public function add_tags( $email, $tags = array() ) {
		try {
			return $this->request( 'contact_tag_add', compact( 'email', 'tags' ), array( 'blocking' => false ) );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Remove tags from a contact on ActioveCampaign
	 *
	 * @param string $email Email address
	 * @param array  $tags  Array of tags to remove
	 */
	public function remove_tags( $email, $tags = array() ) {
		try {
			foreach ( $tags as $tag ) {
				$this->request( 'contact_tag_remove', compact( 'email', 'tags' ), array( 'blocking' => false ) );
			}
		} catch ( \Exception $e ) {
			null;
		}
	}

	/**
	 * Get a list of webhooks from ActiveCampaign
	 *
	 * @return object|false   API result or false on error
	 */
	public function get_webhooks() {
		try {
			return $this->request( 'webhook_list' );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Add webhook to ActiveCampaign
	 *
	 * @param string $url  Webhook URL
	 * @param string $name Webhook Name
	 *
	 * @return object|false API result or false on error
	 */
	public function add_webhook( $url, $name, $actions ) {
		try {
			return $this->request(
				'webhook_add',
				array(
					'url'    => $url,
					'name'   => $name,
					'action' => $actions,
					'init'   => array(
						'public',
						'admin',
						'api',
						'system',
					),
				)
			);
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Remove webhook from ActiveCampaign
	 *
	 * @param string $id Webhook ID
	 *
	 * @return object|false API result or false on error
	 */
	public function remove_webhook( $id ) {
		try {
			return $this->request( 'webhook_delete', array( 'id' => $id ) );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Get ActiveCampaign contact ID for an email address
	 *
	 * @param  string $email Email Address
	 * @return object|false  API result or false on error
	 */
	public function get_user_by_email( $email ) {
		try {
			$resp = $this->request( 'contact_view_email', array( 'email' => $email ) );
			return $resp;
		} catch ( \Exception $e ) {
			return false;
		}

	}

	/**
	 * Get ActiveCampaign lists
	 *
	 * @return object|false API result or false on error
	 */
	public function get_lists() {
		$resp  = $this->request(
			'list_list',
			array(
				'ids'  => 'all',
				'full' => 0,
			)
		);
		$lists = array();
		foreach ( $resp as $l ) {
			if ( is_object( $l ) ) {
				$lists[] = $l;
			}
		}
		return $lists;
	}

	/**
	 * Add user to lists
	 *
	 * @param array $lists     Array of List IDs
	 * @param array $user_data User data
	 * @return object|false API result or false on error
	 */
	public function add_to_lists( $lists, $user_data ) {
		$user = $this->get_user_by_email( $user_data['email'] );
		if ( $user ) {
			// user already exists in ActiveCampaign so we just update that
			$data = array(
				'id' => $user->id,
			);

			// build the previous list items
			foreach ( $user->lists as $list ) {
				$data[ "p[{$list->listid}]" ]      = $list->listid;
				$data[ "status[{$list->listid}]" ] = $list->status;
			}

			// override with our new ones
			foreach ( $lists as $lid ) {
				$data[ "p[$lid]" ]      = $lid;
				$data[ "status[$lid]" ] = 1;
			}

			try {
				return $this->request( 'contact_edit', $data );
			} catch ( \Exception $e ) {
				return false;
			}
		} else {
			// new user so we create it
			$data = array(
				'first_name' => $user_data['first_name'],
				'last_name'  => $user_data['last_name'],
				'email'      => $user_data['email'],
				// misc
				'ip'         => wlm_server_data()['REMOTE_ADDR'],
			);

			// now add this to our lists
			foreach ( $lists as $lid ) {
				$data[ "p[$lid]" ]      = $lid;
				$data[ "status[$lid]" ] = 1;
			}

			try {
				return $this->request( 'contact_add', $data, array( 'blocking' => false ) );
			} catch ( \Exception $e ) {
				return false;
			}
		}
	}
	/**
	 * Remove user from lists
	 *
	 * @param array  $lists     Array of List IDs
	 * @param string $user_data Email address
	 * @return object|false API result or false on error
	 */
	public function remove_from_lists( $lists, $email ) {
		$user = $this->get_user_by_email( $email );
		if ( empty( $user ) ) {
			return;
		}
		$data = array(
			'id' => $user->id,
		);
		// build the previous list items
		foreach ( $user->lists as $list ) {
			$data[ "p[{$list->listid}]" ]      = $list->listid;
			$data[ "status[{$list->listid}]" ] = $list->status;
		}
		// override with our new ones
		foreach ( $lists as $lid ) {
			$data[ "p[$lid]" ]      = $lid;
			$data[ "status[$lid]" ] = 2;
		}
		try {
			$this->request( 'contact_edit', $data, array( 'blocking' => false ) );
		} catch ( \Exception $e ) {
			return false;
		}
	}

}
