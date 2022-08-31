<?php
/**
 * WPML support
 *
 * @package Convert Pro
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'Cp_V2_WPML' ) ) {

	/**
	 * Responsible for setting up constants, classes and includes.
	 *
	 * @since 1.0.0
	 */
	final class Cp_V2_WPML {


		/**
		 * The class instance.
		 *
		 * @since 1.0.0
		 * @var string $instance
		 */
		private static $instance;

		/**
		 * The orignal link of CTA.
		 *
		 * @since 1.3.5
		 * @var string $original_links_cta
		 */
		private $original_links_cta = array();

		/**
		 * Languages.
		 *
		 * @since 1.1.4
		 * @var array $languages
		 */
		private static $languages;

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
			add_filter( 'cp_design_list_columns', array( $this, 'render_design_table_cols' ) );

			add_filter( 'cp_design_list_rows', array( $this, 'render_design_table_rows' ) );

			add_action( 'cp_get_translations_row_value', array( $this, 'render_style_translations' ), 10 );

			add_filter( 'cpro_call_to_action_id', array( $this, 'duplicate_post' ) );

			add_filter( 'cpro_front_end_cta_id', array( $this, 'get_translated_id' ) );

			add_filter( 'wpml_link_to_translation', array( $this, 'modify_translation_link' ), 10, 4 );

			add_filter( 'wpml_post_status_display_html', array( $this, 'modify_translation_link_new' ), 11, 4 );

			global $sitepress;
			$current_language = $sitepress->get_current_language();
			$all_languages    = $sitepress->get_active_languages();
			unset( $all_languages[ $current_language ] );

			self::$languages = $all_languages;
		}

		/**
		 * Function Name: get_translated_id.
		 * Function Description: Get tranlslated call-to-action ID.
		 *
		 * @param int $id call to action.
		 */
		public function get_translated_id( $id ) {
			$id = apply_filters( 'wpml_object_id', $id, CP_CUSTOM_POST_TYPE );

			return $id;
		}

		/**
		 * Function Name: duplicate_post.
		 * Function Description: Duplicate and create call-to-action for current language.
		 *
		 * @param int $id call to action.
		 */
		public function duplicate_post( $id ) {

			if ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && ! wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) ) {
				return;
			}

			$screen = get_current_screen();

			if ( 'add' === $screen->action && isset( $_GET['master_post'] ) && '' !== $_GET['master_post'] ) {
				$style_id   = esc_attr( $_GET['master_post'] );
				$modal_data = get_post_meta( $style_id, 'cp_modal_data', true );

				global $sitepress;
				$id = $sitepress->make_duplicate( $style_id, esc_attr( $_GET['lang'] ) );

				$module_type = get_post_meta( $style_id, 'cp_module_type', true );
				$campaigns   = wp_get_post_terms( $style_id, CP_CAMPAIGN_TAXONOMY );

				update_post_meta( $id, 'cp_module_type', $module_type );

				foreach ( $campaigns as $key => $campaign ) {
					wp_set_object_terms( $id, (int) $campaign->term_id, CP_CAMPAIGN_TAXONOMY, false );
				}
			}

			return $id;
		}

		/**
		 * Function Name: render_design_table_rows.
		 * Function Description: render design table rows.
		 *
		 * @param string $rows rows.
		 */
		public function render_design_table_rows( $rows ) {
			array_unshift( $rows, 'translations' );
			return $rows;
		}

		/**
		 * Function Name: render_style_translations.
		 * Function Description: render style translations.
		 *
		 * @param int $style Design ID.
		 */
		public function render_style_translations( $style ) {

			global $sitepress;

			$post_dis_obj = new WPML_Post_Status_Display( self::$languages );

			foreach ( self::$languages as $language_data ) {
				$icon_html = $post_dis_obj->get_status_html( $style->ID, $language_data['code'] );
				echo $icon_html; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			}
		}

		/**
		 * Function Name: render_design_table_cols.
		 * Function Description: render design table cols.
		 *
		 * @param string $cols cols.
		 */
		public function render_design_table_cols( $cols ) {

			global $sitepress;
			$flags_column = '';

			foreach ( self::$languages as $language_data ) {
				$flags_column .= '<img src="' . esc_url( $sitepress->get_flag_url( $language_data['code'] ) ) . '" width="18" height="12" alt="' . esc_attr( $language_data['display_name'] ) . '" title="' . esc_attr( $language_data['display_name'] ) . '" style="margin:2px;width:18px;" />';
			}

			$custom_cols = array(
				'translations' => array(
					'label' => $flags_column,
				),
			);
			$cols        = $custom_cols + $cols;
			return $cols;
		}

		/**
		 * Function Name: render_design_table_cols.
		 * Function Description: render design table cols.
		 *
		 * @param string $link link for post edit.
		 * @param int    $post_id post id.
		 * @param string $lang language code.
		 * @param int    $trid trid.
		 */
		public function modify_translation_link( $link, $post_id, $lang, $trid ) {
			$this->original_links_cta[ $post_id ][ $lang ][ $trid ] = $link;

			$post_type = get_post_type( $post_id );

			if ( CP_CUSTOM_POST_TYPE === $post_type && false !== strpos( $link, 'post-new' ) ) {
				$module_type = get_post_meta( $post_id, 'cp_module_type', true );
				$link       .= '&master_post=' . $post_id . '&type=' . $module_type;
				$link       .= '&save_now=true';
			}

			return $link;
		}

		/**
		 * Function Name: modify_translation_link_new.
		 * Function Description: modify translation link new.
		 *
		 * @since 1.3.5
		 * @param string $html html for post edit.
		 * @param int    $post_id post id.
		 * @param string $lang language code.
		 * @param int    $trid trid.
		 */
		public function modify_translation_link_new( $html, $post_id, $lang, $trid ) {

			$data_attributes = '';
			$post_type       = get_post_type( $post_id );
			$link            = '';
			if ( CP_CUSTOM_POST_TYPE === $post_type && ( false !== strpos( $html, 'post-new' ) || false !== strpos( $html, 'admin' ) ) ) {

				$module_type      = get_post_meta( $post_id, 'cp_module_type', true );
				$link             = 'post-new.php?lang=' . $lang . '&post_type=cp_popups&trid=' . $trid . '&source_lang=en';
				$link            .= '&master_post=' . $post_id . '&type=' . $module_type;
				$link            .= '&save_now=true';
				$data_attributes .= 'href="' . $link . '"';
			}
			if ( false !== strpos( $html, 'data-tm-job-id' ) && CP_CUSTOM_POST_TYPE === $post_type ) {
				$data_attributes = 'href="' . $this->original_links_cta[ $post_id ][ $lang ][ $trid ] . '"';
			}

			return str_replace( '<a ', '<a ' . $data_attributes . ' ', $html );
		}

	}

	$ga_insights = Cp_V2_WPML::get_instance();
}
