<?php
/**
 * Collects leads and subscribe to Moosend
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the Moosend API.
 *
 * @since 1.3.0
 */
final class CPRO_Service_Moosend extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.3.0
	 * @var string $id
	 */
	public $id = 'Moosend';

	/**
	 * Default Custom field array.
	 * This is predefined custom fields array that Moosend
	 * has already defined. When Moosend releases the new
	 * set of fields, we need to update this array.
	 *
	 * @since 1.3.0
	 * @var string $id
	 */
	public static $mapping_fields = array( 'Name' );

	/**
	 * Store API instance
	 *
	 * @since 1.3.0
	 * @var object $api_instance
	 * @access private
	 */
	private $api_instance = null;

	/**
	 * Get an instance of the API.
	 *
	 * @since 1.3.0
	 * @param string $api_key A valid API key.
	 * @return object The API instance.
	 */
	public function get_api( $api_key ) {

		if ( $this->api_instance ) {
			return $this->api_instance;
		}

		if ( file_exists( CP_SERVICES_BASE_DIR . 'includes/vendor/moosend/moosend.php' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/moosend/moosend.php';
		}

		if ( class_exists( 'CPRO_Moosend' ) ) {
			$this->api_instance = new CPRO_Moosend( $api_key );
		}

		return $this->api_instance;
	}
	/**
	 * Test the API connection.
	 *
	 * @since 1.3.0
	 * @param array $fields A valid API key.
	 * @return array{
	 *      @type bool|string $error The error message or false if no error.
	 *      @type array $data An array of data used to make the connection.
	 * }
	 */
	public function connect( $fields = array() ) {
		$response = array(
			'error' => false,
			'data'  => array(),
		);

		// Make sure we have an API key.
		if ( ! isset( $fields['api_key'] ) || empty( $fields['api_key'] ) ) {
			$response['error'] = __( 'Error: You must provide an API key.', 'convertpro-addon' );
		} else { // Try to connect and store the connection data.

			$api = $this->get_api( $fields['api_key'] );

			try {
				$connected = $api->connect();

				$response['data'] = array(
					'api_key' => $fields['api_key'],
				);
			} catch ( Exception $e ) {
				$response['error'] = $e->getMessage();
			}
		}
		return $response;
	}

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @since 1.3.0
	 * @return string The connection settings markup.
	 */
	public function render_connect_settings() {
		ob_start();

		ConvertPlugHelper::render_input_html(
			'api_key',
			array(
				'class' => '',
				'type'  => 'text',
				'label' => __( 'API Key', 'convertpro-addon' ),
				'help'  => __( 'Your API Key can be found in your Moosend account under Account > Settings > API Keys.', 'convertpro-addon' ),
			)
		);
		return ob_get_clean();
	}

	/**
	 * Returns the api_key in array format
	 *
	 * @since 1.3.0
	 * @param string $auth_meta $api_key A valid API key.
	 * @return array Array of api_key
	 */
	public function render_auth_meta( $auth_meta ) {
		return array(
			'api_key' => $auth_meta['api_key'],
		);
	}

	/**
	 * Render the markup for service specific fields.
	 *
	 * @since 1.3.0
	 * @param string $account The name of the saved account.
	 * @param object $post_data Posted data.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 *      @type string $html The field markup.
	 *      @type array $mapping_fields The field mapping array for Moosend.
	 * }
	 */
	public function render_fields( $account, $post_data ) {
		$account_data = ConvertPlugServices::get_account_data( $account );

		$api                 = $this->get_api( $account_data['api_key'] );
		$response            = array(
			'error'          => false,
			'html'           => '',
			'mapping_fields' => self::$mapping_fields,
		);
		$post_data['isEdit'] = ( isset( $post_data['isEdit'] ) ) ? $post_data['isEdit'] : null;
		// Lists field.
		try {

			$resp              = $api->getList();
			$lists             = $resp['Items'];
			$response['html'] .= $this->render_list_field( $lists, $post_data );

		} catch ( Exception $e ) {
			$response['error'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.3.0
	 * @param array $lists List data from the API.
	 * @param array $settings Posted data.
	 * @return string The markup for the list field.
	 * @access private
	 */
	private function render_list_field( $lists, $settings ) {

		$list_options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);
		$default      = '';

		foreach ( $lists as $list ) {
			$list_options[ $list->ID ] = $list->Name; // PHPCS:ignore:WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) ) ? ( ( isset( $settings['default']['moosend_list'] ) ) ? $settings['default']['moosend_list'] : '' ) : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'moosend_list',
			array(
				'class'   => '',
				'type'    => 'select',
				'label'   => __( 'Select a List', 'convertpro-addon' ),
				'help'    => '',
				'default' => $default,
				'options' => $list_options,
			)
		);

		return ob_get_clean();

	}

	/**
	 * Mapping fields.
	 *
	 * @since 1.3.0
	 */
	public function render_mapping() {
		return self::$mapping_fields;
	}

	/**
	 * Subscribe an email address to Moosend.
	 *
	 * @since 1.3.0
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $settings, $email ) {

		$account_data = ConvertPlugServices::get_account_data( $settings['api_connection'] );
		$response     = array(
			'error' => false,
		);

		if ( ! $account_data ) {
			$response['error'] = __( 'There was an error subscribing to Moosend! The account is no longer connected.', 'convertpro-addon' );
		} else {

			$api = $this->get_api( $account_data['api_key'] );

			$data          = array();
			$fields        = array();
			$fixed_fields  = array();
			$custom_data   = array();
			$custom_fields = array();
			$cust_fields   = array();

			foreach ( $settings['param'] as $key => $p ) {

				if ( 'email' !== $key && 'date' !== $key ) {
					if ( isset( $settings['meta'][ $key ] ) ) {
						if ( 'custom_field' === $settings['meta'][ $key ] ) {

							$fields[ $settings['meta'][ $key . '-input' ] ] = $p;
							$custom_fields                                  = array(
								'name'  => $settings['meta'][ $key . '-input' ],
								'value' => $p,
							);
							array_push( $cust_fields, $custom_fields );
						} else {
							$fixed_fields[ $settings['meta'][ $key ] ] = $p;
						}
					}
				}
			}

			// Map fields and custom fields.
			$default_fields = self::$mapping_fields;
			foreach ( $default_fields as $val ) {

				if ( isset( $fixed_fields[ $val ] ) ) {

					$data[ $val ] = array( 'Value' => $fixed_fields[ $val ] );
				}
			}

			if ( ! empty( $cust_fields ) ) {

				foreach ( $cust_fields as $key => $field_val ) {

					$custom_data[] = array(
						'name'  => $field_val['name'],
						'value' => $field_val['value'],
					);
				}
				$data['CustomFields'] = $custom_data;
			}

			$ipaddress = '';
			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
				// HTTP_X_FORWARDED_FOR sometimes returns internal or local IP address, which is not usually useful. Also, it would return a comma separated list if it was forwarded from multiple ipaddresses.
				$addr      = explode( ',', $ipaddress );
				$ipaddress = $addr[0];
			} else {
				$ipaddress = $_SERVER['REMOTE_ADDR'];
			}

			$data['MailingListId']     = $settings['moosend_list'];
			$data['MemberEmail']       = $email;
			$data['OriginalIpAddress'] = $ipaddress;
			$data['SingleOptInStatus'] = array( 'OptedInSource' => 100 );
			$data['SubscribeType']     = 1;
			// Subscribe.
			try {
				$response = $api->subscribe( $data, $account_data['api_key'] );

			} catch ( Exception $e ) {
				$response['error'] = sprintf(
					/* translators: %s Error Message */
					__( 'There was an error subscribing to Moosend! %s', 'convertpro-addon' ),
					$e->getMessage()
				);

			}
		}

		return $response;
	}
}
