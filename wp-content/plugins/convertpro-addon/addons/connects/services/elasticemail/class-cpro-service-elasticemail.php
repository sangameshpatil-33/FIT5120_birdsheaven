<?php
/**
 * Collects leads and subscribe to ElasticEmail
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the ElasticEmail API.
 *
 * @since 1.0.0
 */
final class CPRO_Service_ElasticEmail extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public $id = 'elasticEmail';

	/**
	 * Default Custom field array.
	 * This is predefined custom fields array that ElasticEmail
	 * has already defined. When Elastic Email releases the new
	 * set of fields, we need to update this array.
	 *
	 * @since 1.0.0
	 * @var string $mapping_fields
	 */
	public static $mapping_fields = array( 'firstName', 'lastName' );

	/**
	 * Store API instance
	 *
	 * @since 1.0.0
	 * @var object $api_instance
	 * @access private
	 */
	private $api_instance = null;

	/**
	 * Get an instance of the API.
	 *
	 * @since 1.0.0
	 * @param string $api_key A valid API key.
	 * @param string $account_id A valid Account ID.
	 * @return object The API instance.
	 */
	public function get_api( $api_key, $account_id ) {

		if ( $this->api_instance ) {
			return $this->api_instance;
		}

		if ( file_exists( CP_SERVICES_BASE_DIR . 'includes/vendor/elasticemail/cp-v2-elasticemail.php' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/elasticemail/cp-v2-elasticemail.php';
		}

		if ( class_exists( 'CPRO_ElasticEmail' ) ) {
			$this->api_instance = new CPRO_ElasticEmail( $api_key, $account_id );
		}

		return $this->api_instance;
	}
	/**
	 * Test the API connection.
	 *
	 * @since 1.0.0
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
		} elseif ( ! isset( $fields['account_id'] ) || empty( $fields['account_id'] ) ) {
			$response['error'] = __( 'Error: You must provide an Public Account ID.', 'convertpro-addon' );
		} else {
			// Try to connect and store the connection data.
			$api = $this->get_api( $fields['api_key'], $fields['account_id'] );

			try {
				$connected = $api->connect();
				if ( false !== $connected['error'] ) {
					$response['error'] = $connected['error'];
				}

				$response['data'] = array(
					'api_key'    => $fields['api_key'],
					'account_id' => $fields['account_id'],
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
	 * @since 1.0.0
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
				'help'  => __( 'Your API key can be found in your Elastic Email account under Settings > API.', 'convertpro-addon' ),
			)
		);

		ConvertPlugHelper::render_input_html(
			'account_id',
			array(
				'class' => '',
				'type'  => 'text',
				'label' => __( 'Public Account ID', 'convertpro-addon' ),
				'help'  => __( 'Your Account ID can be found in your AElastic Email account under Settings > API.', 'convertpro-addon' ),
			)
		);
		return ob_get_clean();
	}

	/**
	 * Returns the api_key in array format
	 *
	 * @since 1.0.0
	 * @param string $auth_meta $api_key A valid API key.
	 * @return array Array of api_key
	 */
	public function render_auth_meta( $auth_meta ) {
		return array(
			'api_key'    => $auth_meta['api_key'],
			'account_id' => $auth_meta['account_id'],
		);
	}

	/**
	 * Render the markup for service specific fields.
	 *
	 * @since 1.0.0
	 * @param string $account The name of the saved account.
	 * @param object $post_data Posted data.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 *      @type string $html The field markup.
	 *      @type array $mapping_fields The field mapping array for Elastic Email.
	 * }
	 */
	public function render_fields( $account, $post_data ) {
		$account_data = ConvertPlugServices::get_account_data( $account );

		$api                 = $this->get_api( $account_data['api_key'], $account_data['account_id'] );
		$response            = array(
			'error'          => false,
			'html'           => '',
			'mapping_fields' => self::$mapping_fields,
		);
		$post_data['isEdit'] = ( isset( $post_data['isEdit'] ) ) ? $post_data['isEdit'] : null;
		// Lists field.
		try {
			$resp  = $api->getList( $account_data['api_key'] );
			$lists = $resp['lists'];

			if ( 'false' === $post_data['isEdit'] || null === $post_data['isEdit'] ) {

				if ( ! isset( $post_data['list_id'] ) ) {

					$response['html'] .= $this->render_list_field( $lists, $post_data );
				}
			} else {
				$response['html'] .= $this->render_list_field( $lists, $post_data );
			}
		} catch ( Exception $e ) {
			$response['error'] = $e->getMessage();
		}
		return $response;
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.0.0
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
			$options[ $list->listid . '|' . $list->listname ] = $list->listname;
		}

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) ) ? ( ( isset( $settings['default']['elasticemail_list'] ) ) ? $settings['default']['elasticemail_list'] : '' ) : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'elasticemail_list',
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
	 * @since 1.0.0
	 */
	public function render_mapping() {
		return self::$mapping_fields;
	}

	/**
	 * Subscribe an email address to Elastic Email.
	 *
	 * @since 1.0.0
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
			$response['error'] = __( 'There was an error subscribing to Elastic Email! The account is no longer connected.', 'convertpro-addon' );
		} else {
			$api = $this->get_api( $account_data['api_key'], $account_data['account_id'] );

			$data = array();

			$data['merge_fields'] = array();

			$fields        = array();
			$custom_fields = array();
			$cust_fields   = array();

			foreach ( $settings['param'] as $key => $p ) {

				if ( 'email' !== $key && 'date' !== $key ) {
					if ( isset( $settings['meta'][ $key ] ) ) {
						if ( 'custom_field' !== $settings['meta'][ $key ] ) {

							$fields[ $settings['meta'][ $key ] ] = $p;

						} else {

							$fields[ $settings['meta'][ $key . '-input' ] ] = $p;
							$custom_fields                                  = array(
								'name'  => $settings['meta'][ $key . '-input' ],
								'value' => $p,
							);
							array_push( $cust_fields, $custom_fields );
						}
					}
				}
			}

			// Map fields and custom fields.
			$default_fields = self::$mapping_fields;
			foreach ( $default_fields as $val ) {

				if ( isset( $fields[ $val ] ) ) {

					$data['merge_fields'][ $val ] = $fields[ $val ];
				}
			}

			if ( ! empty( $cust_fields ) ) {

				foreach ( $cust_fields as $key => $field_val ) {

					$data['merge_fields'][ $field_val['name'] ] = $field_val['value'];
				}
			}

			$data['email'] = $email;

			$listname = explode( '|', $settings['elasticemail_list'] );

			$load_data = array(
				'apikey' => $account_data['api_key'],
				'email'  => $email,
			);

			$user_data = array(
				'publicAccountID' => $account_data['account_id'],
				'email'           => $email,
				'listName'        => $listname[1],
				'sendActivation'  => false,
			);

			if ( empty( $data['merge_fields'] ) ) {
				unset( $data['merge_fields'] );
			} else {
				foreach ( $data['merge_fields'] as $key => $p ) {
					if ( 'email' !== $key && 'listName' !== $key && 'sendActivation' !== $key ) {
						$user_data[ $key ] = $p;
					}
				}
			}
			// Subscribe.
			try {

				$method    = 'contact/add';
				$json_data = http_build_query( $user_data );
				$request   = $api->cp_v2_request_elastic_data( $method, $json_data );

				if ( ! $request->success ) {
					$response['error'] = true;
				}
			} catch ( Exception $e ) {
				$response['error'] = sprintf(
					/* translators: %s Error Message */
					__( 'There was an error subscribing to ElasticEMail! %s', 'convertpro-addon' ),
					$e->getMessage()
				);
			}
		}

		return $response;
	}
}
