<?php
/**
 * Collects leads and subscribe to FluentCRM
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the FluentCRM API.
 *
 * @since 1.5.0
 */
final class CPRO_Service_FluentCRM extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.5.0
	 * @var string $id
	 */
	public $id = 'fluentcrm';

	/**
	 * Constructor function.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function __construct() {

		add_filter( 'cp_static_account_service', array( $this, 'cp_static_account_service' ) );

		if ( ! function_exists( 'FluentCrmApi' ) && ! defined( 'FLUENTCRM_PLUGIN_VERSION' ) ) {
			self::$check_fluentcrm = true;
		}
	}

	/**
	 * Filter callback function.
	 *
	 * @param string $string FluentCRM slug.
	 * @since 1.5.0
	 * @return string $string FluentCRM slug.
	 */
	public function cp_static_account_service( $string ) {
		$string = $this->$id;
		return $string;
	}

	/**
	 * Default field array.
	 * This is predefined fields array that FluentCRM
	 * has already defined. When FluentCRM releases the new
	 * set of fields, we need to update this array.
	 *
	 * @since 1.5.0
	 * @var string $id
	 */
	public static $mapping_fields = array( 'first_name', 'last_name', 'prefix', 'phone', 'address_line_1', 'address_line_2', 'city', 'state', 'country', 'postal_code', 'date_of_birth' );

	/**
	 * Default check boolean.
	 * This is predefined field is to check
	 * whether the FluentCRM plugin is installed or not.
	 *
	 * @since 1.5.0
	 * @var bool $check_fluentcrm
	 */
	public static $check_fluentcrm = false;

	/**
	 * Test the API connection.
	 *
	 * @since 1.5.0
	 * @param array $fields A valid API credentials.
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

		if ( self::$check_fluentcrm ) {

			$response['error'] = __( 'Error: FluentCRM connects addon requires FluentCRM plugin installed and activated.', 'convertpro-addon' );
		}
		return $response;
	}

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @since 1.5.0
	 * @return string The connection settings markup.
	 */
	public function render_connect_settings() {
		ob_start();
		return ob_get_clean();
	}

	/**
	 * Returns boolean.
	 *
	 * @since 1.5.0
	 * @param @type string $auth_meta A valid API credentials.
	 * @return bool
	 */
	public function render_auth_meta( $auth_meta ) {
		return true;
	}

	/**
	 * Render the markup for service specific fields.
	 *
	 * @since 1.5.0
	 * @param string $account The name of the saved account.
	 * @param object $settings Saved module settings.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 *      @type string $html The field markup.
	 * }
	 */
	public function render_fields( $account, $settings ) {

		if ( self::$check_fluentcrm ) {

			$response = array(
				'error' => __( 'Error: FluentCRM connects addon requires FluentCRM plugin installed and activated.', 'convertpro-addon' ),
			);
			return $response;
		}

		$response = array(
			'error'          => false,
			'html'           => '',
			'mapping_fields' => self::$mapping_fields,
		);

		$fluentcrm_api_list = FluentCrmApi( 'lists' )->all();
		$lists              = array();

		foreach ( $fluentcrm_api_list as $list ) {
			$lists[ $list->id ] = $list->title;
		}
		if ( empty( $lists ) ) {
			$response['error'] = __( 'No lists found in your FluentCRM account.', 'convertpro-addon' );
		} else {
			$tags_array = array();

			$get_all_tags = FluentCrmApi( 'tags' )->all();

			if ( is_object( $get_all_tags ) && ! is_wp_error( $get_all_tags ) ) {
				foreach ( $get_all_tags as $tag ) {
					if ( isset( $tag->id ) ) {
						$tags_array[ $tag->id ] = $tag->title;
					}
				}
			}
			$response['html'] = $this->render_list_field( $lists, $settings );
			if ( ! empty( $tags_array ) ) {
				$response['html'] .= $this->render_tags_field( $tags_array, $settings );
			}
			$response['html'] .= $this->render_optin_field( $settings );
		}
		return $response;
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.5.0
	 * @param array  $lists List data from the API.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the list field.
	 * @access private
	 */
	private function render_list_field( $lists, $settings ) {
		ob_start();

		$options = array();
		$default = '';
		foreach ( $lists as $list_id => $list_title ) {
			$options[ $list_id ] = $list_title;
		}

		if ( $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['fluentcrm_list'] ) ) ? $settings['default']['fluentcrm_list'] : '';
		}
		ConvertPlugHelper::render_input_html(
			'fluentcrm_list',
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
	 * Render markup for the tags field.
	 *
	 * @since 1.5.0
	 * @param array  $tags An array of group data.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the group field.
	 * @access private
	 */
	private function render_tags_field( $tags, $settings ) {

		$options = array();
		$default = '';

		foreach ( $tags as $key => $group ) {
			$options[ $key ] = $group;
		}

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['fluentcrm_tags'] ) ) ? $settings['default']['fluentcrm_tags'] : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'fluentcrm_tags',
			array(
				'class'   => '',
				'type'    => 'multi-select',
				'label'   => __( 'Select Tags', 'convertpro-addon' ),
				'help'    => '',
				'default' => $default,
				'options' => $options,
			)
		);

		return ob_get_clean();
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.5.0
	 * @param array $settings Posted data.
	 * @return string The markup for the list field.
	 * @access private
	 */
	private function render_optin_field( $settings ) {

		$default = '';
		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['fluentcrm_double_optin'] ) ) ? $settings['default']['fluentcrm_double_optin'] : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'fluentcrm_double_optin',
			array(
				'class'   => '',
				'type'    => 'checkbox',
				'label'   => __( 'Enable Double Opt-in', 'convertpro-addon' ),
				'help'    => '',
				'default' => $default,
			)
		);

		return ob_get_clean();
	}

	/**
	 * Mapping fields.
	 *
	 * @since 1.5.0
	 */
	public function render_mapping() {
		return self::$mapping_fields;
	}

	/**
	 * Subscribe an email address to FluentCRM.
	 *
	 * @since 1.5.0
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @param array  $dynamic_tags get the dynamic tags via checkboxes.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $settings, $email, $dynamic_tags ) {

		$response = array(
			'error' => false,
		);

		if ( self::$check_fluentcrm ) {

			$response['error'] = __( 'Error: FluentCRM connects addon requires FluentCRM plugin installed and activated.', 'convertpro-addon' );
			return $response;
		}

		$list_id = $settings['fluentcrm_list'];

		if ( ! $list_id ) {
			$response['error'] = __( 'There was an error subscribing to FluentCRM! The account is no longer connected.', 'convertpro-addon' );
		} else {
			$response = array(
				'error' => false,
			);
			try {
				$double_optin = ( isset( $settings['fluentcrm_double_optin'] ) ) ? true : false;
				$lead_data    = array(
					'email'  => $email,
					'status' => ! $double_optin ? 'subscribed' : 'pending',
					'lists'  => array( $settings['fluentcrm_list'] ),
				);

				foreach ( $settings['param'] as $key => $p ) {

					if ( 'email' !== $key && 'date' !== $key && isset( $settings['meta'][ $key ] ) ) {
						if ( 'custom_field' !== $settings['meta'][ $key ] ) {
							$lead_data[ $settings['meta'][ $key ] ] = $p;
						} else {
							$lead_data['custom_values'][ $settings['meta'][ $key . '-input' ] ] = $p;
						}
					}
				}

				/**
				* Dynamic Tags support from the Checkboxes selection.
				* As FluentCRM tags are accepted as array format with tags ID.
				* So $dynamic_tags variable will receive in array and convert tags name into associated tags ID,
				* to accept in the FluentCRM tags format.
				*/
				$lead_data['tags'] = array();
				if ( ! empty( $dynamic_tags ) ) {

					$get_all_tags = FluentCrmApi( 'tags' )->all();

					if ( is_object( $get_all_tags ) ) {
						foreach ( $get_all_tags as $tag ) {

							if ( isset( $tag->title ) && in_array( $tag->title, $dynamic_tags, true ) ) {
								$lead_data['tags'][] = (int) $tag->id;
							}
						}
					}
				}
				if ( isset( $settings['fluentcrm_tags'] ) && ! empty( $settings['fluentcrm_tags'] ) ) {
					$settings['fluentcrm_tags'] = array_map(
						function( $value ) {
							return intval( $value );
						},
						$settings['fluentcrm_tags']
					);
					$lead_data['tags']          = array_merge( $settings['fluentcrm_tags'], $lead_data['tags'] );
				}

				$contact_api = FluentCrmApi( 'contacts' );
				$result      = $contact_api->createOrUpdate( $lead_data );

				if ( 'pending' === $result->status ) {
					$result->sendDoubleOptinEmail();
				}
			} catch ( Exception $e ) {
				$response['error'] = sprintf(
					/* translators: %s Error Message */
					__( 'There was an error subscribing to FluentCRM! %s', 'convertpro-addon' ),
					$e->getMessage()
				);
			}
		}
		return $response;
	}
}
