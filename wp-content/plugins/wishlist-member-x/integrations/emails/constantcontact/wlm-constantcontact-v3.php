<?php
/**
 * Constant Contact API v3 interface.
 *
 * @package WishListMember/Autoresponders
 */

namespace WishListMember\Autoresponders\ConstantContact;

/**
 * API v3 class interface.
 */
class API_V3 {

	/**
	 * Client ID
	 *
	 * @var string
	 */
	private $client_id = '936a2fc1-2062-49be-8abc-d3f45499123d';
	/**
	 * Client secret
	 *
	 * @var string
	 */
	private $client_secret = 'PsOsTBIERPGdqjEwbvk-UA';
	/**
	 * Base redirect URI
	 *
	 * @var string
	 */
	private $redirect_uri_base = 'https://ccwlm.apps.wishlistproducts.com?redirect=';
	/**
	 * Redirect URI
	 *
	 * @var string
	 */
	private $redirect_uri;
	/**
	 * Auth URI
	 *
	 * @var string
	 */
	public $auth_url;
	/**
	 * Base API URI
	 *
	 * @var string
	 */
	private $api_base_url = 'https://api.cc.email/v3';
	/**
	 * Token Base URI
	 *
	 * @var string
	 */
	private $token_base_url = 'https://idfed.constantcontact.com/as/token.oauth2';
	/**
	 * Request success state
	 *
	 * @var string
	 */
	private $request_successful = false;
	/**
	 * Last error
	 *
	 * @var string
	 */
	private $last_error = '';
	/**
	 * Last response
	 *
	 * @var string
	 */
	private $last_response = array();
	/**
	 * Last response headers
	 *
	 * @var array
	 */
	private $last_response_header = array();

	/**
	 * Constructor
	 *
	 * @param string $admin_url  Admin URL.
	 */
	public function __construct( $admin_url ) {

		$this->last_response        = null;
		$this->last_response_header = null;

		if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_setopt' ) ) {
			trigger_error( 'cURL not supported.' );
			$this->last_error = 'cURL not supported.';
		} else {
			$this->redirect_uri = $this->redirect_uri_base . $admin_url;
			$this->auth_url     = $this->api_base_url . '/idfed?client_id=' . $this->client_id . '&scope=contact_data&response_type=code&redirect_uri=' . $this->redirect_uri;
			$this->last_error   = '';
		}
	}

	/**
	 * Get access token
	 *
	 * @param  string $code Token request code.
	 * @return string
	 */
	public function get_access_token( $code ) {
		// Use cURL to get access token and refresh token.
		$ch = curl_init();

		// Create full request URL.
		$url = $this->token_base_url . '?code=' . $code . '&redirect_uri=' . $this->redirect_uri . '&grant_type=authorization_code&scope=contact_data';
		curl_setopt( $ch, CURLOPT_URL, $url );

		// Set authorization header.
		// Make string of "API_KEY:SECRET".
		$auth = $this->client_id . ':' . $this->client_secret;
		// Base64 encode it.
		$credentials = base64_encode( $auth );
		// Create and set the Authorization header to use the encoded credentials.
		$authorization = 'Authorization: Basic ' . $credentials;
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $authorization ) );

		// Set method and to expect response.
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		// Make the call.
		$result = curl_exec( $ch );
		curl_close( $ch );
		return $result;
	}

	/**
	 * Refresh the token.
	 *
	 * @return boolean
	 */
	public function refresh_token() {
		// Use cURL to get a new access token and refresh token.
		$ch = curl_init();

		delete_transient( 'wlm_constantcontact_token' );

		$ar = ( new \WishListMember\Autoresponder( 'constantcontact' ) );

		if ( ! isset( $ar->settings['refresh_token'] ) || empty( $ar->settings['refresh_token'] ) ) {
			return false;
		}

		// Create full request URL.
		$url = $this->token_base_url . '?refresh_token=' . $ar->settings['refresh_token'] . '&grant_type=refresh_token';
		curl_setopt( $ch, CURLOPT_URL, $url );

		// Set authorization header.
		// Make string of "API_KEY:SECRET".
		$auth = $this->client_id . ':' . $this->client_secret;
		// Base64 encode it.
		$credentials = base64_encode( $auth );
		// Create and set the Authorization header to use the encoded credentials.
		$authorization = 'Authorization: Basic ' . $credentials;
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $authorization ) );

		// Set method and to expect response.
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		// Make the call.
		$result = curl_exec( $ch );

		curl_close( $ch );
		$acces_token = json_decode( $result, true );
		if ( isset( $acces_token['access_token'] ) ) {
			unset( $ar->settings['refresh_token'] );
			$ar->settings = array_merge( $ar->settings, $acces_token );
			$ar->save_settings();
			set_transient( 'wlm_constantcontact_token', $acces_token['access_token'], 2 * HOUR_IN_SECONDS );
		} else {
			unset( $ar->settings['refresh_token'] );
			return false;
		}
		return true;
	}

	/**
	 * Make DELETE request
	 *
	 * @param  string  $access_token Access token.
	 * @param  string  $action       HTTP request method.
	 * @param  string  $method       API Method.
	 * @param  array   $args         Arguments.
	 * @param  integer $timeout      Request timeout. Default 10.
	 * @return array
	 */
	private function make_request( $access_token, $action, $method, $args = array(), $timeout = 10 ) {

		$this->last_error           = '';
		$this->request_successful   = false;
		$this->last_response        = null;
		$this->last_response_header = null;

		$request_url = $this->api_base_url . '/' . $method;
		$status_code = 418;

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $request_url );
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			array(
				'Accept: application/json',
				'Authorization: Bearer ' . $access_token,
				'Content-Type: application/json',
				'Cache-Control: no-cache',
				'Postman-Token: ' . $this->client_id,
			)
		);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		switch ( $action ) {
			case 'post':
				curl_setopt( $ch, CURLOPT_POST, true );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, wp_json_encode( $args ) );
				break;

			case 'get':
				$query = http_build_query( $args, '', '&' );
				curl_setopt( $ch, CURLOPT_URL, $request_url . '?' . $query );
				break;

			case 'delete':
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'DELETE' );
				break;

			case 'patch':
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PATCH' );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, wp_json_encode( $args, JSON_FORCE_OBJECT ) );
				break;

			case 'put':
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PUT' );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, wp_json_encode( $args, JSON_FORCE_OBJECT ) );
				break;
		}

		$this->last_response        = curl_exec( $ch );
		$this->last_response_header = curl_getinfo( $ch );

		if ( $this->last_response ) {
			$this->last_response = json_decode( $this->last_response, true );
		} else {
			$this->last_response = array( 'error' => 'An error occured while doing the request' );
		}

		if ( isset( $this->last_response_header['http_code'] ) ) {
			$status_code = (int) $this->last_response_header['http_code'];
		}

		if ( $status_code >= 200 && $status_code <= 299 ) {
			$this->request_successful = true;
		} else {
			if ( isset( $this->last_response['error_message'] ) ) {
				$this->last_error = sprintf( '[%s] %s', $this->last_response['error_key'], $this->last_response['error_message'] );
			} else {
				$this->last_error = curl_error( $ch );
			}
			$this->last_error                     = empty( $this->last_error ) ? $status_code . ': An error occured while doing the request.' : $status_code . $this->last_error;
			$this->last_response['error_message'] = $this->last_error;
		}

		curl_close( $ch );

		return $this->last_response;
	}

	/**
	 * Get request success state
	 *
	 * @return boolean
	 */
	public function is_success() {
		return $this->request_successful;
	}

	/**
	 * Get last error.
	 *
	 * @return string|false
	 */
	public function get_last_error() {
		return $this->last_error ? $this->last_error : false;
	}

	/**
	 * Get last response.
	 *
	 * @return array
	 */
	public function get_last_response() {
		return $this->last_response;
	}

	/**
	 * Get last response header
	 *
	 * @return array
	 */
	public function get_last_response_header() {
		return $this->last_response_header;
	}

	/**
	 * Make DELETE request
	 *
	 * @param  string  $access_token Access token.
	 * @param  string  $method       API Method.
	 * @param  array   $args         Arguments.
	 * @param  integer $timeout      Request timeout. Default 10.
	 * @return array
	 */
	public function delete( $access_token, $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( $access_token, 'delete', $method, $args, $timeout );
	}

	/**
	 * Make GET request
	 *
	 * @param  string  $access_token Access token.
	 * @param  string  $method       API Method.
	 * @param  array   $args         Arguments.
	 * @param  integer $timeout      Request timeout. Default 10.
	 * @return array
	 */
	public function get( $access_token, $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( $access_token, 'get', $method, $args, $timeout );
	}

	/**
	 * Make PATCH request
	 *
	 * @param  string  $access_token Access token.
	 * @param  string  $method       API Method.
	 * @param  array   $args         Arguments.
	 * @param  integer $timeout      Request timeout. Default 10.
	 * @return array
	 */
	public function patch( $access_token, $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( $access_token, 'patch', $method, $args, $timeout );
	}

	/**
	 * Make POST request
	 *
	 * @param  string  $access_token Access token.
	 * @param  string  $method       API Method.
	 * @param  array   $args         Arguments.
	 * @param  integer $timeout      Request timeout. Default 10.
	 * @return array
	 */
	public function post( $access_token, $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( $access_token, 'post', $method, $args, $timeout );
	}

	/**
	 * Make PUT request
	 *
	 * @param  string  $access_token Access token.
	 * @param  string  $method       API Method.
	 * @param  array   $args         Arguments.
	 * @param  integer $timeout      Request timeout. Default 10.
	 * @return array
	 */
	public function put( $access_token, $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( $access_token, 'put', $method, $args, $timeout );
	}
}
