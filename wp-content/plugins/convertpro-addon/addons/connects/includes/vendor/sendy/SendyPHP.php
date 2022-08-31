<?php
/**
 * Sendy Class
 *
 * @package Convert Pro Addon
 */

if ( ! class_exists( 'CPRO_SendyPHP' ) ) {
	/**
	 * Helper class for the Sendy API.
	 *
	 * @since 1.0.0
	 */
	class CPRO_SendyPHP {

		/**
		 * The installation_url for this service.
		 *
		 * @since 1.0.0
		 * @var string $installation_url
		 */
		private $installation_url;

		/**
		 * The api_key for this service.
		 *
		 * @since 1.0.0
		 * @var string $api_key
		 */
		private $api_key;

		/**
		 * The list_id for this service.
		 *
		 * @since 1.0.0
		 * @var string $list_id
		 */
		private $list_id;

		/**
		 * Function Name: __construct
		 * Function Description: Constructor
		 *
		 * @param array $config configurations.
		 * @throws \Exception Error Message.
		 */
		public function __construct( array $config ) {
			// error checking.
			$list_id          = $config['list_id'];
			$installation_url = $config['installation_url'];
			$api_key          = $config['api_key'];

			if ( empty( $installation_url ) ) {
				throw new \Exception( 'Required config parameter [installation_url] is not set or empty', 1 );
			}

			if ( empty( $api_key ) ) {
				throw new \Exception( 'Required config parameter [api_key] is not set or empty', 1 );
			}

			$this->list_id          = $list_id;
			$this->installation_url = $installation_url;
			$this->api_key          = $api_key;
		} // __construct ends

		/**
		 * Function Name: connect_sendy
		 * Function Description: Connect to Sendy Account
		 */
		public function connect_sendy() {

			$url     = $this->installation_url;
			$api_key = $this->api_key;
			$list_id = $this->list_id;

			$values         = array(
				'api_key' => $api_key,
				'list_id' => '',
			);
			$return_options = array(
				'list'    => '',
				'boolean' => 'true',
			);
			// Merge the passed in values with the options for return.
			$content = array_merge( $values, $return_options );

			$result = wp_remote_post(
				$url . '/api/subscribers/active-subscriber-count.php',
				array(
					'timeout' => 15,
					'body'    => $content,
					'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
				)
			);

			// Handle the results.
			if ( strstr( $result['body'], 'File not found.' ) || strstr( $result['body'], 'A valid URL was not provided.' ) || strstr( $result['body'], 'Invalid API key' ) ) {
				return array(
					'status'  => false,
					'message' => $result,
				);
			}

			// Error.
			return array(
				'status'  => true,
				'message' => $result,
			);

		} // connect_sendy ends

		/**
		 * Function Name: subscribe_sendy
		 * Function Description: Subscribe a user
		 *
		 * @param string $name name.
		 * @param array  $customfields custom fields.
		 * @param string $email Email ID.
		 */
		public function subscribe_sendy( $name, $customfields = array(), $email = '' ) {
			if ( ! empty( $email ) ) {
				$postarr = array_merge(
					$customfields,
					array(
						'api_key' => $this->api_key,
						'email'   => $email,
						'name'    => $name,
						'list'    => $this->list_id,
						'boolean' => 'true',
					)
				);

				$result = wp_remote_post(
					$this->installation_url . '/subscribe',
					array(
						'timeout' => 15,
						'body'    => $postarr,
						'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
					)
				);

				$body = wp_remote_retrieve_body( $result );
				$code = (int) wp_remote_retrieve_response_code( $result );
				if ( 200 !== $code ) {
					$status = 0;
				} else {
					$status = 1;
				}
				return array(
					'status'  => $status,
					'message' => $body,
				);

			} else {
				return array(
					'status'  => 0,
					'message' => 'Email field is empty.',
				);
			}
		}//end subscribe_sendy()
	} //class ends
}//if ends
