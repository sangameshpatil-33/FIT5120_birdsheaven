<?php

/**
 * Sendfox API Class.
 *
 * @since 1.4.0
 */
class CPRO_Sendfox {

	/**
	 * The SendFox API Key.
	 *
	 * @since 1.4.0
	 * @var string $apikey
	 */
	public $apikey;

	/**
	 * The Base URL for the API.
	 *
	 * @since 1.4.0
	 * @var string $root
	 */
	public $root = 'https://api.sendfox.com/';

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 * @param string $apikey API Key.
	 */
	public function __construct( $apikey ) {
		$this->apikey = $apikey;
	}

	/**
	 * Make a connection call to SendFox.
	 *
	 * @since 1.4.0
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function connect() {

		$response = array( 'error' => false );

		$opts = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->apikey,
			),
			'body'    => array(),
			'method'  => 'GET',
			'timeout' => 30,
		);

		$resp = wp_remote_get( $this->root . 'me/', $opts );

		if ( ! is_wp_error( $resp ) && ( 200 === $resp['response']['code'] || 201 === $resp['response']['code'] ) ) {
				$body = wp_remote_retrieve_body( $resp );

				$request = json_decode( $body, true );

			if ( empty( $request ) ) {
				$response['error'] = __( 'Error: API Access token not valid!', 'convertpro-addon' );
			}
		} elseif ( is_object( $resp ) ) {
			$response['error'] = $resp->get_error_message();
		}

		return $response;
	}

	/**
	 * Get lists.
	 *
	 * @since 1.4.0
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function get_list() {
		$response = array( 'error' => false );

		$opts = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->apikey,
			),
			'body'    => array(),
			'method'  => 'GET',
			'timeout' => 30,
		);

		$resp = wp_remote_get( $this->root . 'lists/', $opts );

		if ( ! is_wp_error( $resp ) && ( 200 === $resp['response']['code'] || 201 === $resp['response']['code'] ) ) {
				$body = wp_remote_retrieve_body( $resp );

				$request = json_decode( $body, true );

				if ( empty( $request ) ) {
					$response['error'] = __( 'Error: API Access token not valid!', 'convertpro-addon' );
				} else {
					$response['lists'] = $request['data'];
				}
			} elseif ( is_object( $resp ) ) {
				$response['error'] = $resp->get_error_message();
			}
		return $response;
	}

	/**
	 * Subscribe an email address to Moosend.
	 *
	 * @since 1.4.0
	 * @param string $data The other data.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $data ) {
		$response = array( 'error' => false );
		$opts     = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->apikey,
			),
			'body'    => $data,
			'method'  => 'POST',
			'timeout' => 30,
		);

		$resp = wp_remote_post( $this->root . 'contacts/', $opts );

		if ( ! is_wp_error( $resp ) && ( 200 === $resp['response']['code'] || 201 === $resp['response']['code'] ) ) {
			$body = wp_remote_retrieve_body( $resp );

			$request = json_decode( $body, true );

			if ( empty( $request ) ) {
				$response['error'] = __( 'Error: There was an error subscribing to SendFox!!!', 'convertpro-addon' );
			}
		} elseif ( is_object( $resp ) ) {
			$response['error'] = $resp->get_error_message();
		}

		return $response;
	}
}
