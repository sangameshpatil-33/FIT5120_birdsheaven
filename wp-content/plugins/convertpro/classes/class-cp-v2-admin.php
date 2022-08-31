<?php
/**
 * Main builder admin class.
 *
 * @package ConvertPro
 */

/**
 * Class bsf menu.
 */
final class CP_V2_Admin {

	/**
	 * The unique instance of the plugin.
	 *
	 * @var parent_page_slug
	 */
	private static $instance;

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

		add_action( 'admin_init', array( $this, 'redirect_on_activation' ) );
		add_action( 'admin_print_scripts', array( $this, 'deregister_scripts' ), 11 );
		// WPBakery JS conflict code starts.
		add_action( 'admin_print_scripts-post.php', array( $this, 'cp_wpbakery_deregister_scripts' ), 20 );
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'cp_wpbakery_deregister_scripts' ), 20 );
		// WPBakery JS conflict code ends.
		add_filter( 'plugin_action_links_' . CP_V2_DIR_NAME, array( $this, 'action_links' ), 10, 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'cpro_admin_scripts' ), 301 );
		add_action( 'mce_external_plugins', array( $this, 'load_tiny_scripts' ), 10 );
		add_action( 'admin_footer', array( $this, 'edit_post_type_screen' ), 10 );
		add_action( 'current_screen', array( $this, 'init_framework_components' ) );
		add_filter( 'user_can_richedit', array( $this, 'enable_tinyeditor' ), 50 );
		add_filter( 'bsf_allow_beta_updates_convertpro', array( $this, 'cpro_beta_updates_check' ) );
		// Fix for Search Exclude plugin.
		add_filter( 'searchexclude_filter_search', array( $this, 'cp_remove_filter_search' ), 10, 2 );
	}

	/**
	 * Function Name: cp_wpbakery_deregister_scripts.
	 * Function Description: WPBakery Deregister scripts which conflicts with Convert Pro
	 *
	 * @since 1.3.3
	 */
	public function cp_wpbakery_deregister_scripts() {

		if ( function_exists( 'get_current_screen' ) ) {

			$screen = get_current_screen();

			if ( ! empty( $screen ) && isset( $screen->base ) && 'post' === $screen->base && CP_CUSTOM_POST_TYPE === $screen->post_type ) {
				wp_dequeue_script( 'vc-backend-min-js' );
				wp_deregister_script( 'vc-backend-min-js' );
			}
		}
	}

	/**
	 * Function Name: deregister_scripts.
	 * Function Description: Deregister scripts which conflicts with Convert Pro
	 *
	 * @since 1.1.3
	 */
	public function deregister_scripts() {

		$screen = get_current_screen();

		if ( isset( $screen->base ) && strpos( $screen->base, CP_PRO_SLUG ) !== false ) {
			// Deregister clinky plugin script.
			wp_dequeue_script( 'yoast_ga_admin' );
		}
	}

	/**
	 * Function Name: cpro_beta_updates_check.
	 * Function Description: Turn on the Beta updates for Convert Pro.
	 *
	 * @since 1.0.4
	 */
	public function cpro_beta_updates_check() {

		$beta_update_option = esc_attr( get_option( 'cpro_beta_updates' ) );

		if ( (int) $beta_update_option ) {
			return true;
		}

			return false;
	}

	/**
	 * Function Name: enable_tinyeditor.
	 * Function Description: Turn on the rich tiny editor for Convert Pro pages.
	 *
	 * @param bool $wp_rich_edit wp editor.
	 * @since 1.0.0
	 */
	public function enable_tinyeditor( $wp_rich_edit ) {

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();

			if ( ! empty( $screen ) && isset( $screen->base ) && 'post' === $screen->base && CP_CUSTOM_POST_TYPE === $screen->post_type ) {
				$wp_rich_edit = true;
			}
		}

		return $wp_rich_edit;
	}

	/**
	 * Redirect on activation hook
	 *
	 * @since 1.0
	 */
	public function redirect_on_activation() {

		if ( get_option( 'convert_pro_redirect' ) === true ) {
			update_option( 'convert_pro_redirect', false );

			if ( ! is_multisite() ) :
				$this->redirect_to_home();
			endif;
		}
	}

	/**
	 * Redirect to Convertro plugin home page after updating menu position
	 */
	public function redirect_to_home() {

		// Menu position.
		$position      = esc_attr( get_option( 'bsf_menu_position' ) );
		$menu_position = ! $position ? 'middle' : $position;

		$is_top_level_page = in_array( $menu_position, array( 'top', 'middle', 'bottom' ), true );

		// If menu is at top level.
		if ( $is_top_level_page ) {
			$url = admin_url( 'admin.php?page=' . CP_PRO_SLUG );
		} else {
			if ( strpos( $menu_position, '?' ) !== false ) {
				$query_var = '&page=' . CP_PRO_SLUG;
			} else {
				$query_var = '?page=' . CP_PRO_SLUG;
			}
			$url = admin_url( $menu_position . $query_var );
		}

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Function Name: action_links.
	 * Function Description: Adds settings link in plugins action.
	 *
	 * @param string $actions string parameter.
	 * @param string $plugin_file string parameter.
	 */
	public function action_links( $actions, $plugin_file ) {
		static $plugin;

		if ( ! isset( $plugin ) ) {
			$plugin = plugin_basename( __FILE__ );
		}
		if ( $plugin === $plugin_file ) {
			$settings = array(
				/* translators: %s link */
				'settings' => sprintf( __( '<a href="%s">Settings</a>', 'convertpro' ), admin_url( 'admin.php?page=' . CP_PRO_SLUG . '&view=settings' ) ),
			);
			$actions  = array_merge( $settings, $actions );
		}
		return $actions;
	}

	/**
	 * Function Name: cpro_admin_scripts.
	 * Function Description: Dequeue scripts and styles on admin area of Convert Pro popup Editor.
	 *
	 * @param string $hook string parameter.
	 */
	public function cpro_admin_scripts( $hook ) {

		if ( $this->cpro_popup_editor_condition_check( $hook ) ) {
			// Woodmart theme JS conflict with CPRO popup editor.
			wp_dequeue_script( 'wp-color-picker-alpha' );
			global $wp_version;
			$wp = '5.6';
			if ( version_compare( $wp_version, $wp, '>=' ) ) {
				wp_deregister_script( 'jquery-ui-resizable' );
			}
		}
	}


	/**
	 * Function Name: admin_scripts.
	 * Function Description: Load scripts and styles on admin area of convertPro.
	 *
	 * @param string $hook string parameter.
	 */
	public function admin_scripts( $hook ) {

		$new_handle = '';
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'cp-admin-style', CP_V2_BASE_URL . 'assets/admin/css/convertplug-admin.css', array(), CP_V2_VERSION );

		$current_screen = get_current_screen();

		global $post;

		if ( strpos( $hook, CP_PRO_SLUG ) !== false ) {
			wp_dequeue_script( 'yoast_ga_admin' );

			wp_enqueue_script( 'thickbox' );

			wp_enqueue_style( 'cp-frosty-style', CP_V2_BASE_URL . 'assets/admin/css/frosty.css', array(), CP_V2_VERSION );
			wp_enqueue_script( 'cp-frosty-script', CP_V2_BASE_URL . 'assets/modules/js/frosty.js', false, CP_V2_VERSION, true );
			wp_enqueue_script( 'cp-dashboard-script', CP_V2_BASE_URL . 'assets/admin/js/dashboard.js', false, CP_V2_VERSION, true );

			wp_enqueue_script( 'convert-select2', CP_V2_BASE_URL . 'assets/admin/js/select2.js', false, CP_V2_VERSION, true );

			wp_localize_script(
				'cp-dashboard-script',
				'cp_ajax',
				array(
					'url'             => admin_url( 'admin-ajax.php' ),
					'ajax_nonce'      => wp_create_nonce( 'cp_ajax_nonce' ),
					'refresh_btn_txt' => __( 'Clear Cache', 'convertpro' ),
					'loading_txt'     => __( 'Clearing cache...', 'convertpro' ),
					'cleared_cache'   => __( 'Cleared', 'convertpro' ),
				)
			);
			$new_handle = 'cp-dashboard-script';
			wp_enqueue_style( 'cp-animate', CP_V2_BASE_URL . 'assets/modules/css/animate.css', array(), CP_V2_VERSION );

			wp_enqueue_style( 'css-select2', CP_V2_BASE_URL . 'assets/admin/css/select2.min.css', array(), CP_V2_VERSION );
			wp_enqueue_style( 'cp-switch-style', CP_V2_BASE_URL . 'assets/admin/css/switch.css', array(), CP_V2_VERSION );
			wp_enqueue_script( 'cp-switch-script', CP_V2_BASE_URL . 'assets/admin/js/switch.js', false, CP_V2_VERSION, true );
			wp_enqueue_style( 'cp-dashboard-style', CP_V2_BASE_URL . 'assets/admin/css/dashboard.css', array(), CP_V2_VERSION );
		}

		$dev_mode = get_option( 'cp_dev_mode' );
		if ( $this->cpro_popup_editor_condition_check( $hook ) ) {

					// Fix Insert Post Ads plugin JS Conflict with CPro popup editor.
					wp_dequeue_script( 'insert-post-adschart-admin' );

					// Fix WP Shortcode Pro by MyThemeShop plugin JS Conflict with CPro popup editor.
					wp_dequeue_script( 'wps-izimodal' );
					wp_dequeue_script( 'wp-shortcode-admin' );

					// Fix Raffle Ticket Generator - Woocommerce JS Conflict with CPro popup editor.
					wp_dequeue_script( 'custom-script' );
					wp_dequeue_script( 'custom-script-12' );

					// Fix Urban Mag theme JS Conflict with CPro popup editor.
					wp_dequeue_script( 'gdlr-tax-meta' );

					// Fix Ads for WP – Advanced Ads & Adsense Solution for WP & AMP plugin JS Conflict with CPro popup editor.
					wp_dequeue_script( 'ads-for-wp-admin-js' );

					// Fix Listeo-Core - Directory Plugin by Purethemes plugin JS Conflict with CPro popup editor.
					wp_dequeue_script( 'listeo_core-settings' );
					wp_dequeue_script( 'listeo_core-admin' );

					// Enqueue jquery-migrate script in the customizer.
					wp_enqueue_script( 'jquery-migrate' );

					// Fix YITH WooCommerce Membership premium plugin JS Conflict with CPro popup editor.
					wp_dequeue_script( 'yith_wcmbs_admin_js' );

					// Fix YUZO plugin JS Conflict with CPro popup editor.
					wp_dequeue_script( 'pf-plugins' );

					// Fix WP Schema Pro plugin JS Conflict with CPro popup editor.
					wp_dequeue_script( 'aiosrs-pro-admin-edit-script' );

					// Fix Soho Hotel Booking plugin JS Conflict with CPro popup editor.
					wp_dequeue_script( 'sohohotel_booking_js' );

					// Fix Phlox theme CSS Conflict.
					wp_dequeue_style( 'auxin-admin-style' );

					// Fix Strong Testimonials Pro Templates JS Conflict.
					wp_dequeue_script( 'wpmtst-color-picker-alpha-script' );

					// Fix Ultimate VC Addon JS Conflict - Issue with the integrated Addon.
					wp_dequeue_script( 'woocomposer-admin-script' );

					// Fix Savory Theme JS Conflicts.
					wp_deregister_script( 'eltd-ui-admin' );

					// Fix Evergenius Theme JS Conflicts.
					wp_dequeue_script( 'xt-ui-admin' );

					// Fix for Bridge theme js - Issue with the required form field in the CPro Popup Editor.
					wp_deregister_script( 'qodef-ui-admin' );

					// Fix Apply Online plugin JS Conflicts.
					wp_dequeue_script( 'apply-online' );

					// Fix Woo Birthday discount vouchers plugin JS Conflicts.
					wp_dequeue_script( 'wbdv-admin-script' );

					// Fix Custom Facebook Feed Pro - Extensions plugin JS Conflicts.
					wp_dequeue_script( 'cff_ext_date_range' );

					// Fix Schema and Structured data for wp plugin JS Conflicts.
					wp_dequeue_script( 'saswp-main-js' );

					// Fix WooCommerce Table rate Shipping plugin JS Conflicts.
					wp_dequeue_script( 'betrs_settings_table_rates_js' );

					// Fix Civic Cookie Control 8 plugin JS Conflicts.
					wp_dequeue_script( 'civic_cookiecontrol_settings' );

					// Fix Switching Product plugin JS Conflicts.
					wp_dequeue_script( 'swProductAdminJs' );

					// Fix WooCommerce Store Exporter Deluxe plugin JS Conflicts.
					wp_dequeue_script( 'woo_ce_scripts' );

					// Fix Multi Rating plugin JS Conflicts.
					wp_dequeue_script( 'mr-admin-script' );

					// Fix Showcase pro(Child theme of Genesis Parent theme) theme JS Conflicts.
					wp_dequeue_script( 'showcase_notice_script' );

					// Fix All in one schema rich snippets plugin JS Conflicts.
					wp_dequeue_script( 'bsf-scripts' );

					// Fix for JS conflict with Schema Creator By Reven plugin.
					wp_dequeue_script( 'schema-form' );
					wp_dequeue_script( 'jquery-timepicker' );
					wp_dequeue_script( 'jquery-ui-datepicker' );

					// Fix for wpsso core plugin JS conflict.
					wp_dequeue_script( 'sucom-metabox' );

					// Fix for JS conflict with WP to Buffer plugin.
					wp_dequeue_script( 'wp-to-buffer-admin' );
					wp_dequeue_script( 'wp-to-buffer-pro-admin' );
					wp_dequeue_style( 'DailyMaverickOnePageSubscriptionCheckoutSubscriptionSliderJqueryUi-css' );

					// Fix for Bridge theme js issue.
					wp_dequeue_script( 'bridge-admin-default' );
					wp_dequeue_script( 'default' );
					wp_dequeue_script( 'qodef-ui-admin' );

					// Fix for Pie Register plugin js issue.
					wp_dequeue_script( 'pie_prBackendVariablesDeclaration_script_Footer' );

					// Fix for Silverscreen theme by Edge Themes.
					wp_dequeue_script( 'edgtf-ui-admin' );

					// Fix for colorpicker conflict with fusion builder plugin.
					wp_dequeue_script( 'wp-color-picker-alpha' );

					// Fix for date timepicker conflict with themify plugin.
					wp_dequeue_script( 'themify-plupload' );
					wp_dequeue_script( 'themify-metabox' );

					// Fix for JS conflict with NextGen Gallery.
					wp_dequeue_script( 'frame_event_publisher' );

					// Fix for date timepicker conflict with Advance CF7 DB.
					wp_dequeue_script( 'advanced-cf7-db' );

					// Fix for Molongui Authorship Premium Plugin.
					wp_dequeue_script( 'Molongui Authorship-edit-post' );

					wp_enqueue_media();

					// This script removes files related to WP SEO Meta plugin.
					// We are doing this since they have a conflict with private custom post type post.

			if ( wp_script_is( 'm-wp-seo-metabox', 'enqueued' ) ) {
				wp_dequeue_script( 'm-wp-seo-metabox' );
			}
			if ( wp_script_is( 'metaseo-cliffpyles', 'enqueued' ) ) {
				wp_dequeue_script( 'metaseo-cliffpyles' );
			}
					// developer mode.
			if ( '1' === $dev_mode ) {
				// array of styles to enqueue in customizer.
				$styles = array(
					'convert-admin'                     => CP_V2_BASE_URL . 'assets/admin/css/admin.css',
					'css-select2'                       => CP_V2_BASE_URL . 'assets/admin/css/select2.min.css',
					'cp-pscroll-style'                  => CP_V2_BASE_URL . 'assets/admin/css/perfect-scrollbar.min.css',
					'cp-frosty-style'                   => CP_V2_BASE_URL . 'assets/admin/css/frosty.css',
					'cp-animate'                        => CP_V2_BASE_URL . 'assets/modules/css/animate.css',
					'cp-customizer-style'               => CP_V2_BASE_URL . 'assets/admin/css/cp-customizer.css',
					'cp-bootstrap-datetimepicker-style' => CP_V2_BASE_URL . 'assets/admin/css/bootstrap-datetimepicker.min.css',
					'cp-bootstrap-standalone-style'     => CP_V2_BASE_URL . 'assets/admin/css/bootstrap-datetimepicker-standalone.min.css',
					'cp-rotation-style'                 => CP_V2_BASE_URL . 'assets/admin/css/jquery.ui.rotatable.css',
					'cp-component-style'                => CP_V2_BASE_URL . 'assets/admin/css/component.css',
					'cp-tiny-style'                     => CP_V2_BASE_URL . 'assets/admin/css/cp-tinymce.css',
				);

				$styles = apply_filters( 'cp_customizer_styles', $styles );

				do_action( 'cp_before_load_scripts' );

				foreach ( $styles as $handle => $src ) {
					wp_enqueue_style( $handle, $src, array(), CP_V2_VERSION );
				}

				wp_enqueue_style( 'cp-switch-style', CP_V2_BASE_URL . 'assets/admin/css/switch.css', array(), CP_V2_VERSION );

				wp_enqueue_script( 'cp-switch-script', CP_V2_BASE_URL . 'assets/admin/js/switch.js', false, CP_V2_VERSION, true );

				// scripts to enqueue in customizer ( defined source and dependencies ).
				$scripts = array(
					'cp-jquery-cookie'         => CP_V2_BASE_URL . 'assets/admin/js/jquery.cookies.js',
					'cp-frosty-script'         => CP_V2_BASE_URL . 'assets/modules/js/frosty.js',
					'convert-select2'          => CP_V2_BASE_URL . 'assets/admin/js/select2.js',
					'cp-helper-functions-js'   => CP_V2_BASE_URL . 'assets/admin/js/cp-helper-functions.js',
					'cp-moment-script'         => array(
						'src' => CP_V2_BASE_URL . 'assets/admin/js/moment-with-locales.js',
						'dep' => array( 'jquery' ),
					),
					'cp-datetimepicker-script' => array(
						'src' => CP_V2_BASE_URL . 'assets/admin/js/bootstrap-datetimepicker.min.js',
						'dep' => array( 'cp-moment-script' ),
					),
					'cp-perfect-scroll-js'     => CP_V2_BASE_URL . 'assets/admin/js/perfect-scrollbar.jquery.js',
					'cp-proptotypes-js'        => array(
						'src' => CP_V2_BASE_URL . 'assets/admin/js/cp-proptotypes.js',
						'dep' => array( 'cp-helper-functions-js' ),
					),
					'cp-panel-layers-js'       => array(
						'src' => CP_V2_BASE_URL . 'assets/admin/js/cp-panel-layers.js',
						'dep' => array( 'cp-helper-functions-js' ),
					),
					'cp-edit-panel-js'         => array(
						'src' => CP_V2_BASE_URL . 'assets/admin/js/cp-edit-panel.js',
						'dep' => array( 'cp-helper-functions-js' ),
					),
					'cp-panel-steps-js'        => array(
						'src' => CP_V2_BASE_URL . 'assets/admin/js/cp-panel-steps.js',
						'dep' => array( 'cp-helper-functions-js' ),
					),
					'cp-backbone-model-js'     => array(
						'src' => CP_V2_BASE_URL . 'assets/admin/js/cp-backbone-panel.js',
						'dep' => array( 'cp-helper-functions-js' ),
					),
					'cp-mobile-editor'         => CP_V2_BASE_URL . 'assets/admin/js/cp-mobile-editor.js',
					'cp-design-area'           => CP_V2_BASE_URL . 'assets/admin/js/cp-design-area.js',
					'cp-field-events'          => array(
						'src' => CP_V2_BASE_URL . 'assets/admin/js/cp-field-events.js',
						'dep' => array( 'backbone' ),
					),
					'cp-sidepanel-js'          => array(
						'src' => CP_V2_BASE_URL . 'assets/admin/js/cp-sidepanel.js',
						'dep' => array( 'backbone' ),
					),
					'cp-rotation-script'       => CP_V2_BASE_URL . 'assets/admin/js/jquery.ui.rotatable.js',
					'cp-modal-effect'          => CP_V2_BASE_URL . 'assets/admin/js/modalEffects.js',
				);

				$scripts = apply_filters( 'cp_customizer_scripts', $scripts );

				foreach ( $scripts as $slug => $script ) {
					$src        = $script;
					$dependency = array( 'jquery' );

					if ( is_array( $script ) ) {
						$dependency = $script['dep'];
						$src        = $script['src'];
					}

					wp_enqueue_script( $slug, $src, $dependency, CP_V2_VERSION, true );
				}

				$new_handle = 'cp-helper-functions-js';

				wp_localize_script(
					'cp-helper-functions-js',
					'cp_customizer_vars',
					array(
						'admin_img_url'         => CP_V2_BASE_URL . 'assets/admin/img',
						'timer_labels'          => __( 'Years', 'convertpro' ) . ',' . __( 'Months', 'convertpro' ) . ',' . __( 'Weeks', 'convertpro' ) . ',' . __( 'Days', 'convertpro' ) . ',' . __( 'Hours', 'convertpro' ) . ',' . __( 'Minutes', 'convertpro' ) . ',' . __( 'Seconds', 'convertpro' ),
						'timer_labels_singular' => __( 'Year', 'convertpro' ) . ',' . __( 'Month', 'convertpro' ) . ',' . __( 'Week', 'convertpro' ) . ',' . __( 'Day', 'convertpro' ) . ',' . __( 'Hour', 'convertpro' ) . ',' . __( 'Minute', 'convertpro' ) . ',' . __( 'Second', 'convertpro' ),
					)
				);
			} else {
				// array of styles to enqueue in customizer.
				$styles = array(
					'convert-admin-css' => CP_V2_BASE_URL . 'assets/admin/css/admin.min.css',
				);

				$styles = apply_filters( 'cp_customizer_styles', $styles );

				foreach ( $styles as $handle => $src ) {
					wp_enqueue_style( $handle, $src, array(), CP_V2_VERSION );
				}

				// scripts to enqueue in customizer ( defined source and dependencies ).
				$scripts = array(
					'convert-admin-js' => CP_V2_BASE_URL . 'assets/admin/js/admin.min.js',
				);

				$scripts = apply_filters( 'cp_customizer_scripts', $scripts );

				foreach ( $scripts as $slug => $script ) {
					$src        = $script;
					$dependency = array( 'jquery' );

					if ( is_array( $script ) ) {
						$dependency = $script['dep'];
						$src        = $script['src'];
					}

					wp_enqueue_script( $slug, $src, $dependency, CP_V2_VERSION, true );
				}

				$new_handle = 'convert-admin-js';

				wp_localize_script(
					'convert-admin-js',
					'cp_customizer_vars',
					array(
						'admin_img_url'         => CP_V2_BASE_URL . 'assets/admin/img',
						'timer_labels'          => __( 'Years', 'convertpro' ) . ',' . __( 'Months', 'convertpro' ) . ',' . __( 'Weeks', 'convertpro' ) . ',' . __( 'Days', 'convertpro' ) . ',' . __( 'Hours', 'convertpro' ) . ',' . __( 'Minutes', 'convertpro' ) . ',' . __( 'Seconds', 'convertpro' ),
						'timer_labels_singular' => __( 'Year', 'convertpro' ) . ',' . __( 'Month', 'convertpro' ) . ',' . __( 'Week', 'convertpro' ) . ',' . __( 'Day', 'convertpro' ) . ',' . __( 'Hour', 'convertpro' ) . ',' . __( 'Minute', 'convertpro' ) . ',' . __( 'Second', 'convertpro' ),
					)
				);
			}

					$cp_typekit_id = '';

					$kit_info = get_option( 'custom-typekit-fonts' );

					include_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( is_plugin_active( 'custom-typekit-fonts/custom-typekit-fonts.php' ) && ! empty( $kit_info['custom-typekit-font-details'] ) ) {
				$cp_typekit_id = esc_js( $kit_info['custom-typekit-font-id'] );
			}

			if ( is_plugin_active( 'custom-fonts/custom-fonts.php' ) ) {
				$terms = get_terms(
					'bsf_custom_fonts',
					array(
						'hide_empty' => false,
					)
				);

				$font_links = array();
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						if ( $term->name ) {
							$font_links[ $term->name ] = self::get_font_links( $term->term_id );
						}
					}

					$css = '<style type="text/css">';

					foreach ( $font_links as $font => $links ) :
						$css .= '@font-face { font-family:' . esc_attr( $font ) . ';';
						$css .= 'src:';
						$arr  = array();
						if ( $links['font_woff_2'] ) {
							$arr[] = 'url(' . esc_url( $links['font_woff_2'] ) . ") format('woff2')";
						}
						if ( $links['font_woff'] ) {
							$arr[] = 'url(' . esc_url( $links['font_woff'] ) . ") format('woff')";
						}
						if ( $links['font_ttf'] ) {
							$arr[] = 'url(' . esc_url( $links['font_ttf'] ) . ") format('truetype')";
						}
						if ( $links['font_svg'] ) {
							$arr[] = 'url(' . esc_url( $links['font_svg'] ) . '#' . esc_attr( strtolower( str_replace( ' ', '_', $font ) ) ) . ") format('svg')";
						}
						$css .= join( ', ', $arr );
						$css .= ';}';
					endforeach;

					$css .= '</style>';

					echo $css; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				}
			}

					wp_localize_script(
						$new_handle,
						'cp_admin_ajax',
						array(
							'url'               => admin_url( 'admin-ajax.php' ),
							'admin_img_url'     => CP_V2_BASE_URL . 'assets/admin/img',
							'assets_url'        => CP_V2_BASE_URL . 'assets/',
							'mobileIncludeOpt'  => Cp_V2_Model::$mobile_include_opt,
							'stepdependentOpts' => Cp_V2_Model::$step_dependent_options,
							'cp_typekit_id'     => $cp_typekit_id,
							'cpro_get_posts_by_query_nonce' => wp_create_nonce( 'cpro_get_posts_by_query_nonce' ),
						)
					);
		}

		wp_localize_script(
			$new_handle,
			'cp_pro',
			array(
				'close_scroll_ruleset_err_msg' => __( 'Please note that the scroll between range trigger will not work as expected when you have it enabled in more than one rulesets.', 'convertpro' ),
				'group_filters'                => __( 'Specific Pages, Posts, Categories or Taxonomies.', 'convertpro' ),
				'country_filters'              => __( 'Specific Countries.', 'convertpro' ),
				'post_types'                   => __( 'Select post types', 'convertpro' ),
				'hidden_field_text'            => __( 'This is a hidden field.This text will not appear at frontend.', 'convertpro' ),
				'recaptcha_field_text'         => __( 'This is a recaptcha field.This will appear at frontend.', 'convertpro' ),
				'click_here'                   => __( 'Click Here', 'convertpro' ),
				'search_settings'              => __( 'Search Settings...', 'convertpro' ),
				'search_mailer'                => __( 'Search Mailer...', 'convertpro' ),
				'search_elements'              => __( 'Search Shapes...', 'convertpro' ),
				'refreshed'                    => __( 'Refreshed', 'convertpro' ),
				'use_this'                     => __( 'Use This', 'convertpro' ),
				'try_again'                    => __( 'Try Again', 'convertpro' ),
				/* translators: %s link */
				'confirm_delete_design'        => __( 'Are you sure you want to delete this call-to-action?', 'convertpro' ),
				'select_diff_camp'             => __( 'Please select different campaign to process.', 'convertpro' ),
				'empty_campaign'               => __( 'Campaign name cannot be empty.', 'convertpro' ),
				'already_exists_camp'          => __( 'This name is already registered! Please try again using a different name.', 'convertpro' ),
				'already_exists_slug_name'     => __( 'This Slug name is already registered! Please try again using a different Slug name.', 'convertpro' ),
				'empty_design'                 => __( 'Design name cannot be empty.', 'convertpro' ),
				'empty_slug_name'              => __( 'Design Slug name cannot be empty Or It should not contain any space in the name.', 'convertpro' ),
				'deleting'                     => __( 'Deleting...', 'convertpro' ),
				'saving'                       => __( 'Saving...', 'convertpro' ),
				'duplicate'                    => __( 'Duplicate', 'convertpro' ),
				'duplicating'                  => __( 'Duplicating...', 'convertpro' ),
				'creating'                     => __( 'Creating...', 'convertpro' ),
				'saved'                        => __( 'SAVED', 'convertpro' ),
				'error'                        => __( 'ERROR', 'convertpro' ),
				'save_changes'                 => __( 'SAVE CHANGES', 'convertpro' ),
				'not_connected_to_mailer'      => __( 'This form is not connected with any mailer service! Please contact web administrator.', 'convertpro' ),
				'step_delete_confirmation'     => __( 'Do you really want to delete Step', 'convertpro' ),
				'ruleset_delete_confirmation'  => __( 'Are you sure you want to delete this ruleset?', 'convertpro' ),
			)
		);

		do_action( 'cp_admin_settinga_scripts', $current_screen );
	}

	/**
	 * Function Name: cpro_popup_editor_condition_check.
	 * Function Description: Check the popup editor condition.
	 *
	 * @param string $hook string parameter.
	 */
	public function cpro_popup_editor_condition_check( $hook ) {
		$status         = false;
		$current_screen = get_current_screen();
		global $post;

		if ( ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) ) && ( ( ( 'edit' === $current_screen->base && CP_CUSTOM_POST_TYPE === $current_screen->post_type ) || ( ( 'post-new.php' === $hook || 'post.php' === $hook ) && ( isset( $post->post_type ) && CP_CUSTOM_POST_TYPE === $post->post_type ) ) ) && ( ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) || 'add' === $current_screen->action ) ) ) {
				$status = true;
		}
		return $status;
	}
	/**
	 * Get font links
	 *
	 * @since 1.2.5
	 * @param int $term_id custom font term id.
	 * @return array $links custom font data links.
	 */
	public static function get_font_links( $term_id ) {
		$links = get_option( 'taxonomy_bsf_custom_fonts' . "_{$term_id}", array() );
		return self::default_args( $links );
	}

	/**
	 * Default fonts
	 *
	 * @since 1.2.5
	 * @param array $fonts fonts array of fonts.
	 */
	protected static function default_args( $fonts ) {
		return wp_parse_args(
			$fonts,
			array(
				'font_woff_2' => '',
				'font_woff'   => '',
				'font_ttf'    => '',
				'font_svg'    => '',
				'font_eot'    => '',
			)
		);
	}

	/**
	 * Function Name: cp_remove_filter_search.
	 * Function Description: Remove filter for search posts and display all posts.
	 *
	 * @param string $exclude boolean parameter.
	 * @param string $query string parameter.
	 */
	public function cp_remove_filter_search( $exclude, $query ) {

		require_once ABSPATH . 'wp-admin/includes/screen.php';

		$current_screen = get_current_screen();

		if ( ( null !== $current_screen ) && ( ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) ) && ( ( CP_CUSTOM_POST_TYPE === $current_screen->post_type && isset( $_GET['post'] ) ) || ( CP_CUSTOM_POST_TYPE === $current_screen->post_type && isset( $current_screen->action ) && 'add' === $current_screen->action ) ) && ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) ) ) {
			return false;
		}
	}

	/**
	 * Function Name: load_tiny_scripts.
	 * Function Description: Load tiny scripts.
	 *
	 * @param string $mce_plugins string parameter.
	 */
	public function load_tiny_scripts( $mce_plugins ) {

		$current_screen = get_current_screen();

		if ( isset( $current_screen->post_type ) && CP_CUSTOM_POST_TYPE === $current_screen->post_type ) {
			$plugpath    = CP_V2_BASE_URL . 'assets/admin/js/tinymce/plugins/';
			$mce_plugins = (array) $mce_plugins;

			$this->plugins = $this->get_all_plugins();

			foreach ( $this->plugins as $plugin ) {
				$mce_plugins[ "$plugin" ] = $plugpath . $plugin . '/plugin.min.js';
			}
		}

		return $mce_plugins;
	}

	/**
	 * Function Name: get_all_plugins.
	 * Function Description: get all plugins.
	 */
	private function get_all_plugins() {
		return array(
			'anchor',
			'save',
			'link',
			'nonbreaking',
			'visualblocks',
			'visualchars',
		);
	}

	/**
	 * Load customizer on edit post and new post screen
	 *
	 * @since 1.0
	 */
	public function edit_post_type_screen() {

		$current_screen = get_current_screen();

		if ( ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) ) && ( null !== $current_screen ) ) {
				// Display only if it is edit post or new post screen.
			if ( ( CP_CUSTOM_POST_TYPE === $current_screen->post_type && isset( $_GET['post'] ) ) || ( CP_CUSTOM_POST_TYPE === $current_screen->post_type && isset( $current_screen->action ) && 'add' === $current_screen->action ) ) {

				if ( wp_script_is( 'jquery-time-picker', 'enqueued' ) ) {
					wp_dequeue_script( 'jquery-time-picker' );
				}
				if ( wp_script_is( 'custom', 'enqueued' ) ) {
					wp_dequeue_script( 'custom' );
				}
				// Sliced Invoices Plugin JS Conflict for CPRO editor.
				if ( wp_script_is( 'jquery-ui-datetimepicker', 'enqueued' ) ) {
					wp_deregister_script( 'jquery-ui-datetimepicker' );
				}

				$style_id = isset( $_GET['post'] ) ? esc_attr( $_GET['post'] ) : '';
				$type     = get_post_meta( $style_id, 'cp_module_type', true );

				if ( false === $type || 'undefined' === $style_id || empty( $style_id ) ) {
					$type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : 'modal_popup';
				}

				$types_dir = CP_FRAMEWORK_DIR . 'types/';
				$file_path = str_replace( '_', '-', $type );
				$file_path = 'class-cp-' . $file_path;

				// load module class.
				if ( file_exists( $types_dir . $file_path . '.php' ) ) {
					require_once $types_dir . $file_path . '.php';
				}

				require_once CP_V2_BASE_DIR . 'framework/style-options.php';
				require_once CP_V2_BASE_DIR . 'framework/edit.php';
			}

			if ( isset( $_REQUEST['page'] ) && isset( $_REQUEST['view'] ) && strpos( $_REQUEST['page'], CP_PRO_SLUG ) !== false && 'template' === $_REQUEST['view'] ) {
				$data_modal_type = isset( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : '';
				if ( isset( $_REQUEST['cp_debug'] ) ) {
					?>
					<a href="#" style="position: absolute; bottom: 10px; right: auto; top: auto; left: 170px; z-index: 999" data-modal-type="<?php echo esc_attr( $data_modal_type ); ?>" class="cp-btn-primary cp-sm-btn cp-button-style cp-remove-local-templates"><?php esc_html_e( 'Delete Template Data', 'convertpro' ); ?></a>;
						<?php wp_nonce_field( 'cpro_delete_template_data', 'cpro_delete_template_data_nonce' ); ?>
						<?php
				}

				$hide_template_ref_link = ( true === is_multisite() ) ? esc_attr( get_option( 'cpro_hide_refresh_template' ) ) : esc_attr( get_site_option( '_cpro_hide_refresh_template' ) );
				if ( defined( 'CPRO_HIDE_REFRESH_TEMPLATE' ) ) {
					$hide_template_ref_link = ( true === CPRO_HIDE_REFRESH_TEMPLATE ) ? '1' : '0';
				}
				if ( '1' !== $hide_template_ref_link ) {
					?>
					<a href="#" style="position: absolute; bottom: 10px; right: 10px; top: auto; left: auto; z-index: 999" data-modal-type="<?php echo esc_attr( $data_modal_type ); ?>" class="cp-btn-primary cp-sm-btn cp-button-style cp-refresh-templates"><?php esc_html_e( 'Refresh Cloud Templates', 'convertpro' ); ?></a>
						<?php wp_nonce_field( 'cpro_refresh_cloud', 'cpro_refresh_cloud_nonce' ); ?>
						<?php
				}
			}
		}
	}

	/**
	 * Function Name: init_framework_components.
	 * Function Description: Initialize framework components and load jquery UI libraries.
	 *
	 * @param string $current_screen string parameter.
	 */
	public function init_framework_components( $current_screen ) {
		if ( ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) ) && ( ( 'add' === $current_screen->action && CP_CUSTOM_POST_TYPE === $current_screen->post_type )
		|| ( CP_CUSTOM_POST_TYPE === $current_screen->post_type && ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] && 'post' === $current_screen->base ) ) ) ) {
				// include WordPress jQuery inbulit scripts and styles.
				$styles = array(
					'wp-color-picker',
					'thickbox',
				);

				// Load Elements resizable file update for WP 5.6 and above.
				global $wp_version;
				$wp                       = '5.6';
				$cpro_jquery_ui_resizable = 'jquery-ui-resizable';
				if ( version_compare( preg_replace( '/[^0-9\.]/', '', $wp_version ), $wp, '>=' ) ) {
					wp_register_script( 'cpro-jquery-ui-resizable', CP_V2_BASE_URL . 'assets/admin/cpro-jquery/cpro-resizable.min.js', array( 'jquery' ), '1.11.4', true );
					$cpro_jquery_ui_resizable = 'cpro-jquery-ui-resizable';
				}
				$scripts = array(
					'thickbox',
					'jquery',
					'wp-color-picker',
					'jquery-ui-core',
					'jquery-ui-widget',
					'jquery-ui-slider',
					'jquery-ui-draggable',
					'jquery-ui-droppable',
					$cpro_jquery_ui_resizable,
					'jquery-ui-tabs',
					'jquery-ui-autocomplete',
				);

				foreach ( $styles as $style ) {
					wp_enqueue_style( $style );
				}

				foreach ( $scripts as $script ) {
					wp_enqueue_script( $script );
				}
		}
	}
}

$cp_v2_admin = CP_V2_Admin::get_instance();

