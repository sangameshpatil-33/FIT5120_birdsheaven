<?php
/**
 * Collects leads and subscribe to SendGrid
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the SendGrid API.
 *
 * @since 1.2.1
 */
final class CPRO_Service_SendGrid extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.2.1
	 * @var string $id
	 */
	public $id = 'SendGrid';

	/**
	 * Default Custom field array.
	 * This is predefined custom fields array that SendGrid
	 * has already defined. When SendGrid releases the new
	 * set of fields, we need to update this array.
	 *
	 * @since 1.2.1
	 * @var string $id
	 */
	public static $mapping_fields = array( 'first_name', 'last_name', 'phone_number', 'address_line_1', 'address_line_2', 'city', 'state_province_region', 'postal_code', 'country' );

	/**
	 * SendGrid Custom field array.
	 * This is custom fields array that user will create in SendGrid
	 *
	 * @since 1.2.4
	 * @var string $map_custom_fields
	 */
	public static $map_custom_fields = array();

	/**
	 * Store API instance
	 *
	 * @since 1.2.1
	 * @var object $api_instance
	 * @access private
	 */
	private $api_instance = null;

	/**
	 * Get an instance of the API.
	 *
	 * @since 1.2.1
	 * @param string $api_key A valid API key.
	 * @return object The API instance.
	 */
	public function get_api( $api_key ) {

		if ( $this->api_instance ) {
			return $this->api_instance;
		}

		if ( file_exists( CP_SERVICES_BASE_DIR . 'includes/vendor/sendgrid/sendgrid.php' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/sendgrid/sendgrid.php';
		}

		if ( class_exists( 'CPRO_SendGrid' ) ) {
			$this->api_instance = new CPRO_SendGrid( $api_key );
		}

		return $this->api_instance;
	}
	/**
	 * Test the API connection.
	 *
	 * @since 1.2.1
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
		} else {
			// Try to connect and store the connection data.
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
	 * @since 1.2.1
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
				'help'  => __( 'Your API Key can be found in your SendGrid account under Account > Settings > API Keys.', 'convertpro-addon' ),
			)
		);
		return ob_get_clean();
	}

	/**
	 * Returns the api_key in array format
	 *
	 * @since 1.2.1
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
	 * @since 1.2.1
	 * @param string $account The name of the saved account.
	 * @param object $post_data Posted data.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 *      @type string $html The field markup.
	 *      @type array $mapping_fields The field mapping array for SendGrid.
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

			$resp = $api->getList( $account_data['api_key'] );

			if ( ! empty( $resp['lists'] ) ) {

				$lists             = $resp['lists'];
				$response['html'] .= $this->render_list_field( $lists, $post_data );
				// Get and Update SendGrid Custom fields.
				$this->get_and_update_custom_fields( $account_data['api_key'], $post_data['account'] );
			} else {
				$response['error'] = __( 'No list added yet.', 'convertpro-addon' );
			}
		} catch ( Exception $e ) {
			$response['error'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.2.1
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
			$list_options[ $list->id ] = $list->name;
		}

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) ) ? ( ( isset( $settings['default']['SendGrid_list'] ) ) ? $settings['default']['SendGrid_list'] : '' ) : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'SendGrid_list',
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
	 * @since 1.2.1
	 */
	public function render_mapping() {
		return self::$mapping_fields;
	}

	/**
	 * Get the custom fields of SendGrid and save it.
	 *
	 * @param string $api_key of the SendGrid.
	 * @param string $account The name of the saved account.
	 * @since 1.2.4
	 */
	public function get_and_update_custom_fields( $api_key, $account ) {

		$term = get_term_by( 'slug', $account, CP_CONNECTION_TAXONOMY );

		$opts = array(
			'headers' => array(
				'content-Type'  => 'application/json',
				'authorization' => 'Bearer ' . $api_key,
			),
			'body'    => '',
		);

		$request = 'https://api.sendgrid.com/v3/marketing/field_definitions';
		$result  = wp_remote_get( $request, $opts );

		$response_arr = json_decode( $result['body'] );

		if ( array_key_exists( 'custom_fields', $response_arr ) ) {

			foreach ( $response_arr->custom_fields as $key => $sg_custom_fields ) {

				self::$map_custom_fields[] = array(
					'id'   => $sg_custom_fields->id,
					'name' => $sg_custom_fields->name,
				);
			}
		}

		$save_meta = array(
			'api_key'          => $api_key,
			'sg_custom_fields' => self::$map_custom_fields,
		);

		update_term_meta( $term->term_id, CP_API_CONNECTION_SERVICE_AUTH, $save_meta );
	}

	/**
	 * Subscribe an email address to SendGrid.
	 *
	 * @since 1.2.1
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
			$response['error'] = __( 'There was an error subscribing to SendGrid! The account is no longer connected.', 'convertpro-addon' );
		} else {

			$api              = $this->get_api( $account_data['api_key'] );
			$sg_custom_fields = $account_data['sg_custom_fields'];
			$data             = array();
			$fields           = array();
			$custom_fields    = array();
			$cust_fields      = array();

			foreach ( $settings['param'] as $key => $p ) {

				if ( 'email' !== $key && 'date' !== $key ) {
					if ( isset( $settings['meta'][ $key ] ) ) {
						if ( 'custom_field' !== $settings['meta'][ $key ] ) {

							$fields[ $settings['meta'][ $key ] ] = $p;

						} else {

							$key = array_search(
								$settings['meta'][ $key . '-input' ],
								array_map(
									function( $element ) {
											return $element['name'];},
									$sg_custom_fields
								),
								true
							);
							if ( '' !== $key || false !== $key || null !== $key ) {

								$custom_field_id = $sg_custom_fields[ $key ]['id'];

								$custom_fields = array(
									$custom_field_id => $p,
								);

								$cust_fields = array_merge( $cust_fields, $custom_fields );
							}
						}
					}
				}
			}

			// Map fields and custom fields.
			$default_fields = self::$mapping_fields;
			foreach ( $default_fields as $val ) {

				if ( isset( $fields[ $val ] ) ) {

					$data[ $val ] = $fields[ $val ];
				}
			}

			if ( ! empty( $cust_fields ) ) {
				$data['custom_fields'] = $cust_fields;
			}

			$data['email'] = $email;

			// Subscribe.
			try {
				$response = $api->subscribe( $settings['SendGrid_list'], $data, $account_data['api_key'] );

			} catch ( Exception $e ) {
				$response['error'] = sprintf(
					/* translators: %s Error Message */
					__( 'There was an error subscribing to SendGrid! %s', 'convertpro-addon' ),
					$e->getMessage()
				);

			}
		}

		return $response;
	}
}
