<?php
/**
 * Geolocation class
 *
 * Handles geolocation and updating the geolocation database.
 *
 * This product includes GeoLite data created by MaxMind, available from http://www.maxmind.com.
 *
 * @package Convert Pro/Classes
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * CP_Geolocation Class.
 */
class CP_Geolocation {

	/**
	 * GeoLite IPv4 DB.
	 *
	 * @deprecated 3.4.0
	 */
	const GEOLITE_DB = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz';

	/**
	 * GeoLite IPv6 DB.
	 *
	 * @deprecated 3.4.0
	 */
	const GEOLITE_IPV6_DB = 'http://geolite.maxmind.com/download/geoip/database/GeoIPv6.dat.gz';

	/**
	 * GeoLite2 DB.
	 *
	 * @since 3.4.0
	 */
	const GEOLITE2_DB = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz';

	/**
	 * API endpoints for looking up user IP address.
	 *
	 * @var array
	 */
	private static $ip_lookup_apis = array(
		'icanhazip'         => 'http://icanhazip.com',
		'ipify'             => 'http://api.ipify.org/',
		'ipecho'            => 'http://ipecho.net/plain',
		'ident'             => 'http://ident.me',
		'whatismyipaddress' => 'http://bot.whatismyipaddress.com',
	);

	/**
	 * API endpoints for geolocating an IP address
	 *
	 * @var array
	 */
	private static $geoip_apis = array(
		'ipinfo.io'  => 'https://ipinfo.io/%s/json',
		'ip-api.com' => 'http://ip-api.com/json/%s',
	);

	/**
	 * Check if server supports MaxMind GeoLite2 Reader.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	private static function supports_geolite2() {
		return version_compare( PHP_VERSION, '5.4.0', '>=' );
	}

	/**
	 * Hook in geolocation functionality.
	 */
	public static function init() {
		if ( self::supports_geolite2() ) {

			// Check Geolite2 option present and license key present or not.
			self::get_geolite2_option();
		}
	}

	/**
	 * Get current user IP Address.
	 *
	 * @return string
	 */
	public static function get_ip_address() {
		$get_ip_address = '';
		if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) { // WPCS: input var ok, CSRF ok.
			$get_ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) ); // WPCS: input var ok, CSRF ok.
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { // WPCS: input var ok, CSRF ok.
			// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
			// Make sure we always only send through the first IP in the list which should always be the client IP.
			$get_ip_address = (string) rest_is_ip_address( trim( current( preg_split( '/[,:]/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) ) ); // WPCS: input var ok, CSRF ok.
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$get_ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return $get_ip_address;
	}

	/**
	 * Get user IP Address using an external service.
	 * This is used mainly as a fallback for users on localhost where
	 * get_ip_address() will be a local IP and non-geolocatable.
	 *
	 * @return string
	 */
	public static function get_external_ip_address() {
		$external_ip_address = '0.0.0.0';

		if ( '' !== self::get_ip_address() ) {
			$transient_name      = 'external_ip_address_' . self::get_ip_address();
			$external_ip_address = get_transient( $transient_name );
		}

		if ( false === $external_ip_address ) {
			$external_ip_address     = '0.0.0.0';
			$ip_lookup_services      = apply_filters( 'convert_pro_geolocation_ip_lookup_apis', self::$ip_lookup_apis );
			$ip_lookup_services_keys = array_keys( $ip_lookup_services );
			shuffle( $ip_lookup_services_keys );

			foreach ( $ip_lookup_services_keys as $service_name ) {
				$service_endpoint = $ip_lookup_services[ $service_name ];
				$response         = wp_safe_remote_get( $service_endpoint, array( 'timeout' => 2 ) );

				if ( ! is_wp_error( $response ) && rest_is_ip_address( $response['body'] ) ) {
					$external_ip_address = apply_filters( 'convert_pro_geolocation_ip_lookup_api_response', sanitize_text_field( $response['body'] ), $service_name );
					break;
				}
			}

			set_transient( $transient_name, $external_ip_address, WEEK_IN_SECONDS );
		}

		return $external_ip_address;
	}

	/**
	 * Geolocate an IP address.
	 *
	 * @param  string $ip_address   IP Address.
	 * @param  bool   $fallback     If true, fallbacks to alternative IP detection (can be slower).
	 * @param  bool   $api_fallback If true, uses geolocation APIs if the database file doesn't exist (can be slower).
	 * @return array
	 */
	public static function geolocate_ip( $ip_address = '', $fallback = true, $api_fallback = true ) {
		// Filter to allow custom geolocation of the IP address.
		$country_code = apply_filters( 'convert_pro_geolocate_ip', false, $ip_address, $fallback, $api_fallback );
		if ( false === $country_code ) {
			// If GEOIP is enabled in CloudFlare, we can use that (Settings -> CloudFlare Settings -> Settings Overview).
			if ( ! empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) { // WPCS: input var ok, CSRF ok.
				$country_code = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) ); // WPCS: input var ok, CSRF ok.
			} elseif ( ! empty( $_SERVER['GEOIP_COUNTRY_CODE'] ) ) { // WPCS: input var ok, CSRF ok.
				// WP.com VIP has a variable available.
				$country_code = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['GEOIP_COUNTRY_CODE'] ) ) ); // WPCS: input var ok, CSRF ok.
			} elseif ( ! empty( $_SERVER['HTTP_X_COUNTRY_CODE'] ) ) { // WPCS: input var ok, CSRF ok.
				// VIP Go has a variable available also.
				$country_code = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_COUNTRY_CODE'] ) ) ); // WPCS: input var ok, CSRF ok.
			} else {

				$ip_address = $ip_address ? $ip_address : self::get_ip_address();
				$database   = self::get_local_database_path();

				if ( self::supports_geolite2() && file_exists( $database ) ) {
					$country_code = self::geolocate_via_db( $ip_address, $database );
				} elseif ( $api_fallback ) {
					$country_code = self::geolocate_via_api( $ip_address );
				} else {
					$country_code = '';
				}

				if ( ! $country_code && $fallback ) {
					// May be a local environment - find external IP.
					return self::geolocate_ip( self::get_external_ip_address(), false, $api_fallback );
				}
			}
		}

		return array(
			'country' => $country_code,
			'state'   => '',
		);
	}

	/**
	 * Path to our local db.
	 *
	 * @deprecated 3.9.0
	 * @return string
	 */
	public static function get_local_database_path() {

		$uploads_dir = wp_upload_dir();

		$database_path   = trailingslashit( $uploads_dir['basedir'] ) . 'convertpro_uploads/';
		$database_prefix = ( is_multisite() ) ? get_site_option( '_convertpro_maxmind_geolocation_settings' ) : get_option( 'convertpro_maxmind_geolocation_settings' );
		$database_prefix = $database_prefix['database_prefix'];

		if ( ! empty( $database_prefix ) ) {
			$database_path .= $database_prefix . '-';
		}
		$database_path .= 'GeoLite2-Country.mmdb';

		/**
		 * Filter the geolocation database storage path.
		 *
		 * @param string $database_path The path to the database.
		 * @param int $version Deprecated since 3.4.0.
		 * @deprecated 3.9.0
		 */
		$database_path = apply_filters_deprecated(
			'convert_pro_geolocation_local_database_path',
			array( $database_path, 2 ),
			'3.9.0',
			'convert_pro_maxmind_geolocation_database_path'
		);

		/**
		 * Filter the geolocation database storage path.
		 *
		 * @since 3.9.0
		 * @param string $database_path The path to the database.
		 */
		return apply_filters( 'convert_pro_maxmind_geolocation_database_path', $database_path );
	}


	/**
	 * Get the Geolite2 option and license key.
	 *
	 * @since 3.9.0
	 */
	public static function get_geolite2_option() {
		$cpro_maxmind_option = ( is_multisite() ) ? get_site_option( '_convertpro_maxmind_geolocation_settings' ) : get_option( 'convertpro_maxmind_geolocation_settings' );
		if ( false === $cpro_maxmind_option || '' === $cpro_maxmind_option['license_key'] ) {
			error_log( 'The MaxMind Geolocation Integration - License key is not activated in Convert Pro. Find Settings under Convert Pro->Settings MaxMind Geolocation' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	/**
	 * Use MAXMIND GeoLite database to geolocation the user.
	 *
	 * @param  string $ip_address IP address.
	 * @param  string $database   Database path.
	 * @return string
	 */
	private static function geolocate_via_db( $ip_address, $database ) {
		if ( ! class_exists( 'CP_Geolite_Integration', false ) ) {
			require_once CP_V2_BASE_DIR . 'includes/class-cp-geolite-integration.php';
		}

		$geolite = new CP_Geolite_Integration( $database );

		return $geolite->get_country_iso( $ip_address );
	}


	/**
	 * Use APIs to Geolocate the user.
	 *
	 * Geolocation APIs can be added through the use of the convert_pro_geolocation_geoip_apis filter.
	 * Provide a name=>value pair for service-slug=>endpoint.
	 *
	 * If APIs are defined, one will be chosen at random to fulfil the request. After completing, the result
	 * will be cached in a transient.
	 *
	 * @param  string $ip_address IP address.
	 * @return string
	 */
	private static function geolocate_via_api( $ip_address ) {
		$country_code = get_transient( 'geoip_' . $ip_address );

		if ( false === $country_code ) {
			$geoip_services = apply_filters( 'convert_pro_geolocation_geoip_apis', self::$geoip_apis );

			if ( empty( $geoip_services ) ) {
				return '';
			}

			$geoip_services_keys = array_keys( $geoip_services );

			shuffle( $geoip_services_keys );

			foreach ( $geoip_services_keys as $service_name ) {
				$service_endpoint = $geoip_services[ $service_name ];
				$response         = wp_safe_remote_get( sprintf( $service_endpoint, $ip_address ), array( 'timeout' => 2 ) );

				if ( ! is_wp_error( $response ) && $response['body'] ) {
					switch ( $service_name ) {
						case 'ipinfo.io':
							$data         = json_decode( $response['body'] );
							$country_code = isset( $data->country ) ? $data->country : '';
							break;
						case 'ip-api.com':
							$data         = json_decode( $response['body'] );
							$country_code = isset( $data->countryCode ) ? $data->countryCode : ''; // @codingStandardsIgnoreLine
							break;
						default:
							$country_code = apply_filters( 'convert_pro_geolocation_geoip_response_' . $service_name, '', $response['body'] );
							break;
					}

					$country_code = sanitize_text_field( strtoupper( $country_code ) );

					if ( $country_code ) {
						break;
					}
				}
			}

			set_transient( 'geoip_' . $ip_address, $country_code, WEEK_IN_SECONDS );
		}

		return $country_code;
	}
}

CP_Geolocation::init();
