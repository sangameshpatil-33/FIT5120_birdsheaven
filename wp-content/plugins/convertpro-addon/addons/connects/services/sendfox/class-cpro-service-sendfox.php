<?php
/**
 * Collects leads and subscribe to SendFox
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the SendFox API.
 *
 * @since 1.4.0
 */
final class CPRO_Service_Sendfox extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.4.0
	 * @var string $id
	 */
	public $id = 'sendfox';

	/**
	 * Default Custom field array.
	 * This is predefined custom fields array that SendFox
	 * has already defined. When SendFox releases the new
	 * set of fields, we need to update this array.
	 *
	 * @since 1.4.0
	 * @var string $id
	 */
	public static $mapping_fields = array( 'first_name', 'last_name' );

	/**
	 * Store API instance
	 *
	 * @since 1.4.0
	 * @var object $api_instance
	 * @access private
	 */
	private $api_instance = null;

	/**
	 * Get an instance of the API.
	 *
	 * @since 1.4.0
	 * @param string $api_key A valid API key.
	 * @return object The API instance.
	 */
	public function get_api( $api_key ) {

		if ( $this->api_instance ) {
			return $this->api_instance;
		}

		if ( file_exists( CP_SERVICES_BASE_DIR . 'includes/vendor/sendfox/cp-v2-sendfox.php' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/sendfox/cp-v2-sendfox.php';
		}

		if ( class_exists( 'CPRO_Sendfox' ) ) {
			$this->api_instance = new CPRO_Sendfox( $api_key );
		}

		return $this->api_instance;
	}
	/**
	 * Test the API connection.
	 *
	 * @since 1.4.0
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
			$response['error'] = __( 'Error: You must provide an API Access Token.', 'convertpro-addon' );
		} else {
			// Try to connect and store the connection data.
			$api = $this->get_api( $fields['api_key'] );

			try {
				$connected = $api->connect();

				if ( false !== $connected['error'] ) {
					$response['error'] = $connected['error'];
				}

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
	 * @since 1.4.0
	 * @return string The connection settings markup.
	 */
	public function render_connect_settings() {
		ob_start();

		ConvertPlugHelper::render_input_html(
			'api_key',
			array(
				'class' => '',
				'type'  => 'text',
				'label' => __( 'API Access Token', 'convertpro-addon' ),
				'help'  => __( 'Your API Access Token can be found in your SendFox account under Settings > API', 'convertpro-addon' ),
			)
		);
		return ob_get_clean();
	}

	/**
	 * Returns the api_key in array format
	 *
	 * @since 1.4.0
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
	 * @since 1.4.0
	 * @param string $account The name of the saved account.
	 * @param object $post_data Posted data.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 *      @type string $html The field markup.
	 *      @type array $mapping_fields The field mapping array for SendFox.
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
		$resp = $api->get_list();
		if ( false !== $resp['error'] ) {
			$response['error'] = $resp['error'];
		} else {
			$lists             = $resp['lists'];
			$response['html'] .= $this->render_list_field( $lists, $post_data );
		}

		return $response;
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.4.0
	 * @param array $lists List data from the API.
	 * @param array $settings Posted data.
	 * @return string The markup for the list field.
	 * @access private
	 */
	private function render_list_field( $lists, $settings ) {
		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);
		$default = '';

		foreach ( $lists as $list ) {
			$options[ $list['id'] ] = $list['name'];
		}

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['sendfox_list'] ) ) ? $settings['default']['sendfox_list'] : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'sendfox_list',
			array(
				'class'   => '',
				'type'    => 'select',
				'label'   => __( 'Select a List', 'convertpro-addon' ),
				'help'    => '',
				'default' => $default,
				'options' => $options,
			)
		);
		return ob_get_clean();

	}

	/**
	 * Mapping fields.
	 *
	 * @since 1.4.0
	 */
	public function render_mapping() {
		return self::$mapping_fields;
	}

	/**
	 * Subscribe an email address to SendFox.
	 *
	 * @since 1.4.0
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $settings, $email ) {

		$account_data = ConvertPlugServices::get_account_data( $settings['api_connection'] );

		$response = array(
			'error' => false,
		);

		if ( ! $account_data ) {
			$response['error'] = __( 'There was an error subscribing to SendFox! The account is no longer connected.', 'convertpro-addon' );
		} else {
			$api = $this->get_api( $account_data['api_key'] );

			$data = array();

			$data['merge_fields'] = array();

			$ip_address = '';
			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ip_address = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
				// HTTP_X_FORWARDED_FOR sometimes returns internal or local IP address, which is not usually useful. Also, it would return a comma separated list if it was forwarded from multiple ipaddresses.
				$addr       = explode( ',', $ip_address );
				$ip_address = $addr[0];
			} else {
				$ip_address = $_SERVER['REMOTE_ADDR'];
			}

			$contact = array(
				'email'      => $email,
				'lists'      => array(
					intval( $settings['sendfox_list'] ),
				),
				'ip_address' => $ip_address,
			);

			foreach ( $settings['param'] as $key => $p ) {

				if ( 'email' !== $key && 'date' !== $key && isset( $settings['meta'][ $key ] ) ) {
					$contact[ $settings['meta'][ $key ] ] = $p;
				}
			}

			// Subscribe.
			$response = $api->subscribe( $contact );

		}

		return $response;
	}
}
