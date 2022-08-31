<?php
/**
 * ConverPlug Service SendInBlue API V3.
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the SendInBlue API V3.
 *
 * @since 1.4.3
 */
class CPRO_Sendinblue_Api {

	/**
	 * The API key for this service.
	 *
	 * @since 1.4.3
	 * @var string api_key
	 */
	protected $api_key;

	/**
	 * The API URL for this service.
	 *
	 * @since 1.4.3
	 * @var string api_url
	 */
	protected $api_url;

	/**
	 * Constructor.
	 *
	 * @since 1.4.3
	 * @param string $api_url API URL.
	 * @param string $api_key API Key.
	 */
	public function __construct( $api_url, $api_key ) {
		$this->api_key = $api_key;
		$this->api_url = $api_url;
	}

	/**
	 * Make request function for API results.
	 *
	 * @since 1.4.3
	 * @param string $endpoint API Endpoint.
	 * @param array  $args data arguments.
	 * @param string $method request method.
	 * @return array {
	 *      @type string|string response from the API.
	 * }
	 */
	public function make_request( $endpoint, $args = array(), $method = 'get' ) {
		$url = $this->api_url . $endpoint;

		$wp_args = array(
			'method'  => strtoupper( $method ),
			'timeout' => 30,
		);

		$wp_args['headers'] = array(
			'Content-Type' => 'application/json',
			'api-key'      => $this->api_key,
		);

		switch ( $method ) {
			case 'post':
				$wp_args['body'] = wp_json_encode( $args );
				break;
			case 'get':
				$url = add_query_arg( $args, $url );
				break;
		}

		$response = wp_remote_request( $url, $wp_args );

		if ( ! in_array( $response['response']['code'], array( 200, 201, 204 ), true ) ) {
			return array(
				'code'    => 'failure',
				'message' => $response['response']['message'],
			);
		} else {
			return array(
				'code' => 'success',
				'data' => json_decode( wp_remote_retrieve_body( $response ), true ),
			);
		}
	}
}
