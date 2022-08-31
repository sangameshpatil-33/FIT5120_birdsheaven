<?php
/**
 * Collects leads and subscribe to CleverReach
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the CleverReach API.
 *
 * @since 1.0.0
 */
final class CPRO_Service_Clever_Reach extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public $id = 'clever-reach';

	/**
	 * Initialize Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( file_exists( CP_SERVICES_BASE_DIR . 'includes/vendor/clever-reach/rest_client.php' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/clever-reach/rest_client.php';
		}
	}

	/**
	 * Default Custom field array.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public static $mapping_fields = array();

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
	 * @param string $cust_id A valid Customer ID.
	 * @param string $user A valid Username.
	 * @param string $pass A valid Password.
	 * @return object The API instance.
	 */
	public function get_api( $cust_id, $user, $pass ) {

		if ( $this->api_instance ) {
			return $this->api_instance;
		}
		try {

			$rest = new CPro\CR\tools\rest( 'https://rest.cleverreach.com/v2' );
			// @codingStandardsIgnoreStart
			$rest->throwExceptions = true;  // Default.
			// @codingStandardsIgnoreEnd

			// Skip this part if you have an OAuth access token.
			$token = $rest->post(
				'/login',
				array(
					'client_id' => $cust_id,
					'login'     => $user,
					'password'  => $pass,
				)
			);

			$this->api_instance = $rest->setAuthMode( 'bearer', $token );

		} catch ( \Exception $e ) {
			$this->api_instance = false;
		}

		return $this->api_instance;
	}

	/**
	 * Test the API connection.
	 *
	 * @since 1.0.0
	 * @param array $fields {.
	 *      @type string $api_key A valid API Key.
	 * }.
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
		if ( ! isset( $fields['cust_id'] ) || empty( $fields['cust_id'] ) ) {
			$response['error'] = __( 'Error: You must provide an Customer ID.', 'convertpro-addon' );
		} elseif ( ! isset( $fields['user_name'] ) || empty( $fields['user_name'] ) ) {
			$response['error'] = __( 'Error: You must provide an Username.', 'convertpro-addon' );
		} elseif ( ! isset( $fields['pass_w'] ) || empty( $fields['pass_w'] ) ) {
			$response['error'] = __( 'Error: You must provide an Password.', 'convertpro-addon' );
		} else {
			try {
				$api = $this->get_api( $fields['cust_id'], $fields['user_name'], $fields['pass_w'] );
				if ( isset( $api->token->error ) ) {
					$response['error'] = __( 'Oops! Those are wrong credentials. Please enter the correct credentials and try again!', 'convertpro-addon' );
				} else {
					$response['data'] = array(
						'cust_id'   => $fields['cust_id'],
						'user_name' => $fields['user_name'],
						'pass_w'    => $fields['pass_w'],
					);
				}
			} catch ( Exception $e ) {
				$response['error'] = __( 'Sorry! We could not connect to the CleverReach API.', 'convertpro-addon' );
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
			'cust_id',
			array(
				'class' => 'cp_clever_reach_cust_id',
				'type'  => 'text',
				'label' => __( 'Customer ID', 'convertpro-addon' ),
				'help'  => __( 'Your API Key can be found in your CleverReach account under My Account > Extras > API.', 'convertpro-addon' ),
			)
		);

		ConvertPlugHelper::render_input_html(
			'user_name',
			array(
				'class' => 'cp_clever_reach_user_name',
				'type'  => 'text',
				'label' => __( 'User Name', 'convertpro-addon' ),
				'help'  => __( 'Your API Key can be found in your CleverReach account under My Account > Extras > API.', 'convertpro-addon' ),
			)
		);

		ConvertPlugHelper::render_input_html(
			'pass_w',
			array(
				'class' => 'cp_clever_reach_pass_w',
				'type'  => 'text',
				'label' => __( 'Password', 'convertpro-addon' ),
				'help'  => __( 'Your API Key can be found in your CleverReach account under My Account > Extras > API.', 'convertpro-addon' ),
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
			'cust_id'   => $auth_meta['cust_id'],
			'user_name' => $auth_meta['user_name'],
			'pass_w'    => $auth_meta['pass_w'],
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

		$post_data = ConvertPlugHelper::get_post_data();
		$response  = array(
			'error'          => false,
			'html'           => '',
			'mapping_fields' => self::$mapping_fields,
		);

		// Get the list data.
		$account_data = ConvertPlugServices::get_account_data( $account );
		$api          = $this->get_api( $account_data['cust_id'], $account_data['user_name'], $account_data['pass_w'] );
		$rest         = new CPro\CR\tools\rest( 'https://rest.cleverreach.com/v2' );
		$lists        = $rest->get( '/groups?token=' . rawurlencode( $api->token ) );
		$forms        = $rest->get( '/forms?token=' . rawurlencode( $api->token ) );

		if ( 'false' === $settings['isEdit'] || null === $settings['isEdit'] ) {
			/* Double optin `yes` show form field. */
			if ( isset( $post_data['double_optin'] ) && 'yes' === $post_data['double_optin'] ) {
				if ( ! empty( $forms->error ) && '0' === $forms->result_code ) {
					$response['error'] .= __( 'Error: No forms found.', 'convertpro-addon' );
				} else {
					$response['html'] .= $this->render_form_field( $forms, $settings, $post_data['list_id'] );
				}
			} else {
				/* List change check double optin checked or not. */
				if ( isset( $post_data['double_optin'] ) && 'no_list' === $post_data['double_optin'] ) {
					$response['html'] .= '';
				} else {
					$response['html'] .= $this->render_list_field( $lists, $settings );
					$response['html'] .= $this->render_optin_field( '', $settings );
				}
			}
		} else {
			/* Default settings load if set. */
			$response['html'] .= $this->render_list_field( $lists, $settings );
			$response['html'] .= $this->render_optin_field( 'checked', $settings );
			if ( ! empty( $settings['default']['cleverreach_double_optin'] ) && 'on' === $settings['default']['cleverreach_double_optin'] ) {
				$response['html'] .= $this->render_form_field( $forms, $settings, $settings['default']['cleverreach_lists'] );
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
	 * Render markup for the list field.
	 *
	 * @since 1.0.0
	 * @param array  $lists Account data from the API.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the list field.
	 * @access private
	 */
	private function render_list_field( $lists, $settings ) {

		$default = '';
		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['cleverreach_lists'] ) ) ? $settings['default']['cleverreach_lists'] : '';
		}
		// Render the list field.
		ob_start();
		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);

		foreach ( $lists as $value ) {
			if ( $value->id && $value->name ) {
				$options[ $value->id ] = $value->name;
			}
		}

		ConvertPlugHelper::render_input_html(
			'cleverreach_lists',
			array(
				'class'   => 'cpro-select',
				'type'    => 'select',
				'label'   => _x( 'List', 'Select a list from CleverReach.', 'convertpro-addon' ),
				'default' => $default,
				'options' => $options,
			),
			$settings
		);

		return ob_get_clean();
	}

	/**
	 * Render markup for the optin field.
	 *
	 * @since 1.0.0
	 * @param string $default Posted data.
	 * @param array  $settings Posted data.
	 * @return string The markup for the optin field.
	 * @access private
	 */
	private function render_optin_field( $default, $settings ) {

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['cleverreach_double_optin'] ) ) ? $settings['default']['cleverreach_double_optin'] : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'cleverreach_double_optin',
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
	 * Render markup for the form field
	 *
	 * @since 1.0.0
	 * @param array  $forms Form data from the API.
	 * @param object $settings Saved module settings.
	 * @param string $list_id selected list id.
	 * @return string The markup for the form field.
	 * @access private
	 */
	private function render_form_field( $forms, $settings, $list_id ) {

		$default = '';
		if ( $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['cleverreach_double_forms'] ) ) ? $settings['default']['cleverreach_double_forms'] : '';
		}
		ob_start();

		if ( '-1' === $list_id ) {
			$options = array(
				'-1' => __( 'Please choose list...', 'convertpro-addon' ),
			);
		} else {
			$options = array(
				'-1' => __( 'Choose...', 'convertpro-addon' ),
			);
		}

		foreach ( (array) $forms as $form ) {
			if ( is_object( $form ) && isset( $form->id ) ) {
				if ( (string) $list_id === (string) $form->customer_tables_id ) {
					$options[ $form->id ] = $form->name;
				}
			}
		}

		ConvertPlugHelper::render_input_html(
			'cleverreach_double_forms',
			array(
				'class'   => 'cpro-select',
				'type'    => 'select',
				'label'   => _x( 'Form', 'Select a form for this list.', 'convertpro-addon' ),
				'default' => $default,
				'options' => $options,
			),
			$settings
		);
		return ob_get_clean();
	}


	/**
	 * Subscribe an email address to CleverReach.
	 *
	 * @since 1.0.0
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $settings, $email ) {

		// API Key.
		$account  = ConvertPlugServices::get_account_data( $settings['api_connection'] );
		$api      = $this->get_api( $account['cust_id'], $account['user_name'], $account['pass_w'] );
		$response = array(
			'error' => false,
		);
		// user data.
		$user_data = array();

		foreach ( $settings['param'] as $key => $value ) {
			if ( 'email' !== $key && 'date' !== $key && 'custom_field' === $settings['meta'][ $key ] ) {
					$custom_field               = $settings['meta'][ $key . '-input' ];
					$user_data[ $custom_field ] = $value;
			}
		}

		$rest = new CPro\CR\tools\rest( 'https://rest.cleverreach.com/v2' );
		try {
			$source               = get_bloginfo( 'name' );
			$send_activation_mail = false;
			$time                 = ( $send_activation_mail ? false : time() );
			$attribute            = 'global_attributes';
			if ( isset( $settings['cleverreach_double_optin'] ) && isset( $api->token ) && 'on' === $settings['cleverreach_double_optin'] ) {
				$time         = 0;
				$send_confirm = true;
				$attribute    = 'attributes';
			}
			$user = array(
				'email'             => $email,
				'registered'        => time(),
				'activated'         => $time,
				'source'            => $source,
				'attributes'        => $user_data,
				'global_attributes' => $user_data,
			);

			$response['checkbox'] = $settings['cleverreach_double_optin'];

			if ( ( 'on' === $settings['cleverreach_double_optin'] ) && isset( $settings['cleverreach_double_optin'] ) && isset( $api->token ) ) {

				$success = $rest->post( '/groups/' . $settings['cleverreach_lists'] . '/receivers/insert?token=' . $api->token, $user );

				if ( $success ) {

					$new_user = array(
						'email'     => $email,
						'groups_id' => $settings['cleverreach_lists'],
						'doidata'   => array(
							'user_ip'    => $_SERVER['REMOTE_ADDR'],
							'referer'    => $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
							'user_agent' => $_SERVER['HTTP_USER_AGENT'],
						),
					);

					$pg = $rest->post( '/forms/' . $settings['cleverreach_double_forms'] . '/send/activate?token=' . $api->token, $new_user );

					if ( ! $pg ) {
						$response['error'] = __( 'Something went wrong! Please try again.', 'convertpro-addon' );
					}
				} else {
					$response['error'] = __( 'Something went wrong! Please try again.', 'convertpro-addon' );
				}
			} else {

				$result = $rest->post( '/groups/' . $settings['cleverreach_lists'] . '/receivers/?token=' . $api->token, $user );
				if ( ! $result ) {
					$response['error'] = __( 'Something went wrong! Please try again.', 'convertpro-addon' );
				}
			}
		} catch ( Exception $e ) {
			$return['success'] = false;
			$return['error']   = __( 'Sorry! We could not connect to the CleverReach API.', 'convertpro-addon' );
		}
		return $response;
	}

}
