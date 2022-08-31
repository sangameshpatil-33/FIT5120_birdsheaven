<?php
/**
 * Collects leads and subscribe to ConvertKit
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the ConvertKit API.
 *
 * @since 1.0.0
 */
final class CPRO_Service_ConvertKit extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public $id = 'convertkit';

	/**
	 * Default Custom field array.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public static $mapping_fields = array( 'FIRST NAME' );

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
	 * @param string $api_key A valid API Key.
	 * @return object The API instance.
	 */
	public function get_api( $api_key ) {
		if ( $this->api_instance ) {
			return $this->api_instance;
		}

		if ( file_exists( CP_SERVICES_BASE_DIR . 'includes/vendor/convertkit/ConvertKit.php' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/convertkit/ConvertKit.php';
		}

		if ( class_exists( 'CPRO_ConvertKit' ) ) {
			$this->api_instance = new CPRO_ConvertKit( $api_key );
		}

		return $this->api_instance;
	}

	/**
	 * Test the API connection.
	 *
	 * @since 1.0.0
	 * @param array $fields {.
	 *      @type string $api_key A valid API Key.
	 * }
	 * @throws Exception Error Message.
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

		// Make sure we have an API Key.
		if ( ! isset( $fields['api_key'] ) || empty( $fields['api_key'] ) ) {
			$response['error'] = __( 'Error: You must provide an API Key.', 'convertpro-addon' );
		} else {
			// Try to connect and store the connection data.
			try {
				$api = $this->get_api( $fields['api_key'] );
				if ( ! empty( $api ) ) {
					if ( $api->is_authenticated() ) {
						$response['data'] = array(
							'api_key' => $fields['api_key'],
						);
					} else {
						$response['error'] = __( 'Oops! You\'ve entered the wrong API Key. Please enter the API key and try again.', 'convertpro-addon' );
					}
				} else {
					throw new Exception( 'Error: There seems to be an error with the configuration' );
				}
			} catch ( Exception $e ) {
				$response['error'] = $e->getMessage();
			}
		}
		return $response;
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
			'api_key' => $auth_meta['api_key'],
		);
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
				'class' => 'cpro-input',
				'type'  => 'text',
				'label' => __( 'API Key', 'convertpro-addon' ),
				'help'  => __( 'Your API Key can be found in your ConvertKit account under Account > Account Settings > API Key.', 'convertpro-addon' ),
			)
		);

		return ob_get_clean();
	}

	/**
	 * Render the markup for service specific fields.
	 *
	 * @since 1.0.0
	 * @param string $account The name of the saved account.
	 * @param object $settings Saved module settings.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 *      @type string $html The field markup.
	 * }
	 */
	public function render_fields( $account, $settings ) {
		$account_data = ConvertPlugServices::get_account_data( $account );
		$api          = $this->get_api( $account_data['api_key'] );
		$forms        = $api->get_resources( 'forms' );
		$tags         = $api->get_resources( 'tags' );
		$response     = array(
			'error'          => false,
			'html'           => '',
			'mapping_fields' => self::$mapping_fields,
		);
		$res          = array();
		$res_tags     = array();

		if ( ! $forms ) {
			$response['error'] = __( 'Oops! You\'ve entered the wrong API Key. Please enter the API key and try again.', 'convertpro-addon' );
		} else {
			foreach ( $forms as $value ) {
				foreach ( $value as $form ) {
					$res[ $form['id'] ] = $form['name'];
				}
			}
			foreach ( $tags as $value ) {
				foreach ( $value as $tag ) {
					$res_tags[ $tag['id'] ] = $tag['name'];
				}
			}
			if ( ! empty( $res ) ) {
				$response['html']  = $this->render_form_field( $res, $settings );
				$response['html'] .= $this->render_tags_field( $res_tags, $settings );
			} else {
				$response['error'] .= __( 'Error: No forms found.', 'convertpro-addon' );
			}
		}

		return $response;
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
	 * Render markup for the form field.
	 *
	 * @since 1.0.0
	 * @param array  $forms Form data from the API.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the form field.
	 * @access private
	 */
	private function render_form_field( $forms, $settings ) {

		$default = '';
		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = isset( $settings['default']['convertkit_forms'] ) ? $settings['default']['convertkit_forms'] : '';
		}
		ob_start();

		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);

		foreach ( $forms as $key => $value ) {
			$options[ $key ] = $value;
		}

		ConvertPlugHelper::render_input_html(
			'convertkit_forms',
			array(
				'class'   => 'cpro-select',
				'type'    => 'select',
				'label'   => _x( 'Form', 'A list of forms from ConvertKit.', 'convertpro-addon' ),
				'default' => $default,
				'options' => $options,
			),
			$settings
		);

		return ob_get_clean();
	}

	/**
	 * Render markup for the tags field.
	 *
	 * @since 1.0.0
	 * @param object $tags tags list.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the tags field.
	 * @access private
	 */
	private function render_tags_field( $tags, $settings ) {
		ob_start();

		$default = '';
		if ( isset( $settings['isEdit'] ) && '' !== $settings['isEdit'] ) {
			$default = isset( $settings['default']['convertkit_tags'] ) ? $settings['default']['convertkit_tags'] : '';
		}

		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);

		foreach ( $tags as $key => $value ) {
			$options[ $key ] = $value;
		}

		ConvertPlugHelper::render_input_html(
			'convertkit_tags',
			array(
				'class'   => '',
				'type'    => 'multi-select',
				'help'    => __( 'Please separate tags with a comma.', 'convertpro-addon' ),
				'label'   => _x( 'Tags', 'A list of tags from ConvertKit.', 'convertpro-addon' ),
				'default' => $default,
				'options' => $options,
			),
			$settings
		);

		return ob_get_clean();
	}

	/**
	 * Subscribe an email address to ConvertKit.
	 *
	 * @since 1.0.0
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @param array  $dynamic_tags get the dynamic tags via checkboxes.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $settings, $email, $dynamic_tags ) {
		$account_data = ConvertPlugServices::get_account_data( $settings['api_connection'] );
		$response     = array(
			'error' => false,
		);
		$first_name   = '';

		if ( ! $account_data ) {
			$response['error'] = __( 'There was an error subscribing to ConvertKit! The account is no longer connected.', 'convertpro-addon' );
		} else {

			$api = $this->get_api( $account_data['api_key'] );

			$data = array();

			$data['fields'] = array();

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

					$data['fields'][ $val ] = $fields[ $val ];
				}
			}

			if ( ! empty( $cust_fields ) ) {

				foreach ( $cust_fields as $key => $field_val ) {

					$data['fields'][ $field_val['name'] ] = $field_val['value'];
				}
			}

			foreach ( $settings['meta'] as $key => $value ) {
				if ( 'FIRST NAME' === $settings['meta'][ $key ] ) {
					$custom_field = $key;
				}
			}

			foreach ( $settings['param'] as $key => $value ) {
				if ( $key === $custom_field ) {
					$first_name = $value;
				}
			}

			$convertkit_tags = isset( $settings['convertkit_tags'] ) ? $settings['convertkit_tags'] : array();

			/**
			 * Dynamic Tags support from the Checkboxes, Radio, and Dropdown selection.
			 * As Convertkit tags are accepted as array format.
			 * So $dynamic_tags variable will receive in array.
			 */
			if ( ! empty( $dynamic_tags ) ) {
				$get_tags             = $api->get_resources( 'tags' );
				$filter_selected_tags = array_filter(
					$get_tags['tags'],
					function( $tags ) use ( $dynamic_tags ) {
						return ( in_array( $tags['name'], $dynamic_tags, true ) );
					}
				);

				$ck_tags = array();
				if ( ! empty( $filter_selected_tags ) ) {
					$ck_tags = array_map(
						function( $tags_id ) {
							return (string) $tags_id['id'];
						},
						$filter_selected_tags
					);
				}

				$convertkit_tags = array_merge( $convertkit_tags, $ck_tags );
			}

			// Form Subscribe.
			try {
				$result = $api->form_subscribe(
					$settings['convertkit_forms'],
					array(
						'email'      => $email,
						'first_name' => $first_name,
						'fields'     => $data['fields'],
					),
					$convertkit_tags
				);
			} catch ( Exception $e ) {
				$response['error'] = sprintf(
					/* translators: %s Error Message */
					__( 'There was an error subscribing to ConvertKit! %s', 'convertpro-addon' ),
					$e->getMessage()
				);
			}
		}
		return $response;
	}
}
