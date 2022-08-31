<?php
/**
 * ConverPlug Service SimplyCast
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the SendInBlue API.
 *
 * @since 1.0.0
 */
final class CPRO_Service_SendinBlue extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public $id = 'sendinblue';

	/**
	 * Sendinblue version number.
	 *
	 * @since 1.4.3
	 * @var int $sendinblue_version
	 */
	public $sendinblue_version = 2;

	/**
	 * Default Custom field array.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public static $mapping_fields = array();

	/**
	 * API object.
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

		if ( strlen( $api_key ) > 16 && ! class_exists( 'CPRO_Sendinblue_Api' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/sendinblue/CPRO_Sendinblue_Api.php';
			$this->api_instance       = new CPRO_Sendinblue_Api( 'https://api.sendinblue.com/v3/', $api_key );
			$this->sendinblue_version = 3;
		} elseif ( ! class_exists( 'CPRO_MAILIN' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/sendinblue/Mailin.php';
			$this->api_instance = new CPRO_MAILIN( 'https://api.sendinblue.com/v2.0', $api_key );
		}

		return $this->api_instance;
	}

	/**
	 * Test the API connection.
	 *
	 * @since 1.0.0
	 * @param array $fields  A valid API Key.
	 *
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

		// Make sure we have an api key.
		if ( ! isset( $fields['api_key'] ) || empty( $fields['api_key'] ) ) {
			$response['error'] = __( 'Error: You must provide an Access Key.', 'convertpro-addon' );
		} else {
			// Try to connect and store the connection data.
			$api = $this->get_api( $fields['api_key'] );

			if ( 3 === $this->sendinblue_version ) {
				$result = $api->make_request( 'account' );
			} else {
				$result = $api->get_account();
			}

			if ( ! is_array( $result ) ) {
				$response['error'] = __( 'There was an error connecting to SendinBlue! Please try again.', 'convertpro-addon' );
			} elseif ( isset( $result['code'] ) && 'failure' === $result['code'] ) {
				/* translators: %s Message */
				$response['error'] = sprintf( __( 'Error: Could not connect to SendinBlue. %s', 'convertpro-addon' ), $result['message'] );
			} else {
				$response['data'] = array(
					'api_key' => $fields['api_key'],
				);
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
				'class' => 'cp_sendinblue_api_key',
				'type'  => 'text',
				'label' => __( 'API Key', 'convertpro-addon' ),
				'help'  => __( 'The API Key can be found under API & Integration in your SendinBlue account. > Manager Your Keys > Version 2.0 > Access Key.', 'convertpro-addon' ),
			)
		);

		return ob_get_clean();
	}

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @param object $authmeta Authentication meta.
	 * @since 1.0.0
	 * @return string The connection settings markup.
	 */
	public function render_auth_meta( $authmeta ) {
		return array(
			'api_key' => $authmeta['api_key'],
		);
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
		$post_data    = ConvertPlugHelper::get_post_data();
		$account_data = ConvertPlugServices::get_account_data( $account );
		$api          = $this->get_api( $account_data['api_key'] );
		$response     = array(
			'error'          => false,
			'html'           => '',
			'mapping_fields' => array(),
		);

		$double_optin_templates = array();

		if ( 3 === $this->sendinblue_version ) {
			$result = $api->make_request(
				'contacts/lists',
				array(
					'limit'  => 50,
					'offset' => 0,
				)
			);
			// This if condition is for getting more than 50 lists present.
			if ( isset( $result['data']['count'] ) && 50 < (int) $result['data']['count'] ) {
				$list_count  = (int) $result['data']['count'] / 50;
				$temp_result = array(
					'code' => 'failure',
					'data' => array(
						'lists' => array(),
					),
				);
				for ( $i = 1; $i <= $list_count; $i++ ) {
					$result_list                  = $api->make_request(
						'contacts/lists',
						array(
							'limit'  => 50,
							'offset' => 50 * $i,
						)
					);
					$temp_result['code']          = $result_list['code'];
					$temp_result['data']['lists'] = array_merge( $result['data']['lists'], $result_list['data']['lists'] );
				}
				$result = $temp_result;
			}

			// Get Template ids for double-optin for Sendinblue V3.
			$args = array(
				'templateStatus' => 'true',
				'limit'          => 1000,
			);

			$result_template = $api->make_request( 'smtp/templates', $args );

			if ( isset( $result_template['data']['templates'] ) && ! empty( $result_template['data']['templates'] ) ) {
				foreach ( $result_template['data']['templates'] as $template ) {
					$double_optin_templates[ $template['id'] ] = $template['name'];
				}
			}
		} else {
			$result = $api->get_lists( 1, 50 );
		}

		if ( ! is_array( $result ) ) {
			$response['error'] = __( 'There was an error connecting to SendinBlue! Please try again.', 'convertpro-addon' );
		} elseif ( isset( $result['code'] ) && 'failure' === $result['code'] ) {
			/* translators: %s Message */
			$response['error'] = sprintf( __( 'Error: Could not connect to SendinBlue. %s', 'convertpro-addon' ), $result['message'] );
		} else {

			if ( ! isset( $settings['isEdit'] ) || 'false' === $settings['isEdit'] || null === $settings['isEdit'] ) {
				/* Double optin `yes` show template and redirection URL fields. */
				if ( isset( $post_data['double_optin'] ) && 'yes' === $post_data['double_optin'] ) {
					$response['html'] .= $this->render_template_ids( $double_optin_templates, $settings );
				} else {
					$response['html'] .= $this->render_list_field( $result['data']['lists'], $settings );
					$response['html'] .= $this->render_optin_field( '', $settings );
				}
			} else {
				/* Default settings load if set. */
				$response['html'] .= $this->render_list_field( $result['data']['lists'], $settings );
				$response['html'] .= $this->render_optin_field( 'checked', $settings );
				if ( ! empty( $settings['default']['sendinblue_double_optin'] ) && 'on' === $settings['default']['sendinblue_double_optin'] ) {
					$response['html'] .= $this->render_template_ids( $double_optin_templates, $settings );
				}
			}

			/* Sendinblue mapping attributes. */
			if ( 3 === $this->sendinblue_version ) {
				$sendinblue_attrs = $api->make_request( 'contacts/attributes' );
				$sendinblue_attrs = array_filter(
					$sendinblue_attrs['data']['attributes'],
					function( $filter_fields ) {
						return ( 'normal' === $filter_fields['category'] );
					}
				);
			} else {
				$sendinblue_attrs = $api->get_attributes();
				$sendinblue_attrs = array_slice( $sendinblue_attrs['data']['normal_attributes'], 0, 3 );
			}
			foreach ( $sendinblue_attrs as $attr ) {
				self::$mapping_fields[] = $attr['name'];
			}

			$response['mapping_fields'] = self::$mapping_fields;
		}

		return $response;
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.0.0
	 * @param array  $lists List data from the API.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the list field.
	 * @access private
	 */
	private function render_list_field( $lists, $settings ) {

		$default = '';
		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['sendinblue_lists'] ) ) ? $settings['default']['sendinblue_lists'] : '';
		}

		ob_start();

		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);

		foreach ( $lists as $list ) {
			$options[ $list['id'] ] = $list['name'];
		}

		ConvertPlugHelper::render_input_html(
			'sendinblue_lists',
			array(
				'class'   => 'cpro-select',
				'type'    => 'select',
				'label'   => _x( 'Select a List', 'An email list from SendinBlue.', 'convertpro-addon' ),
				'default' => $default,
				'options' => $options,
			),
			$settings
		);

		return ob_get_clean();
	}

	/**
	 * Render markup for the Sendinblue Template Ids for double-optin field.
	 *
	 * @since 1.5.0
	 * @param array  $template_ids Template Ids data from the API.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the Sendinblue Template Ids field.
	 * @access private
	 */
	private function render_template_ids( $template_ids, $settings ) {

		$default_template_id     = '';
		$default_redirection_url = '';
		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default_template_id     = ( isset( $settings['default']['sendinblue_template_ids'] ) ) ? $settings['default']['sendinblue_template_ids'] : '';
			$default_redirection_url = ( isset( $settings['default']['sendinblue_redirection_url'] ) ) ? $settings['default']['sendinblue_redirection_url'] : '';
		}
		ob_start();

		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);

		foreach ( $template_ids as $template_id => $template_id_name ) {
			$options[ $template_id ] = $template_id_name;
		}

		ConvertPlugHelper::render_input_html(
			'sendinblue_template_ids',
			array(
				'class'   => 'sendinblue_template_ids',
				'type'    => 'select',
				'label'   => _x( 'Select a Template ID', 'An Template list Id from SendinBlue for Double-optin.', 'convertpro-addon' ),
				'default' => $default_template_id,
				'options' => $options,
			),
			$settings
		);

		ConvertPlugHelper::render_input_html(
			'sendinblue_redirection_url',
			array(
				'class'   => 'sib_redirection_url',
				'type'    => 'text-wrap',
				'label'   => __( 'Redirection URL', 'convertpro-addon' ),
				'help'    => __( 'URL of the web page that user will be redirected to after clicking on the double opt in URL.', 'convertpro-addon' ),
				'default' => $default_redirection_url,
			),
			$settings
		);

		return ob_get_clean();
	}

	/**
	 * Render markup for the double-optin field.
	 *
	 * @since 1.5.0
	 * @param string $default default value.
	 * @param array  $settings Posted data.
	 * @return string The markup for the double-optin field.
	 * @access private
	 */
	private function render_optin_field( $default, $settings ) {

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default']['sendinblue_double_optin'] ) ) ? $settings['default']['sendinblue_double_optin'] : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'sendinblue_double_optin',
			array(
				'class'   => '',
				'type'    => 'checkbox',
				'label'   => __( 'Enable Double Opt-in', 'convertpro-addon' ),
				'help'    => '',
				'default' => $default,
			),
			$settings
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
	 * Subscribe an email address to SendInBlue.
	 *
	 * @since 1.0.0
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @param string $name The name to subscribe.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $settings, $email, $name = false ) {

		$account_data = ConvertPlugServices::get_account_data( $settings['api_connection'] );
		$response     = array(
			'error' => false,
		);

		if ( ! $account_data ) {
			$response['error'] = __( 'There was an error subscribing to SendinBlue! The account is no longer connected.', 'convertpro-addon' );
		} else {

			$api = $this->get_api( $account_data['api_key'] );

			foreach ( $settings['param'] as $key => $p ) {
				if ( 'email' !== $key && 'date' !== $key ) {
					if ( 'custom_field' === $settings['meta'][ $key ] ) {
						$custom_field                = $settings['meta'][ $key . '-input' ];
						$subscriber[ $custom_field ] = $p;
					} else {
						$subscriber[ $settings['meta'][ $key ] ] = $p;
					}
				}
			}

			if ( empty( $subscriber ) || null === $subscriber ) {
				$subscriber = '';
			}

			if ( 3 === $this->sendinblue_version ) {
				$lead_data = array(
					'email'            => $email,
					'listIds'          => array( absint( $settings['sendinblue_lists'] ) ),
					'updateEnabled'    => true,
					'attributes'       => (object) $subscriber,
					'emailBlacklisted' => false,
					'smsBlacklisted'   => false,
				);

				$default_contact_route = 'contacts';

				$req = $api->make_request( sprintf( 'contacts/%s', rawurlencode( $email ) ) );

				if ( ! isset( $req['data']['listIds'] ) && isset( $settings['sendinblue_double_optin'] ) && 'on' === $settings['sendinblue_double_optin'] ) {

					unset( $lead_data['listIds'] );
					unset( $lead_data['updateEnabled'] );
					$lead_data['includeListIds'] = array( absint( $settings['sendinblue_lists'] ) );
					$lead_data['templateId']     = absint( $settings['sendinblue_template_ids'] );
					$lead_data['redirectionUrl'] = esc_url( $settings['sendinblue_redirection_url'] );

					$default_contact_route = 'contacts/doubleOptinConfirmation';
				}

				$result = $api->make_request( $default_contact_route, $lead_data, 'post' );

			} else {
				$result = $api->create_update_user( $email, $subscriber, 0, array( $settings['sendinblue_lists'] ), array(), 0 );
			}
			if ( ! is_array( $result ) ) {
				$response['error'] = __( 'There was an error subscribing to SendinBlue! Please try again.', 'convertpro-addon' );
			} elseif ( isset( $result['code'] ) && 'failure' === $result['code'] ) {
				/* translators: %s Message */
				$response['error'] = sprintf( __( 'Error: Could not subscribe to SendinBlue. %s', 'convertpro-addon' ), $result['message'] );
			}
		}

		return $response;
	}
}
