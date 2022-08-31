<?php
/**
 * CP_V2_GA.
 *
 * @package Convert Pro
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'CP_V2_GA' ) ) {

	/**
	 * Class CP_V2_GA.
	 */
	class CP_V2_GA {

		/**
		 * View actions
		 *
		 * @var view_actions
		 */
		private $ga_instance;

		/**
		 * AccessToken
		 *
		 * @var access_token
		 */
		private $access_token = '';

		/**
		 * Client id
		 *
		 * @var client_id
		 */
		private $client_id = '853470414267-rqdo6lto825h04j6kmlvbqhcih4qpqvb.apps.googleusercontent.com';

		/**
		 * Client secret
		 *
		 * @var client_secret
		 */
		private $client_secret = 'MKWqvrC7jMo3-nsiC7qVbht5';

		/**
		 * Redirect URI
		 *
		 * @var redirect_uri
		 */
		private $redirect_uri = 'urn:ietf:wg:oauth:2.0:oob';

		/**
		 * Constructor
		 */
		public function __construct() {

			$analytics_lib = CP_ADDON_DIR . 'addons/analytics/lib/class-googleanalyticsapi.php';

			if ( file_exists( $analytics_lib ) ) {
				require_once $analytics_lib;
			}

			if ( class_exists( 'GoogleAnalyticsAPI' ) ) {

				add_action( 'wp_ajax_cp_get_ga_token_details', array( $this, 'cp_get_ga_token_details' ) );
				add_action( 'wp_ajax_cp_resync_ga_data', array( $this, 'resync_ga_data' ) );
				add_action( 'wp_ajax_cp_update_ga_access_code', array( $this, 'cp_update_ga_access_code' ) );
				add_action( 'wp_ajax_cp_get_ga_data', array( $this, 'cp_get_ga_data' ) );
				add_action( 'wp_ajax_cp_delete_ga_integration', array( $this, 'cp_delete_ga_integration' ) );

				add_action( 'wp_ajax_cp_save_ga_details', array( $this, 'cp_save_ga_details' ) );

				$this->ga_instance = new GoogleAnalyticsAPI();
				$this->refresh_access_token();
			}
		}

		/**
		 * Function Name: cp_save_ga_details.
		 * Function Description: Save google analytics credentials
		 */
		public function cp_save_ga_details() {

			check_ajax_referer( 'cp_save_ga_details', 'security' );

			$profile  = isset( $_POST['profile'] ) ? esc_attr( $_POST['profile'] ) : '';
			$timezone = isset( $_POST['timezone'] ) ? esc_attr( $_POST['timezone'] ) : '';

			if ( '' === $profile ) {
				wp_send_json_error();
			}

			$credentials = get_option( '_cp_ga_credentials' );

			$credentials['profile']  = $profile;
			$credentials['timezone'] = $timezone;

			update_option( 'cp_ga_credentials', $credentials );

			update_option( 'cp_ga_analytics_updated_on', gmdate( 'Y-m-d H:i:s' ) );

			$this->refresh_access_token();

			$analytics_data = $this->get_analytics_data();

			$this->cp_map_details( $analytics_data );

			wp_send_json_success();

		}

		/**
		 * Function Name: cp_get_ga_data.
		 * Function Description: cp get ga data.
		 */
		public function cp_get_ga_data() {

			check_ajax_referer( 'cp_get_ga_data_chart', 'security' );

			if ( ! current_user_can( 'access_cp_pro' ) ) {
				die( '-1' );
			}

			$style_slug = isset( $_POST['style_id'] ) ? sanitize_text_field( $_POST['style_id'] ) : '';
			$sdate      = isset( $_POST['sdate'] ) ? sanitize_text_field( $_POST['sdate'] ) : '';
			$edate      = isset( $_POST['edate'] ) ? sanitize_text_field( $_POST['edate'] ) : '';

			if ( '' === $style_slug ) {
				wp_send_json_error();
			}

			$data = get_option( 'cp_ga_analytics_data' );

			$credentials = get_option( 'cp_ga_credentials' );
			$timezone    = isset( $credentials['timezone'] ) ? $credentials['timezone'] : '';

			if ( '' !== $timezone ) {
				date_default_timezone_set( $timezone ); //phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
			}

			$analytics_data = array();

			$new_start_date    = strtotime( $sdate );
			$new_end_date      = strtotime( $edate );
			$popups_impression = 0;
			$popups_conversion = 0;
			$defaults          = array();

			// If Today and Yesterday option selected.
			if ( $sdate === $edate ) {
				$defaults[0] = $sdate;
				$defaults[1] = 0;
				$defaults[2] = 0;
				if ( isset( $data[ $style_slug ] ) ) {
					if ( isset( $data[ $style_slug ][ $sdate ] ) ) {
						$defaults[1] = $data[ $style_slug ][ $sdate ]['impressions'];
						$defaults[2] = $data[ $style_slug ][ $sdate ]['conversions'];

						// Total popups impressions and conversions count.
						$popups_impression = $defaults[1];
						$popups_conversion = $defaults[2];
					}
				}

				$analytics_data[] = $defaults;
			} else {
				// Other than the Today and Yesterday option selected.
				if ( isset( $data[ $style_slug ] ) ) {

					while ( $new_start_date <= $new_end_date ) {

						$recurring_date = gmdate( 'Y-m-d', $new_start_date );
						if ( isset( $data[ $style_slug ][ $recurring_date ] ) ) {
							$defaults[0] = $recurring_date;
							$defaults[1] = $data[ $style_slug ][ $recurring_date ]['impressions'];
							$defaults[2] = $data[ $style_slug ][ $recurring_date ]['conversions'];

							// Total popups impressions and conversions count.
							$popups_impression += $defaults[1];
							$popups_conversion += $defaults[2];

							$analytics_data[] = $defaults;
						}
						$new_start_date = ( $new_start_date + ( 86400 ) );
					}
				}
			}

			$final_analytics_data = array(
				'analytics_count' => array(
					'impressions' => $popups_impression,
					'conversions' => $popups_conversion,
				),
				'analytics_data'  => $analytics_data,
			);

			wp_send_json( $final_analytics_data );

		}

		/**
		 * Function Name: cp_delete_ga_integration.
		 * Function Description: cp delete ga integration.
		 */
		public function cp_delete_ga_integration() {

			check_ajax_referer( 'cp_delete_ga_integration', 'security' );

			if ( ! current_user_can( 'access_cp_pro' ) ) {
				die( '-1' );
			}

			delete_option( 'cp_ga_credentials' );
			delete_option( 'cp_ga_analytics_data' );
			delete_option( 'cp_ga_analytics_updated_on' );

			wp_send_json_success();
		}

		/**
		 * Function Name: cp_update_ga_access_code.
		 * Function Description: cp update ga access code.
		 */
		public function cp_update_ga_access_code() {

			if ( ! current_user_can( 'access_cp_pro' ) ) {
				die( '-1' );
			}

			check_admin_referer( 'cp-auth-ga-access-action', 'cp_security_nonce' );

			$access_code = isset( $_POST['access_code'] ) ? sanitize_text_field( $_POST['access_code'] ) : '';

			if ( '' !== $access_code ) {
				update_option( 'cp_ga_access_code', $access_code );
				wp_send_json_success();
			} else {
				wp_send_json_error();
			}
		}

		/**
		 * Function Name: refresh_access_token.
		 * Function Description: refresh access token.
		 */
		public function refresh_access_token() {

			$credentials = get_option( 'cp_ga_credentials' );

			if ( is_array( $credentials ) && ! empty( $credentials ) && null !== $credentials['access_token'] ) {

				// Check if the accessToken is expired.
				if ( ( time() - $credentials['token_created'] ) >= $credentials['token_expires'] ) {
					$this->update_access_token( $credentials );
				}
			} else {

				if ( is_array( $credentials ) && isset( $credentials['refresh_token'] ) ) {
					$this->update_access_token( $credentials );
				}
			}

			$new_credentials = get_option( 'cp_ga_credentials' );
			if ( is_array( $new_credentials ) && isset( $new_credentials['access_token'] ) ) {
				$this->access_token = $new_credentials['access_token'];
			}

		}

		/**
		 * Update access token in option
		 *
		 * @param array $credentials All related credentail details.
		 * @since 1.0.0
		 */
		public function update_access_token( $credentials ) {

			$this->ga_instance->auth->set_client_id( $this->client_id ); // From the APIs console.
			$this->ga_instance->auth->setClientSecret( $this->client_secret ); // From the APIs console.
			$this->ga_instance->auth->setRedirectUri( $this->redirect_uri );

			$auth = $this->ga_instance->auth->refreshAccessToken( $credentials['refresh_token'] );

			$new_credentials = array(
				'access_token'  => $auth['access_token'],
				'refresh_token' => $credentials['refresh_token'],
				'token_expires' => $auth['expires_in'],
				'token_created' => time(),
			);

			if ( isset( $credentials['profile'] ) ) {
				$new_credentials['profile'] = $credentials['profile'];
			}

			update_option( 'cp_ga_credentials', $new_credentials );

		}

		/**
		 * Get google analytics accounts
		 *
		 * @since 1.0.0
		 */
		public function get_ga_accounts() {

			$accounts = array();
			if ( '' !== $this->access_token ) {

				$this->ga_instance->set_access_token( $this->access_token );

				// Load profiles.
				$profiles = $this->ga_instance->get_profiles();

				if ( isset( $profiles['username'] ) ) {
					update_option( '_cpro_ga_profile', $profiles['username'] );
				}
				$curr_domain = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
				$curr_domain = str_replace( 'www.', '', $curr_domain );

				if ( is_array( $profiles ) && ! empty( $profiles ) && isset( $profiles['items'] ) ) {
					foreach ( $profiles['items'] as $item ) {

						if ( stripos( untrailingslashit( $item['websiteUrl'] ), $curr_domain ) !== false ) {

							$id          = "ga:{$item['id']}";
							$name        = $item['name'];
							$web_prop_id = $item['webPropertyId'];
							$timezone    = $item['timezone'];

							$account_info = array(
								'name'        => $name,
								'web_prop_id' => $web_prop_id,
								'timezone'    => $timezone,
							);

							$accounts[ $id ] = $account_info;
						}
					}
				}
			}

			return $accounts;
		}

		/**
		 * Function Name: get_analytics_data.
		 * Function Description: Get google analytics data.
		 *
		 * @param string $style_id string parameter.
		 */
		public function get_analytics_data( $style_id = '' ) {

			$visits      = array();
			$credentials = get_option( 'cp_ga_credentials' );

			$account_id = isset( $credentials['profile'] ) ? $credentials['profile'] : '';

			if ( '' === $account_id ) {
				$accounts   = $this->get_ga_accounts();
				$account_id = array_keys( $accounts );
				$account_id = $account_id[0];
			}

			$this->ga_instance->auth->set_client_id( $this->client_id ); // From the APIs console.
			$this->ga_instance->auth->setClientSecret( $this->client_secret ); // From the APIs console.
			$this->ga_instance->auth->setRedirectUri( $this->redirect_uri );
			$this->ga_instance->set_account_id( $account_id );
			$this->ga_instance->set_access_token( $credentials['access_token'] );

			$query_args = array(
				'post_type'      => CP_CUSTOM_POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			);

			$popups = new WP_Query( $query_args );
			wp_reset_postdata();

			if ( $popups->post_count > 0 ) {

				if ( '' === $style_id ) {

					$popup_slugs = array();

					foreach ( $popups->posts as $popup ) {

						$style_slug    = $popup->post_name;
						$popup_slugs[] = 'ga:eventLabel==' . $style_slug;
					}

					$filter = 'ga:eventCategory==' . CPRO_BRANDING_NAME . ';' . implode( ',', $popup_slugs );

					$params = array(
						'metrics'    => 'ga:uniqueEvents',
						'dimensions' => 'ga:date,ga:eventAction,ga:eventLabel',
					);

				} else {

					$style_data = get_post( $style_id );
					$style_slug = $style_data->post_name;
					$filter     = 'ga:eventLabel==' . $style_slug . ';ga:eventCategory==' . CPRO_BRANDING_NAME;

					$params = array(
						'metrics'    => 'ga:totalEvents',
						'dimensions' => 'ga:date',
					);
				}

				$min_date = min(
					array_map(
						function( $item ) {
								return $item->post_date;
						},
						$popups->posts
					)
				);

				// Set the default params. For example the start/end dates and max-results.
				$defaults = array(
					'start-date'  => gmdate( 'Y-m-d', strtotime( $min_date ) ),
					'end-date'    => gmdate( 'Y-m-d' ),
					'filters'     => $filter,
					'max-results' => 10000,
				);

				$this->ga_instance->set_default_query_params( $defaults );

				$result = $this->ga_instance->query( $params );

				// If 400 error divide the popup slugs.
				if ( 400 === $result['http_code'] || null === $result || 10000 <= $result['totalResults'] ) {
					$result_rows      = array();
					$merge_result     = array();
					$count            = 17;
					$popup_slugs_temp = array_chunk( $popup_slugs, $count );

					foreach ( $popup_slugs_temp as $value ) {
						$filter                  = 'ga:eventCategory==' . CPRO_BRANDING_NAME . ';' . implode( ',', $value );
						$defaults['filters']     = $filter;
						$defaults['start-index'] = 1; // Default starting index 1 to max-result 10000.

						$this->ga_instance->set_default_query_params( $defaults );

						$result_rows = $this->ga_instance->query( $params );

						if ( isset( $result_rows['rows'] ) ) {
							$merge_result = array_merge( $merge_result, $result_rows['rows'] );
						}

						/**
						 * Get the paginated data for the popups analytics.
						 * i.e. starting index 10001 - 20000 and so on.
						*/
						if ( isset( $result_rows['nextLink'] ) ) {
							$paginator = (int) ( $result_rows['totalResults'] / 10000 );

							for ( $i = 1; $i <= $paginator; $i++ ) {
								$defaults['start-index'] = ( 10000 * $i ) + 1;
								$this->ga_instance->set_default_query_params( $defaults );

								$paginator_result_rows = $this->ga_instance->query( $params );
								if ( isset( $paginator_result_rows['rows'] ) ) {
									$merge_result = array_merge( $merge_result, $paginator_result_rows['rows'] );
								}
							}
						}
					}

					$visits = $merge_result;
				} else {
					if ( isset( $result['rows'] ) ) {
						$visits = $result['rows'];
					}
				}
			}

			return $visits;

		}

		/**
		 * Generate Outh2 URL
		 *
		 * @since 1.0.0
		 */
		public function generate_auth_url() {

			$this->ga_instance->auth->set_client_id( $this->client_id ); // From the APIs console.
			$this->ga_instance->auth->setClientSecret( $this->client_secret ); // From the APIs console.
			$this->ga_instance->auth->setRedirectUri( $this->redirect_uri ); // Url to your app, must match one in the APIs console.

			// Get the Auth-Url.
			return $this->ga_instance->auth->buildAuthUrl();

		}

		/**
		 * Function Name: cp_map_details.
		 * Function Description: cp map details.
		 *
		 * @param string $analytics_data string parameter.
		 */
		public function cp_map_details( $analytics_data ) {
			$ga_data = array();

			if ( is_array( $analytics_data ) && ! empty( $analytics_data ) ) {
				foreach ( $analytics_data as $value ) {

					$date       = gmdate( 'Y-m-d', strtotime( $value[0] ) );
					$action     = $value[1];
					$style_slug = $value[2];
					$impression = 0;
					$conversion = 0;

					if ( 'impression' === $action ) {
						$impression = (int) $value[3];
					} else {
						$conversion = (int) $value[3];
					}

					if ( isset( $ga_data[ $style_slug ] ) ) {

						if ( isset( $ga_data[ $style_slug ][ $date ] ) ) {

							$exist_impressions = isset( $ga_data[ $style_slug ][ $date ]['impressions'] ) ? $ga_data[ $style_slug ][ $date ]['impressions'] : 0;
							$exist_conversions = isset( $ga_data[ $style_slug ][ $date ]['conversions'] ) ? $ga_data[ $style_slug ][ $date ]['conversions'] : 0;

												$impressions = $impression + $exist_impressions;
							$conversions                     = $conversion + $exist_conversions;

												$data = array(
													'impressions' => $impressions,
													'conversions' => $conversions,
												);

												$ga_data[ $style_slug ][ $date ] = $data;

						} else {

							$data = array(
								'impressions' => $impression,
								'conversions' => $conversion,
							);

							$ga_data[ $style_slug ][ $date ] = $data;
						}
					} else {

						$ga_data[ $style_slug ] = array();
						$data                   = array(
							'impressions' => $impression,
							'conversions' => $conversion,
						);

						$ga_data[ $style_slug ][ $date ] = $data;
					}
				}
			}

			update_option( 'cp_ga_analytics_data', $ga_data );
		}

		/**
		 * Function Name: cp_get_ga_token_details.
		 * Function Description: cp get ga token details.
		 */
		public function cp_get_ga_token_details() {

			if ( ! current_user_can( 'access_cp_pro' ) ) {
				die( '-1' );
			}

			check_admin_referer( 'cp-auth-ga-access-action', 'cp_security_nonce' );

			$this->ga_instance->auth->set_client_id( $this->client_id ); // From the APIs console.
			$this->ga_instance->auth->setClientSecret( $this->client_secret ); // From the APIs console.
			$this->ga_instance->auth->setRedirectUri( $this->redirect_uri );

			$code = isset( $_POST['access_code'] ) ? sanitize_text_field( $_POST['access_code'] ) : '';

			$auth = $this->ga_instance->auth->get_access_token( $code );

			$auth_url = $this->generate_auth_url();

			// Try to get the AccessToken.
			if ( 200 === $auth['http_code'] ) {

				$credentials = array(
					'access_token'  => $auth['access_token'],
					'refresh_token' => $auth['refresh_token'],
					'token_expires' => $auth['expires_in'],
					'token_created' => time(),
				);

				$this->access_token = $credentials['access_token'];

				// save credentials in temporary option.
				update_option( '_cp_ga_credentials', $credentials );

				// Retrieve accounts for this particular site.
				$accounts = $this->get_ga_accounts();

				if ( is_array( $accounts ) && ! empty( $accounts ) ) {

					wp_send_json(
						array(
							'success'  => true,
							'accounts' => $accounts,
						)
					);

				} else {
					delete_option( 'cp_ga_analytics_updated_on' );
					delete_option( 'cp_ga_credentials' );
					delete_option( 'cp_ga_analytics_data' );
					delete_option( '_cpro_ga_profile' );
					$this->refresh_access_token();
					$ga_property_url = add_query_arg( array(), 'https://support.google.com/analytics/answer/1042508' );
					wp_send_json(
						array(
							'success' => false,
							/* translators: %s auth URL */
							'msg'     => sprintf( __( 'Please create a Google Analytics Property for this Domain. <a class="google-analytic-page-link" href="%s" target="_blank" rel="noopener">Know more.</a>', 'convertpro-addon' ), esc_url( $ga_property_url ) ),
						)
					);
				}
			} elseif ( 400 === $auth['http_code'] && isset( $auth['error_description'] ) ) {
				// error.
				if ( strpos( $auth['error_description'], 'redeemed' ) !== false ) {
					wp_send_json(
						array(
							'success' => false,
							/* translators: %s auth URL */
							'msg'     => sprintf( __( 'This access code was already redeemed. Please try generating a new code from <a href="%s" target="_blank" rel="noopener">here.</a>', 'convertpro-addon' ), esc_url( $auth_url ) ),
						)
					);
				} else {
					wp_send_json(
						array(
							'success' => false,
							'msg'     => $auth['error_description'],
						)
					);
				}
			} else {
				// error.
				wp_send_json(
					array(
						'success' => false,
						/* translators: %s auth URL */
						'msg'     => sprintf( __( 'The access code you entered is incorrect! Please <a class="google-analytic-page-link" target="_blank" rel="noopener" href="%s">click here</a> to get an access code.', 'convertpro-addon' ), esc_url( $auth_url ) ),
					)
				);
			}
		}

		/**
		 * Resync Google analytics data
		 *
		 * @since 1.0.0
		 */
		public function resync_ga_data() {

			check_ajax_referer( 'cp_update_analytics_data', 'security' );

			if ( ! current_user_can( 'access_cp_pro' ) ) {
				wp_send_json_error();
			}

			$analytics_data = $this->get_analytics_data();
			$this->cp_map_details( $analytics_data );
			update_option( 'cp_ga_analytics_updated_on', gmdate( 'Y-m-d H:i:s' ) );
			wp_send_json_success();

		}

		/**
		 * Resync Google analytics data
		 *
		 * @since 1.0.0
		 */
		public function resync_ga_data_cron() {

			if ( ! current_user_can( 'access_cp_pro' ) ) {
				return false;
			}

			$analytics_data = $this->get_analytics_data();
			$this->cp_map_details( $analytics_data );
			update_option( 'cp_ga_analytics_updated_on', gmdate( 'Y-m-d H:i:s' ) );
			return true;

		}
	}

	new CP_V2_GA();
}
