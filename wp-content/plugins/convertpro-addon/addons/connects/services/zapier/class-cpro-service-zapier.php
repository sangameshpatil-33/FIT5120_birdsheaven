<?php
/**
 * ConverPlug Service Zapier
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the Zapier API.
 *
 * @since 1.0.0
 */
final class CPRO_Service_Zapier extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public $id = 'zapier';

	/**
	 * Default Custom field array.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public static $mapping_fields = array(
		'name',
		'first_name',
		'last_name',
		'fullname',
		'username',
		'phone',
	);

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
	 * @param string $webhook_url Webhook URL.
	 * @return object The API instance.
	 */
	public function get_api( $webhook_url ) {

		if ( $this->api_instance ) {
			return $this->api_instance;
		}

		$auth['webhook_url'] = $webhook_url;

		if ( file_exists( CP_SERVICES_BASE_DIR . 'includes/vendor/zapier/zapier.php' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/zapier/zapier.php';
		}

		if ( class_exists( 'CPRO_Zapier' ) ) {
			$this->api_instance = new CPRO_Zapier( $auth );
		}

		return $this->api_instance;
	}

	/**
	 * Test the API connection.
	 *
	 * @since 1.0.0
	 * @param array $fields {.
	 *      @type array $fields authentication fields.
	 * }
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

		if ( ! isset( $fields['webhook_url'] ) || empty( $fields['webhook_url'] ) ) {
			$response['error'] = __( 'Error: You must provide a Webhook URL.', 'convertpro-addon' );
		} else {
			$response['data'] = array(
				'webhook_url' => $fields['webhook_url'],
			);
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
			'webhook_url',
			array(
				'class' => 'cp_webhook_url',
				'type'  => 'text',
				'label' => __( 'Webhook URL', 'convertpro-addon' ),
				'help'  => __( 'Your project ID can be found in your ConvertFox account under Settings.', 'convertpro-addon' ),
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
			'webhook_url' => $authmeta['webhook_url'],
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
		$response = array(
			'error'          => false,
			'html'           => '',
			'mapping_fields' => self::$mapping_fields,
		);

		$response['html'] .= __( '<div class="zapier-nolist-wrap">Zapier does not require list or tags, you can directly move to mapping fields by clicking next button.</div>', 'convertpro-addon' );

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
	 * Subscribe an email address to Zapier.
	 *
	 * @since 1.0.0
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $settings, $email ) {

		$account     = ConvertPlugServices::get_account_data( $settings['api_connection'] );
		$webhook_url = $account['webhook_url'];
		$response    = array(
			'error' => false,
		);

		if ( ! $account ) {
			$response['error'] = __( 'There was an error subscribing to Zapier! The account is no longer connected.', 'convertpro-addon' );
		} else {

			$custom_arr = array();
			foreach ( $settings['param'] as $key => $p ) {

				if ( 'email' !== $key && 'date' !== $key ) {
					if ( 'custom_field' !== $settings['meta'][ $key ] ) {
						$custom_arr[ $settings['meta'][ $key ] ] = $p;
					} else {
						$custom_arr[ $settings['meta'][ $key . '-input' ] ] = $p;
					}
				}
			}

			$custom_arr['email'] = $email;

			$request = wp_remote_post(
				$webhook_url,
				array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array( 'Accept: application/json', 'Content-Type: application/json' ),
					'body'        => $custom_arr,
					'cookies'     => array(),
				)
			);

			if ( is_wp_error( $request ) ) {
				$error_message     = $request->get_error_message();
				$response['error'] = "Something went wrong: $error_message";
			}
		}
		return $response;
	}

	/**
	 * Test connection call
	 *
	 * @since 1.0.0
	 * @param array $mailer_data Mailer related info.
	 * @param array $post_data Post data.
	 * @param array $mapping_meta Mapping meta data.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function test_connection( $mailer_data, $post_data, $mapping_meta ) {

		$style_id     = $post_data['style_id'];
		$service_auth = maybe_unserialize( $mailer_data[ CP_API_CONNECTION_SERVICE_AUTH ][0] );
		$webhook_url  = $service_auth['webhook_url'];
		$fields       = array( 'email' => '' );
		$response     = array(
			'error' => false,
		);

		if ( is_array( $mapping_meta ) ) {

			foreach ( $mapping_meta as $key => $value ) {
				if ( '' !== $value ) {
					$fields[ $value ] = '';
				}
			}
		}

		$request = wp_remote_post(
			$webhook_url,
			array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'Accept: application/json', 'Content-Type: application/json' ),
				'body'        => $fields,
				'cookies'     => array(),
			)
		);

		if ( is_wp_error( $request ) ) {
			$error_message     = $request->get_error_message();
			$response['error'] = "Something went wrong: $error_message";
		}

		return $response;
	}
}
