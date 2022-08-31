<?php
/**
 * CP_V2_Fonts.
 *
 * @package ConvertPro
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'CP_V2_Fonts' ) ) {

	/**
	 * Class CP_V2_Fonts.
	 */
	class CP_V2_Fonts {

		/**
		 * Get Updated Google font list.
		 *
		 * @since  1.4.8
		 */
		public static function cp_get_updated_google_fonts() {

			$cpro_all_font_weight = array(
				'100italic' => __( '100 italic', 'convertpro' ),
				'200italic' => __( '200 italic', 'convertpro' ),
				'300italic' => __( '300 italic', 'convertpro' ),
				'italic'    => __( '400 italic', 'convertpro' ),
				'500italic' => __( '500 italic', 'convertpro' ),
				'600italic' => __( '600 italic', 'convertpro' ),
				'700italic' => __( '700 italic', 'convertpro' ),
				'800italic' => __( '800 italic', 'convertpro' ),
				'900italic' => __( '900 italic', 'convertpro' ),
			);

			// Get Updated Google fonts from google-fonts.php.
			$cpro_gf_array         = cpro_google_fonts_array();
			$updated_cpro_gf_array = array();
			foreach ( $cpro_gf_array as $key => $font ) {
				$name = key( $font );
				foreach ( $font[ $name ] as $font_key => $single_font ) {
					if ( 'variants' === $font_key ) {
						foreach ( $single_font as $variant_key => $variant ) {

							if ( 'regular' === $variant ) {
								$font[ $name ][ $font_key ][ $variant_key ] = 'Normal';
							}
							if ( array_key_exists( $variant, $cpro_all_font_weight ) ) {
								$font[ $name ][ $font_key ][ $variant_key ] = $cpro_all_font_weight[ $variant ];
							}
						}
						$updated_cpro_gf_array[ $name ] = array_values( $font[ $name ][ $font_key ] );
					}
				}
			}
			return $updated_cpro_gf_array;
		}

		/**
		 * Get font list for dropdown
		 *
		 * @since  0.0.1
		 */
		public static function cp_get_fonts() {

			if ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && ! wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) ) {
				die( 'Nonce not validated.' );
			}

			$google_fonts    = self::cp_get_updated_google_fonts();
			$default_fonts   = self::$default;
			$type_kit_font   = self::$type_kit_font;
			$cp_custom_fonts = self::$cp_custom_fonts;

			$arr_fonts = array();

			/* Default font filter added. */
			$default_fonts = apply_filters( 'cp_add_custom_fonts', $default_fonts );

			$google_fonts = apply_filters( 'cp_add_google_fonts', $google_fonts );

			foreach ( $default_fonts as $font => $value ) {
				array_unshift( $value, 'Inherit' );
				$default_fonts[ $font ] = $value;
			}

			foreach ( $google_fonts as $font => $value ) {
				array_unshift( $value, 'Inherit' );
				$google_fonts[ $font ] = $value;
			}

			$arr_fonts = array(
				'Default' => $default_fonts,
				'Google'  => $google_fonts,
			);

			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( is_plugin_active( 'custom-typekit-fonts/custom-typekit-fonts.php' ) ) {

				$kit_info = get_option( 'custom-typekit-fonts' );

				if ( $kit_info && ! empty( $kit_info['custom-typekit-font-details'] ) ) {

					foreach ( $kit_info['custom-typekit-font-details'] as $font ) {
						array_unshift( $font['weights'], 'Inherit' );
						array_unshift( $font['weights'], 'Normal' );
						$type_kit_font[ $font['family'] ] = $font['weights'];
					}

					$arr_fonts['Typekit'] = $type_kit_font;
				}
			}

			if ( is_plugin_active( 'custom-fonts/custom-fonts.php' ) ) {

				$custom_fonts = self::custom_get_terms( 'bsf_custom_fonts' );

				if ( ! empty( $custom_fonts ) ) {

					$custom_fonts_weights = array( 'Inherit', 'Normal' );

					foreach ( $custom_fonts as $fonts ) {
						$bsf_custom_font[ $fonts->name ] = $custom_fonts_weights;
					}

					$arr_fonts['Custom'] = $bsf_custom_font;
				}
			}

			return $arr_fonts;

		}

		/**
		 * Get all the custom terms
		 *
		 * @since 1.2.5
		 * @param int $term custom font taxonomy name.
		 * @return array of all the terms related to taxonomy.
		 */
		public static function custom_get_terms( $term ) {
			global $wpdb;

			$out = array();

			$a = $wpdb->get_results( $wpdb->prepare( "SELECT t.name,t.slug,t.term_group,x.term_taxonomy_id,x.term_id,x.taxonomy,x.description,x.parent,x.count FROM {$wpdb->prefix}term_taxonomy x LEFT JOIN {$wpdb->prefix}terms t ON (t.term_id = x.term_id) WHERE x.taxonomy=%s;", $term ) );

			foreach ( $a as $b ) {
				$obj                   = new stdClass();
				$obj->term_id          = $b->term_id;
				$obj->name             = $b->name;
				$obj->slug             = $b->slug;
				$obj->term_group       = $b->term_group;
				$obj->term_taxonomy_id = $b->term_taxonomy_id;
				$obj->taxonomy         = $b->taxonomy;
				$obj->description      = $b->description;
				$obj->parent           = $b->parent;
				$obj->count            = $b->count;
				$out[]                 = $obj;
			}

			return $out;
		}

		/**
		 * Array with a list of default fonts.
		 *
		 * @var array
		 */
		public static $default = array(
			'inherit'   => array(
				'Normal',
				'Bold',
			),
			'Helvetica' => array(
				'Normal',
				'Bold',
			),
			'Verdana'   => array(
				'Normal',
				'Bold',
			),
			'Arial'     => array(
				'Normal',
				'Bold',
			),
			'Times'     => array(
				'Normal',
				'Bold',
			),
			'Courier'   => array(
				'Normal',
				'Bold',
			),
		);

		/**
		 * Array with Google Fonts.
		 *
		 * @var array
		 */
		public static $google = array();

		/**
		 * Array with a list of default fonts.
		 *
		 * @var array
		 */
		public static $type_kit_font = array();

		/**
		 * Array with a list of default fonts.
		 *
		 * @var array
		 */
		public static $cp_custom_fonts = array();

		/**
		 * Get font list for dropdown
		 *
		 * @since  0.0.1
		 * @param string $family font Family name.
		 * @return boolean
		 */
		public static function is_google_font( $family ) {
			self::$google = self::cp_get_updated_google_fonts();

			if ( isset( self::$google[ $family ] ) ) {
				return true;
			}

			return false;
		}

	}
}
