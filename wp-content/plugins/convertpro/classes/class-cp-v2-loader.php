<?php
/**
 * Cp_V2_Loader.
 *
 * @package ConvertPro
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'Cp_V2_Loader' ) ) {

	/**
	 * Class Cp_V2_Loader.
	 */
	final class Cp_V2_Loader {

		/**
		 * The unique instance of the plugin.
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Check WP_Block_Parser class exists.
		 *
		 * @var wp_block_parser_class_exists
		 */
		public static $wp_block_parser_class_exists;

		/**
		 * Gets an instance of our plugin.
		 */
		public static function get_instance() {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {

					// minimum requirement for PHP version.
			$php = '5.4';

			// If current version is less than minimum requirement, display admin notice.
			if ( version_compare( PHP_VERSION, $php, '<' ) ) {

				add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
				return;
			}

			$this->define_constants();
			$this->load_files();
			add_filter( 'all_plugins', __CLASS__ . '::plugins_page' );
		}

		/**
		 * Branding addon on the plugins page.
		 *
		 * @since 1.0.4
		 * @param array $plugins An array data for each plugin.
		 * @return array
		 */
		public static function plugins_page( $plugins ) {

			$branding = self::get_branding();
			$basename = plugin_basename( CP_V2_BASE_DIR . 'convertpro.php' );

			if ( isset( $plugins[ $basename ] ) && is_array( $branding ) ) {

				$plugin_name = ( array_key_exists( 'name', $branding ) ) ? $branding['name'] : CP_PRO_NAME;
				$plugin_desc = ( array_key_exists( 'description', $branding ) ) ? $branding['description'] : CPRO_DESCRIPTION;
				$author_name = ( array_key_exists( 'author', $branding ) ) ? $branding['author'] : CPRO_AUTHOR_NAME;
				$author_url  = ( array_key_exists( 'author_url', $branding ) ) ? $branding['author_url'] : CPRO_AUTHOR_URL;

				if ( ! empty( $plugin_name ) ) {
					$plugins[ $basename ]['Name']  = $plugin_name;
					$plugins[ $basename ]['Title'] = $plugin_name;
				}

				if ( ! empty( $plugin_desc ) ) {
					$plugins[ $basename ]['Description'] = $plugin_desc;
				}

				if ( ! empty( $author_name ) ) {
					$plugins[ $basename ]['Author']     = $author_name;
					$plugins[ $basename ]['AuthorName'] = $author_name;
				}

				if ( ! empty( $author_url ) ) {
					$plugins[ $basename ]['AuthorURI'] = $author_url;
					$plugins[ $basename ]['PluginURI'] = $author_url;
				}
			}

			return $plugins;
		}

		/**
		 * Returns Branding details for the plugin.
		 *
		 * @since 1.0.4
		 * @return array
		 */
		public static function get_branding() {

			$branding['name'] = get_option( 'cpro_branding_plugin_name' );
			if ( defined( 'CPRO_CUSTOM_BRANDING_NAME' ) ) {
				$branding['name'] = CPRO_CUSTOM_BRANDING_NAME;
			}
			$branding['description'] = get_option( 'cpro_branding_plugin_desc' );
			if ( defined( 'CPRO_CUSTOM_DESCRIPTION' ) ) {
				$branding['description'] = CPRO_CUSTOM_DESCRIPTION;
			}
			$branding['author'] = get_option( 'cpro_branding_plugin_author_name' );
			if ( defined( 'CPRO_CUSTOM_AUTHOR_NAME' ) ) {
				$branding['author'] = CPRO_CUSTOM_AUTHOR_NAME;
			}
			$branding['author_url'] = esc_url( get_option( 'cpro_branding_plugin_author_url' ) );
			if ( defined( 'CPRO_CUSTOM_AUTHOR_URL' ) ) {
				$branding['author_url'] = CPRO_CUSTOM_AUTHOR_URL;
			}
			$branding['kb_enabled'] = get_option( 'cpro_branding_enable_kb' );
			if ( defined( 'CPRO_ENABLED_KNOWLEDGE_BASE' ) ) {
				$branding['kb_enabled'] = ( true === CPRO_ENABLED_KNOWLEDGE_BASE ) ? '1' : '0';
			}
			$branding['kb_url'] = esc_url( get_option( 'cpro_branding_url_kb' ) );
			if ( defined( 'CPRO_CUSTOM_KNOWLEDGE_BASE_URL' ) ) {
				$branding['kb_url'] = esc_url( CPRO_CUSTOM_KNOWLEDGE_BASE_URL );
			}
			$branding['support_enabled'] = get_option( 'cpro_branding_enable_support' );
			if ( defined( 'CPRO_ENABLED_SUPPORT_URL' ) ) {
				$branding['support_enabled'] = ( true === CPRO_ENABLED_SUPPORT_URL ) ? '1' : '0';
			}
			$branding['support_url'] = esc_url( get_option( 'cpro_branding_url_support' ) );
			if ( defined( 'CPRO_CUSTOM_SUPPORT_URL' ) ) {
				$branding['support_url'] = esc_url( CPRO_CUSTOM_SUPPORT_URL );
			}
			$branding['image_enabled'] = get_option( 'cpro_branding_enable_image' );
			if ( defined( 'CPRO_ENABLED_IMAGE_URL' ) ) {
				$branding['image_enabled'] = ( true === CPRO_ENABLED_IMAGE_URL ) ? '1' : '0';
			}
			$branding['image_url']  = esc_url( get_option( 'cpro_branding_url_image' ) );
			$branding['addon_desc'] = get_option( 'cpro_addon_branding_plugin_desc' );
			if ( defined( 'CPRO_CUSTOM_ADDON_DESCRIPTION' ) ) {
				$branding['addon_desc'] = CPRO_CUSTOM_ADDON_DESCRIPTION;
			}
			$branding['hide_branding']     = get_option( 'cpro_hide_branding' );
			$branding['hide_refresh_temp'] = get_option( 'cpro_hide_refresh_template' );
			if ( defined( 'CPRO_HIDE_REFRESH_TEMPLATE' ) ) {
				$branding['hide_refresh_temp'] = ( true === CPRO_HIDE_REFRESH_TEMPLATE ) ? '1' : '0';
			}

			$branding['kb_enabled']      = ( false === $branding['kb_enabled'] || '1' === $branding['kb_enabled'] ) ? '1' : '0';
			$branding['support_enabled'] = ( false === $branding['support_enabled'] || '1' === $branding['support_enabled'] ) ? '1' : '0';
			$branding['hide_branding']   = ( false === $branding['hide_branding'] || '0' === $branding['hide_branding'] ) ? '0' : '1';

			$branding['hide_refresh_temp'] = ( false === $branding['hide_refresh_temp'] || '0' === $branding['hide_refresh_temp'] ) ? '0' : '1';

			if ( is_multisite() ) {
				$branding = self::get_multisite_branding();
			}

			// BSF Analytics White label case.
			if ( '1' === $branding['hide_branding'] ) {
				add_filter( 'bsf_white_label_options', __CLASS__ . '::cpro_white_label_option' );
			}

			return $branding;
		}

		/**
		 * Returns White Label status.
		 * Return true if the White Label is enabled from Convert Pro to the BSF Analytics library.
		 *
		 * @since 1.4.7
		 * @param array $data array of white labeled products.
		 * @return array
		 */
		public static function cpro_white_label_option( $data ) {
			if ( ! isset( $data['convertpro'] ) ) {
				$data['convertpro'] = true;
			}
			return $data;
		}

		/**
		 * Returns Branding details for the plugin if it is multisite.
		 *
		 * @since 1.3.4
		 * @return array
		 */
		public static function get_multisite_branding() {

				$branding['name'] = get_site_option( '_cpro_branding_plugin_name' );
			if ( defined( 'CPRO_CUSTOM_BRANDING_NAME' ) ) {
					$branding['name'] = CPRO_CUSTOM_BRANDING_NAME;
			}
			$branding['description'] = get_site_option( '_cpro_branding_plugin_desc' );
			if ( defined( 'CPRO_CUSTOM_DESCRIPTION' ) ) {
				$branding['description'] = CPRO_CUSTOM_DESCRIPTION;
			}
				$branding['author'] = get_site_option( '_cpro_branding_plugin_author_name' );
			if ( defined( 'CPRO_CUSTOM_AUTHOR_NAME' ) ) {
				$branding['author'] = CPRO_CUSTOM_AUTHOR_NAME;
			}
				$branding['author_url'] = esc_url( get_site_option( '_cpro_branding_plugin_author_url' ) );
			if ( defined( 'CPRO_CUSTOM_AUTHOR_URL' ) ) {
				$branding['author_url'] = CPRO_CUSTOM_AUTHOR_URL;
			}
				$branding['kb_enabled'] = get_site_option( '_cpro_branding_enable_kb' );
			if ( defined( 'CPRO_ENABLED_KNOWLEDGE_BASE' ) ) {
				$branding['kb_enabled'] = ( true === CPRO_ENABLED_KNOWLEDGE_BASE ) ? '1' : '0';
			}
				$branding['kb_url'] = esc_url( get_site_option( '_cpro_branding_url_kb' ) );
			if ( defined( 'CPRO_CUSTOM_KNOWLEDGE_BASE_URL' ) ) {
				$branding['kb_url'] = esc_url( CPRO_CUSTOM_KNOWLEDGE_BASE_URL );
			}
				$branding['support_enabled'] = get_site_option( '_cpro_branding_enable_support' );
			if ( defined( 'CPRO_ENABLED_SUPPORT_URL' ) ) {
				$branding['support_enabled'] = ( true === CPRO_ENABLED_SUPPORT_URL ) ? '1' : '0';
			}
				$branding['support_url'] = esc_url( get_site_option( '_cpro_branding_url_support' ) );
			if ( defined( 'CPRO_CUSTOM_SUPPORT_URL' ) ) {
				$branding['support_url'] = esc_url( CPRO_CUSTOM_SUPPORT_URL );
			}
				$branding['image_enabled'] = get_site_option( '_cpro_branding_enable_image' );
			if ( defined( 'CPRO_ENABLED_IMAGE_URL' ) ) {
				$branding['image_enabled'] = ( true === CPRO_ENABLED_IMAGE_URL ) ? '1' : '0';
			}
				$branding['image_url']  = esc_url( get_site_option( '_cpro_branding_url_image' ) );
				$branding['addon_desc'] = get_site_option( '_cpro_addon_branding_plugin_desc' );
			if ( defined( 'CPRO_CUSTOM_ADDON_DESCRIPTION' ) ) {
				$branding['addon_desc'] = CPRO_CUSTOM_ADDON_DESCRIPTION;
			}
				$branding['hide_branding']     = get_site_option( '_cpro_hide_branding' );
				$branding['hide_refresh_temp'] = get_site_option( '_cpro_hide_refresh_template' );
			if ( defined( 'CPRO_HIDE_REFRESH_TEMPLATE' ) ) {
				$branding['hide_refresh_temp'] = ( true === CPRO_HIDE_REFRESH_TEMPLATE ) ? '1' : '0';
			}
				$branding['kb_enabled']      = ( false === $branding['kb_enabled'] || '1' === $branding['kb_enabled'] ) ? '1' : '0';
				$branding['support_enabled'] = ( false === $branding['support_enabled'] || '1' === $branding['support_enabled'] ) ? '1' : '0';
				$branding['hide_branding']   = ( false === $branding['hide_branding'] || '0' === $branding['hide_branding'] ) ? '0' : '1';

				$branding['hide_refresh_temp'] = ( false === $branding['hide_refresh_temp'] || '0' === $branding['hide_refresh_temp'] ) ? '0' : '1';

			return $branding;
		}

		/**
		 * Shows an admin notice for outdated php version.
		 */
		public function php_version_notice() {

			$message = __( 'Your server seems to be running outdated, unsupported and vulnerable version of PHP. You are advised to contact your host provider and upgrade to PHP version 5.6 or greater.', 'convertpro' );

			$this->render_admin_notice( $message, 'warning' );
		}

		/**
		 * Function Name: render_admin_notice.
		 * Function Description: Renders an admin notice.
		 *
		 * @param string $message string parameter.
		 * @param string $type string parameter.
		 */
		private function render_admin_notice( $message, $type = 'update' ) {
			if ( ! is_admin() || ! is_user_logged_in() || ! current_user_can( 'update_core' ) ) {
				return;
			}

			echo '<div class="' . esc_attr( $type ) . '">';
			echo '<p>' . esc_attr( $message ) . '</p>';
			echo '</div>';
		}

		/**
		 * Define constants.
		 *
		 * @since 1.0
		 * @return void
		 */
		private function define_constants() {

			$file = dirname( dirname( __FILE__ ) );

			define( 'CP_V2_DIR_NAME', plugin_basename( $file ) );
			define( 'CP_V2_BASE_FILE', trailingslashit( $file ) . CP_V2_DIR_NAME . '.php' );
			define( 'CP_V2_BASE_DIR', plugin_dir_path( CP_V2_BASE_FILE ) );
			define( 'CP_V2_BASE_URL', plugins_url( '/', CP_V2_BASE_FILE ) );
			define( 'CP_CUSTOM_POST_TYPE', 'cp_popups' );
			define( 'CP_CAMPAIGN_TAXONOMY', 'cp_campaign' );
			define( 'CP_CONNECTION_TAXONOMY', 'cp_connections' );
			define( 'CP_AB_TEST_TAXONOMY', 'cp_ab_test' );
			define( 'BSF_REMOVE_CONVERTPLUG_PRO_FROM_REGISTRATION_LISTING', true );
			define( 'CP_PRO_NAME', 'Convert Pro' );
			define( 'CP_PRO_SLUG', 'convert-pro' );
			define( 'CP_GOOGLE_RECAPTCHA_SITE_KEY', 'Google Recaptcha Site Key' );
			define( 'CP_GOOGLE_RECAPTCHA_SECRET_KEY', 'Google Recaptcha Secret Key' );
			define( 'CP_POWERED_BY_URL', 'https://www.convertpro.net/?utm_source=customer-website&utm_medium=credit-link&utm_campaign=powered-by' );
			define( 'CP_KNOWLEDGE_BASE_URL', 'https://www.convertpro.net/docs/' );
			define( 'CP_SUPPORT_URL', 'https://www.convertpro.net/support/open-a-ticket/' );
			define( 'CP_IMAGE_URL', 'Custom Image URL' );
			define( 'CPRO_AUTHOR_NAME', 'Brainstorm Force' );
			define( 'CPRO_AUTHOR_URL', 'https://www.brainstormforce.com' );
			define( 'CPRO_DESCRIPTION', CP_PRO_NAME . ' is an advanced lead generation popup plugin with a drag and drop editor that helps you create beautiful popups and opt-in forms to boost your website conversions. With ' . CP_PRO_NAME . ' you can build email lists, drive traffic, promote videos, offer lead magnets and a lot more.' );

			$cpro_multisite_flag = 0;
			if ( is_multisite() ) {
				$cpro_multisite_flag = 1;
			}

			$plugin_name = ( 0 === $cpro_multisite_flag ) ? get_option( 'cpro_branding_plugin_name' ) : get_site_option( '_cpro_branding_plugin_name' );
			$_name       = ( empty( $plugin_name ) || false === $plugin_name ) ? CP_PRO_NAME : $plugin_name;

			if ( defined( 'CPRO_CUSTOM_BRANDING_NAME' ) ) {
				$_name = CPRO_CUSTOM_BRANDING_NAME;
			}
			define( 'CPRO_BRANDING_NAME', $_name );

			// Remove convert pro from license registration listing.
			// Ignore the PHPCS warning about constant declaration.
			// @codingStandardsIgnoreStart
			define( 'BSF_REMOVE_convertpro_FROM_REGISTRATION_LISTING', true );
			// @codingStandardsIgnoreEnd
		}

		/**
		 * Loads classes and includes.
		 *
		 * @since 1.0
		 * @return void
		 */
		private static function load_files() {
			/* Classes */
			$cp_is_admin = is_admin();

			self::$wp_block_parser_class_exists = class_exists( 'WP_Block_Parser' ) ? true : false;

			require_once CP_V2_BASE_DIR . 'includes/common-helper-functions.php';
			require_once CP_V2_BASE_DIR . 'includes/google-fonts.php';
			require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-fonts.php';
			require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-post-type.php';

			require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-auto-update.php';
			if ( self::$wp_block_parser_class_exists ) {
				require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-wp-block-parser.php';
			}

			require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-model.php';
			require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-popups.php';

			if ( $cp_is_admin ) {
				require_once CP_V2_BASE_DIR . 'includes/ajax-actions.php';
				// load maxmind Geolocation file.
				require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-maxmind-geolocation.php';

				require_once CP_V2_BASE_DIR . 'includes/admin-helper-functions.php';
				require_once CP_V2_BASE_DIR . 'framework/classes/class-cp-framework.php';

				// load framework mapper class.
				require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-admin.php';
				require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-cloud-templates.php';
				require_once CP_V2_BASE_DIR . 'includes/insights-actions.php';
				require_once CP_V2_BASE_DIR . 'includes/class-bsf-menu.php';
				require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-tab-menu.php';

				// Load Astra Notices library.
				require_once CP_V2_BASE_DIR . 'lib/astra-notices/class-astra-notices.php';

			}

			// If WPMl plugin is active.
			if ( function_exists( 'icl_object_id' ) && class_exists( 'SitePress' ) ) {
				require_once CP_V2_BASE_DIR . 'classes/class-cp-v2-wpml.php';
			}

			require_once CP_V2_BASE_DIR . 'framework/class-add-convertplug-v2-widget.php';

			// BSF Analytics library.
			if ( ! class_exists( 'BSF_Analytics_Loader' ) ) {
				require_once CP_V2_BASE_DIR . 'admin/bsf-analytics/class-bsf-analytics-loader.php';
			}

			$bsf_analytics = BSF_Analytics_Loader::get_instance();

			$bsf_analytics->set_entity(
				array(
					'bsf' => array(
						'product_name'    => 'Convert Pro',
						'path'            => CP_V2_BASE_DIR . 'admin/bsf-analytics',
						'author'          => 'Brainstorm Force',
						'time_to_display' => '+24 hours',
					),
				)
			);
		}
	}

	$cp_v2_loader = Cp_V2_Loader::get_instance();
} else {

	add_action( 'admin_notices', 'admin_notices' );
	add_action( 'network_admin_notices', 'admin_notices' );

	/**
	 * Function Name: admin_notices.
	 * Function Description: admin notices.
	 */
	function admin_notices() {

		$url = admin_url( 'plugins.php' );

		?>
		<div class="notice notice-error"><p>
		<?php
		echo esc_html__( 'You currently have two versions of', 'convertpro' );
		?>
		<strong><?php echo esc_html__( 'Convert Pro', 'convertpro' ); ?></strong>
		<?php echo esc_html__( 'active on this site. Please', 'convertpro' ); ?>
		<a href=<?php echo esc_url( $url ); ?>>
		<?php echo esc_html__( 'deactivate one', 'convertpro' ); ?></a>
		<?php echo esc_html__( ' before continuing.', 'convertpro' ); ?>
		</p></div>
		<?php
	}
}

add_action( 'wp_ajax_cp_dismiss_notice', 'cp_dismiss_notice' );
if ( ! function_exists( 'cp_dismiss_notice' ) ) {

	/**
	 * Function Name: cp_dismiss_notice.
	 * Function Description: cp dismiss notice.
	 */
	function cp_dismiss_notice() {
		$notice = esc_attr( $_POST['notice'] ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		update_option( $notice, true );
		die();
	}
}
