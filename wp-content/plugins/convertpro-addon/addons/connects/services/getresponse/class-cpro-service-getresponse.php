<?php
/**
 * Collects leads and subscribe to GetResponse
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the GetResponse API.
 *
 * @since 1.0.0
 */
final class CPRO_Service_GetResponse extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public $id = 'getresponse';

	/**
	 * Default Custom field array.
	 * This is predefined custom fields array that Mailchimp
	 * has already defined. When Mailchimp releases the new
	 * set of fields, we need to update this array.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public static $mapping_fields = array( 'Name', 'age', 'birthdate', 'city', 'comment', 'company', 'country', 'fax', 'gender', 'home_phone', 'http_referer', 'mobile_phone', 'phone', 'postal_code', 'ref', 'state', 'street', 'url', 'work_phone' );

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
	 * @return object The API instance.
	 */
	public function get_api( $api_key ) {
		if ( $this->api_instance ) {
			return $this->api_instance;
		}

		if ( ! class_exists( 'CPRO_GetResponse' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/getresponse/getresponse.php';
		}

		$this->api_instance = new CPRO_GetResponse( $api_key );

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
			return $response;
		}

		if ( ! isset( $fields['is_account_type'] ) || '-1' === $fields['is_account_type'] ) {

			$response['error'] = __( 'Error: You must select GetResponse Account type', 'convertpro-addon' );
			return $response;

		} elseif ( ! isset( $fields['is_account_type'] ) || 'gr_com' === $fields['is_account_type'] || 'gr_pl' === $fields['is_account_type'] ) {

			if ( ! isset( $fields['getresponse_domain_name'] ) || empty( $fields['getresponse_domain_name'] ) ) {

				$response['error'] = __( 'Error: You must provide a GetResponse Domain Name', 'convertpro-addon' );
				return $response;

			}
		} else {
			// Try to connect and store the connection data.
			$api = $this->get_api( $fields['api_key'] );

			if ( 'gr_com' === $fields['is_account_type'] ) {

				$api->api_url           = 'https://api3.getresponse360.com/v3';
				$api->enterprise_domain = $fields['getresponse_domain_name'];

			} elseif ( 'gr_pl' === $fields['is_account_type'] ) {

				$api->api_url           = 'https://api3.getresponse360.pl/v3';
				$api->enterprise_domain = $fields['getresponse_domain_name'];

			}
			$ping = $api->ping();

			if ( ! $ping ) {
				$response['error'] = __( 'Oops! You\'ve entered the wrong API Key. Please enter the API key and try again.', 'convertpro-addon' );
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

		$getresponse_options_array = array(
			'-1'        => __( 'Select Account Type', 'convertpro-addon' ),
			'gr_simple' => __( 'Simple', 'convertpro-addon' ),
			'gr_com'    => __( 'GetResponse Enterprise COM', 'convertpro-addon' ),
			'gr_pl'     => __( 'GetResponse Enterprise PL', 'convertpro-addon' ),
		);

		ConvertPlugHelper::render_input_html(
			'is_account_type',
			array(
				'class'   => 'getresponse_is_account_type',
				'type'    => 'select',
				'label'   => '',
				'default' => '-1',
				'help'    => __( 'List', 'convertpro-addon' ),
				'options' => $getresponse_options_array,
			)
		);

		ConvertPlugHelper::render_input_html(
			'api_key',
			array(
				'class' => '',
				'type'  => 'text',
				'label' => __( 'API Key', 'convertpro-addon' ),
				'help'  => __( 'Your GetResponse API key can be found in your GetResponse My Account section. > GetResponse API.', 'convertpro-addon' ),
			)
		);

		ConvertPlugHelper::render_input_html(
			'getresponse_domain_name',
			array(
				'class' => 'getresponse_domain_name',
				'type'  => 'text',
				'label' => __( 'GetResponse Domain Name', 'convertpro-addon' ),
				'help'  => __( 'Your GetResponse Domain Name can be found in your GetResponse Manage Account section. > Domains.', 'convertpro-addon' ),
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
			'is_account_type'         => $auth_meta['is_account_type'],
			'api_key'                 => $auth_meta['api_key'],
			'getresponse_domain_name' => $auth_meta['getresponse_domain_name'],
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
	 *      @type array $mapping_fields The field mapping array for mailchimp.
	 * }
	 */
	public function render_fields( $account, $post_data ) {
		$account_data = ConvertPlugServices::get_account_data( $account );
		$api          = $this->get_api( $account_data['api_key'] );

		if ( 'gr_com' === $account_data['is_account_type'] ) {

			$api->api_url           = 'https://api3.getresponse360.com/v3';
			$api->enterprise_domain = $account_data['getresponse_domain_name'];

		} elseif ( 'gr_pl' === $account_data['is_account_type'] ) {

			$api->api_url           = 'https://api3.getresponse360.pl/v3';
			$api->enterprise_domain = $account_data['getresponse_domain_name'];

		}

		$lists    = $api->getCampaigns();
		$tags     = (array) $api->get_tags( array( 'fields' => 'name' ) );
		$response = array(
			'error'          => false,
			'html'           => '',
			'mapping_fields' => self::$mapping_fields,
		);

		if ( ! $lists ) {
			$response['error'] = __( 'Oops! You\'ve entered the wrong API Key. Please enter the API key and try again.', 'convertpro-addon' );
		} else {
			$response['html'] = $this->render_list_field( $lists, $post_data );
			if ( ! empty( $tags ) ) {
				$response['html'] .= $this->render_tags_field( $tags, $post_data );
			}
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
		ob_start();

		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);
		$default = '';

		foreach ( $lists as $id => $data ) {
			// phpcs:ignore
			$options[ $data->campaignId ] = $data->name;
		}

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) ) ? ( ( isset( $settings['default']['getresponse_list'] ) ) ? $settings['default']['getresponse_list'] : '' ) : '';
		}

		ConvertPlugHelper::render_input_html(
			'getresponse_list',
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
	 * @param array  $tags An array of segment data.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the tags field.
	 * @access private
	 */
	private function render_tags_field( $tags, $settings ) {

		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);
		$default = '';

		foreach ( $tags as $tag ) {
			if ( is_object( $tag ) ) {
				// tagId is the index name which is returned from the API call.
				$options[ $tag->tagId ] = $tag->name; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			}
		}

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default']['getresponse_tags'] ) ) ? $settings['default']['getresponse_tags'] : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'getresponse_tags',
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
	 * Mapping fields.
	 *
	 * @since 1.0.0
	 */
	public function render_mapping() {
		return self::$mapping_fields;
	}

	/**
	 * Subscribe an email address to MailChimp.
	 *
	 * @since 1.0.0
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @param array  $dynamic_tags get the dynamic tags via checkboxes.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 * @throws Exception Error Message.
	 */
	public function subscribe( $settings, $email, $dynamic_tags ) {
		$account_data = ConvertPlugServices::get_account_data( $settings['api_connection'] );
		$response     = array(
			'error' => false,
		);

		if ( ! $account_data ) {
			$response['error'] = __( 'There was an error subscribing to GetResponse! The account is no longer connected.', 'convertpro-addon' );
		} else {

			$api = $this->get_api( $account_data['api_key'] );

			try {

				if ( 'gr_com' === $account_data['is_account_type'] ) {

					$api->api_url           = 'https://api3.getresponse360.com/v3';
					$api->enterprise_domain = $account_data['getresponse_domain_name'];

				} elseif ( 'gr_pl' === $account_data['is_account_type'] ) {

					$api->api_url           = 'https://api3.getresponse360.pl/v3';
					$api->enterprise_domain = $account_data['getresponse_domain_name'];

				}

				$name          = '';
				$custom        = array();
				$custom_fields = array();
				$exist_result  = array();
				$param_array   = array();
				$tags_arr      = array();
				$exist_user    = false;
				$exist_id      = '';

				$exist_field = $api->getCustomFields( array( 'fields' => 'customFieldId,name' ) );

				if ( isset( $exist_field ) ) {
					$cust_array = json_decode( wp_json_encode( $exist_field ), true );

					foreach ( $cust_array as $key => $cvalue ) {
						foreach ( $cvalue as $key1 => $value ) {
							$c_name                   = $cvalue['name'];
							$c_id                     = $cvalue['customFieldId'];
							$custom_fields[ $c_name ] = $c_id;
						}
					}
				}

				foreach ( $settings['param'] as $key => $p ) {
					if ( 'email' !== $key && 'date' !== $key ) {
						if ( 'Name' === $settings['meta'][ $key ] ) {
							$param_array['name'] = $p;
						} elseif ( 'custom_field' !== $settings['meta'][ $key ] ) {
							if ( array_key_exists( $settings['meta'][ $key ], $custom_fields ) ) {
								$custom[] = array(
									'customFieldId' => $custom_fields[ $settings['meta'][ $key ] ],
									'value'         => array( $p ),
								);
							}
						} else {
							if ( array_key_exists( $settings['meta'][ $key . '-input' ], $custom_fields ) ) {
								$custom[] = array(
									'customFieldId' => $custom_fields[ $settings['meta'][ $key . '-input' ] ],
									'value'         => array( $p ),
								);
							}
						}
					}
				}
				/**
				 * Dynamic Tags support from the Checkboxes selection.
				 * As FluentCRM tags are accepted as array format with tags ID.
				 * So $dynamic_tags variable will receive in array and convert tags name into associated tags ID,
				 * to accept in the FluentCRM tags format.
				 */
				if ( ! empty( $dynamic_tags ) ) {

					$tags = (array) $api->get_tags( array( 'fields' => 'name' ) );
					foreach ( $tags as $tag ) {
						if ( is_object( $tag ) && in_array( $tag->name, $dynamic_tags, true ) ) {
							// tagId is the index name which is returned from the API call.
							$tags_arr[] = array( 'tagId' => $tag->tagId ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						}
					}
				}

				if ( isset( $settings['getresponse_tags'] ) && ! empty( $settings['getresponse_tags'] ) ) {
					foreach ( $settings['getresponse_tags'] as $g ) {
						$tags_arr[] = array( 'tagId' => $g );
					}
				}

				$param_array['email']             = $email;
				$param_array['dayOfCycle']        = 0;
				$param_array['campaign']          = array( 'campaignId' => $settings['getresponse_list'] );
				$param_array['customFieldValues'] = $custom;
				$param_array['ipAddress']         = $_SERVER['REMOTE_ADDR'];
				$param_array['tags']              = $tags_arr;

				$exist_result = $api->getContacts(
					array(
						'query'  => array(
							'email' => $email,
						),
						'fields' => 'name,email,campaign',
					)
				);

				if ( isset( $exist_result ) ) {
					$contact_array = json_decode( wp_json_encode( $exist_result ), true );

					foreach ( $contact_array as $key => $value ) {
						$contact_id = isset( $contact_array[0]['contactId'] ) ? $contact_array[0]['contactId'] : '';
						$camp_id    = isset( $contact_array[0]['campaign'] ) ? $contact_array[0]['campaign'] : '';
						$exist_camp = $camp_id['campaignId'];

						if ( $exist_camp === $settings['getresponse_list'] ) {
							$exist_user = true;
							$exist_id   = $contact_id;
						}
					}
				}

				if ( true === $exist_user ) {
					unset( $param_array['email'] );
					$result = $api->updateContact( $exist_id, $param_array );
				} else {
					$result = $api->addContact( $param_array );
				}

				if ( isset( $result->httpStatus ) && 400 === $result->httpStatus ) { // PHPCS:ignore:WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					throw new Exception( $result->context[0] );
				}
			} catch ( Exception $e ) {
				$response['error'] = sprintf(
					/* translators: %s Error Message */
					__( 'There was an error subscribing to GetResponse. %s', 'convertpro-addon' ),
					$e->getMessage()
				);
			}
		}

		return $response;
	}
}
