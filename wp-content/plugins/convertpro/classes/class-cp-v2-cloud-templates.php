<?php
/**
 * Cp V2 Templates initial setup
 *
 * @package ConvertPro
 */

if ( ! class_exists( 'CP_V2_Cloud_Templates' ) ) {

	/**
	 * Class CP_V2_Cloud_Templates.
	 */
	class CP_V2_Cloud_Templates {

		/**
		 * Class instance.
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Cloud URL.
		 *
		 * @var cloud_url
		 */
		private static $cloud_url;

		/**
		 * Module Types.
		 *
		 * @var module_types
		 */
		private static $module_types;

		/**
		 * ConvertPro filesystem.
		 *
		 * @var cp_filesystem
		 */
		protected static $cp_filesystem = null;

		/**
		 *  Initiator
		 *
		 * @since 0.0.1
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new CP_V2_Cloud_Templates();
			}
			return self::$instance;
		}

		/**
		 * Constructor function that initializes required actions and hooks
		 *
		 * @since 0.0.1
		 */
		public function __construct() {
			self::$module_types = array(
				'modal_popup',
				'info_bar',
				'slide_in',
				'before_after',
				'inline',
				'widget',
				'full_screen',
				'welcome_mat',
			);

			self::$cloud_url = array(
				'templates'     => 'http://templates.convertplug.com/wp-json/convertplug/v2/templates/',
				'template_meta' => 'http://templates.convertplug.com/wp-json/convertplug/v2/template_meta/',
			);

			self::$cloud_url = apply_filters( 'cp_v2_template_cloud_api', self::$cloud_url );

			add_action( 'wp_ajax_cp_v2_refresh', array( $this, 'refresh_cloud_templates' ) );
			add_action( 'wp_ajax_cp_v2_download', array( $this, 'download_cloud_templates' ) );
			add_action( 'wp_ajax_cp_v2_use_this', array( $this, 'use_this_cloud_template' ) );
			add_action( 'wp_ajax_cp_v2_remove_data', array( $this, 'remove_local_templates' ) );
		}

		/**
		 * Update Categories
		 *
		 * @since 0.0.1
		 */
		public static function get_popup_categories() {

			$categories = get_site_option( '_cp_v2_template_categories', false );

			if ( ! $categories ) {

				$categories = self::update_popup_categories();

				if ( ! is_array( $categories ) ) {
					$categories = array(
						'' => __( 'Select Your Goal', 'convertpro' ),
					);
				}
			}

			return $categories;
		}

		/**
		 * Update Categories
		 *
		 * @param Boolean $return_value return value.
		 * @since 0.0.1
		 */
		public static function update_popup_categories( $return_value = true ) {

			$categories = array();

			$url       = self::$cloud_url['templates'] . 'popup_categories';
			$https_url = $url;
			$ssl       = wp_http_supports( array( 'ssl' ) );

			if ( $ssl ) {
				$https_url = set_url_scheme( $https_url, 'https' );
			}

			$response = wp_remote_get(
				$https_url,
				array(
					'timeout' => 30,
				)
			);

			if ( $ssl && is_wp_error( $response ) ) {

				$response = wp_remote_get(
					$url,
					array(
						'timeout' => 30,
					)
				);
			}

			if ( is_wp_error( $response ) ) {

				if ( function_exists( 'cp_get_popup_categories' ) ) {
					$categories = cp_get_popup_categories();
				}
			} else {
				$categories = json_decode( wp_remote_retrieve_body( $response ), 1 );
			}

			update_site_option( '_cp_v2_template_categories', $categories );

			if ( $return_value ) {
				return $categories;
			}
		}

		/**
		 * CHeck transient if not set update it;
		 *
		 * @since 0.0.1
		 */
		public static function check_cloud_transient() {

			$templates = get_site_option( '_cp_v2_cloud_templates', array() );

			if ( $templates ) {
				return;
			}

			$modules = self::$module_types;

			foreach ( $modules as $module ) {
				self::reset_cloud_transient( $module );
			}
		}

		/**
		 * Reset cloud templates transient of type
		 *
		 * @param string $type type.
		 * @since 0.0.1
		 */
		public static function reset_cloud_transient( $type ) {

			$downloaded_templates = get_site_option( '_cp_v2_cloud_templates', array() );
			$cloud_templates      = $downloaded_templates;

			if ( in_array( $type, self::$module_types, true ) ) {

				$url       = self::$cloud_url['templates'] . $type;
				$https_url = $url;
				$ssl       = wp_http_supports( array( 'ssl' ) );

				if ( $ssl ) {
					$https_url = set_url_scheme( $https_url, 'https' );
				}

				$response = wp_remote_get(
					$https_url,
					array(
						'timeout' => 30,
					)
				);

				if ( $ssl && is_wp_error( $response ) ) {

					$response = wp_remote_get(
						$url,
						array(
							'timeout' => 30,
						)
					);
				}

				if ( is_wp_error( $response ) ) {
					$type_templates = 'wp_error';
				} else {
					$type_templates = json_decode( wp_remote_retrieve_body( $response ), 1 );
				}

				/**
				 *  Has {cloud} && has {downloaded}
				 *
				 *  Then, keep latest & installed templates.
				 */
				if ( ( is_array( $type_templates ) && count( $type_templates ) > 0 ) ) {

					/**
					 * Handle unexpected JSON response
					 */
					if (
						array_key_exists( 'code', $type_templates ) ||
						array_key_exists( 'message', $type_templates ) ||
						array_key_exists( 'data', $type_templates )
					) {
						return;
					}

					foreach ( $type_templates as $key => $template ) {
						$cloud_templates[ $type ][ $key ] = $type_templates[ $key ];

						if ( isset( $downloaded_templates[ $type ][ $key ]['download_status'] ) ) {
							$cloud_templates[ $type ][ $key ]['download_status'] = 'success';

							$template_data = array(
								'template_id'   => $key,
								'template_type' => $type,
							);

							self::update_downloaded_template_meta( $template_data );
						}
					}
				} else {

					return;
				}
			} else {

				return;
			}

			/**
			 * Finally update the cloud templates
			 *
			 * So, used update_site_option() to update network option '_uabb_cloud_templats'
			 */
			update_site_option( '_cp_v2_cloud_templates', $cloud_templates );
		}

		/**
		 * Get cloud templates
		 *
		 * @param string $type type.
		 * @since 0.0.1
		 */
		public static function get_cloud_templates( $type = '' ) {

			$templates = get_site_option( '_cp_v2_cloud_templates' );

			if ( ! empty( $templates ) ) {

				// Return all templates.
				if ( empty( $type ) ) {
					return $templates;

					// Return specific templates.
				} else {
					if ( isset( $templates[ $type ] ) ) {
						return $templates[ $type ];
					}
					return array();
				}
			} else {
				return array();
			}
		}

		/**
		 * Get template meta
		 *
		 * @param string $id id.
		 * @since 0.0.1
		 */
		public static function get_cloud_template_meta( $id = '' ) {

			$templates = get_site_option( '_cp_v2_template_styles', false );

			if ( ! empty( $templates ) && ! empty( $id ) ) {

				// Return specific templates.
				return $templates[ $id ];
			} else {
				return array();
			}

		}

		/**
		 * Fetch cloud templates
		 *
		 * @since 0.0.1
		 */
		public function refresh_cloud_templates() {
			check_ajax_referer( 'cpro_refresh_cloud', 'security' );

			if ( ! current_user_can( 'access_cp_pro' ) ) {
				$data = array(
					'message' => __( 'You are not authorized to perform this action.', 'convertpro' ),
				);
				wp_send_json_error( $data );
			}

			$template_type = ( isset( $_POST['template_type'] ) ) ? esc_attr( $_POST['template_type'] ) : '';
			self::reset_cloud_transient( $template_type );
			self::update_popup_categories( false );
			$ajax_result['status'] = 'success';

			echo wp_json_encode( $ajax_result );
			die();
		}

		/**
		 * Update downloaded template meta
		 *
		 * @param string $data data.
		 * @since 0.0.1
		 */
		public static function update_downloaded_template_meta( $data ) {

			$template_id   = ( isset( $data['template_id'] ) ) ? (int) $data['template_id'] : 0;
			$template_type = ( isset( $data['template_type'] ) ) ? $data['template_type'] : '';

			/* Return Failed Status */
			if ( $template_id <= 0 || empty( $template_type ) ) {
				return;
			}

			$url       = self::$cloud_url['template_meta'] . $template_id;
			$https_url = $url;
			$ssl       = wp_http_supports( array( 'ssl' ) );

			if ( $ssl ) {
				$https_url = set_url_scheme( $https_url, 'https' );
			}

			$response = wp_remote_get(
				$https_url,
				array(
					'timeout' => 30,
				)
			);

			if ( $ssl && is_wp_error( $response ) ) {

				$response = wp_remote_get(
					$url,
					array(
						'timeout' => 30,
					)
				);
			}

			/* Return Failed Status */
			if ( is_wp_error( $response ) || 404 === $response['response']['code'] ) {
				return;
			}

			$template_meta = json_decode( wp_remote_retrieve_body( $response ), 1 );

			/* Return Failed Status */
			if ( null === $template_meta ) {
				return;
			}

			/* Updated Style Meta in Local */
			$styles                 = get_site_option( '_cp_v2_template_styles', array(), false );
			$styles[ $template_id ] = $template_meta;
			update_site_option( '_cp_v2_template_styles', $styles );

			/* Return Success Status */
			return; // phpcs:ignore Squiz.PHP.NonExecutableCode.ReturnNotRequired
		}

		/**
		 * Download cloud templates
		 *
		 * @since 0.0.1
		 */
		public function download_cloud_templates() {
			check_ajax_referer( 'cpro_download_cloud', 'security' );

			if ( ! current_user_can( 'access_cp_pro' ) ) {

				$data = array(
					'message' => __( 'You are not authorized to perform this action', 'convertpro' ),
				);
				wp_send_json_error( $data );
			}

			if ( ! class_exists( 'BSF_License_Manager' ) ) {
				$data = array(
					'message' => __( "Couldn't validate your license to download this template.", 'convertpro' ),
				);
				wp_send_json_error( $data );
			}

			if ( ! BSF_License_Manager::instance()->bsf_is_active_license( 'convertpro' ) ) {

				$license_page_link = CP_V2_Tab_Menu::get_page_url( 'general-settings' ) . '#license';

				$license_page_link = '<a target="_blank" rel="noopener" href="' . esc_url( $license_page_link ) . '">' . __( 'here', 'convertpro' ) . '</a>';

				$data = array(
					/* translators: %s: License page link */
					'message' => sprintf( __( 'Please validate your license to download this template. Click %s to validate.', 'convertpro' ), $license_page_link ),
				);
				wp_send_json_error( $data );
			}

			$template_id           = isset( $_POST['template_id'] ) ? (int) $_POST['template_id'] : 0;
			$template_type         = isset( $_POST['template_type'] ) ? sanitize_text_field( $_POST['template_type'] ) : '';
			$ajax_result['status'] = 'failed';
			$purchase_key          = BSF_License_Manager::instance()->bsf_get_product_info( 'convertpro', 'purchase_key' );

			/* Return Failed Status */
			if ( $template_id <= 0 || empty( $template_type ) ) {
				echo wp_json_encode( $ajax_result );
				die();
			}

			$url       = self::$cloud_url['template_meta'];
			$https_url = $url;
			$ssl       = wp_http_supports( array( 'ssl' ) );

			if ( $ssl ) {
				$https_url = set_url_scheme( $https_url, 'https' );
			}

			$response = wp_remote_post(
				$https_url,
				array(
					'timeout' => 30,
					'body'    => array(
						'id'  => $template_id,
						'key' => $purchase_key,
					),
				)
			);

			if ( $ssl && is_wp_error( $response ) ) {

				$response = wp_remote_post(
					$url,
					array(
						'timeout' => 30,
						'body'    => array(
							'id'  => $template_id,
							'key' => $purchase_key,
						),
					)
				);
			}

			/* Return Failed Status */
			if ( is_wp_error( $response ) || 404 === $response['response']['code'] ) {
				echo wp_json_encode( $ajax_result );
				die();
			}

			$template_meta = json_decode( wp_remote_retrieve_body( $response ), 1 );

			/* Return Failed Status */
			if ( null === $template_meta ) {
				/* Return Failed Status */
				echo wp_json_encode( $ajax_result );
				die();
			}

			$modal_data = json_decode( $template_meta['cp_modal_data'] );

			$modal_data = $this->process_modal_data( $modal_data );

			$template_meta['cp_modal_data'] = wp_json_encode( $modal_data );

			/* Updated Style Meta in Local */
			$styles                 = get_site_option( '_cp_v2_template_styles', array() );
			$styles[ $template_id ] = $template_meta;
			update_site_option( '_cp_v2_template_styles', $styles );

			/* Update Download Status in Local */
			$templates = get_site_option( '_cp_v2_cloud_templates', array() );
			$templates[ $template_type ][ $template_id ]['download_status'] = 'success';
			update_site_option( '_cp_v2_cloud_templates', $templates );

			/* Return Success Status */
			$ajax_result['status'] = 'success';
			echo wp_json_encode( $ajax_result );
			die();
		}

		/**
		 * Process modal data to modify URLs
		 *
		 * @param string $modal_data modal_data.
		 *
		 * @since 1.1.7
		 */
		public function process_modal_data( $modal_data ) {

			foreach ( $modal_data as $key => $value ) {

				foreach ( $value as $nested_key => $nested_value ) {

					if ( isset( $nested_value->panel_bg_image ) ) {

						if ( is_array( $nested_value->panel_bg_image ) ) {

							$panel_bg_image = $nested_value->panel_bg_image;

							if ( is_array( $panel_bg_image ) ) {
								foreach ( $panel_bg_image as $img_key => $img_meta ) {

									$panel_bg_image = explode( '|', $img_meta );

									if ( isset( $panel_bg_image[1] ) && '0' !== $panel_bg_image[0] ) {

										$img_meta = $this->get_image_meta( $panel_bg_image );

										// @codingStandardsIgnoreStart
										$modal_data->$key->$nested_key->panel_bg_image[ $img_key ] = $img_meta['path'];

										if( isset( $modal_data->$key->$nested_key->panel_bg_image_sizes ) && is_object( $modal_data->$key->$nested_key->panel_bg_image_sizes ) ) {
											$modal_data->$key->$nested_key->panel_bg_image_sizes = array($modal_data->$key->$nested_key->panel_bg_image_sizes);
										}
										$modal_data->$key->$nested_key->panel_bg_image_sizes[ $img_key ] = $img_meta['sizes'];
										// @codingStandardsIgnoreEnd
									}
								}
							}
						} else {

							$panel_bg_image = explode( '|', $nested_value->panel_bg_image );

							if ( isset( $panel_bg_image[1] ) && '0' !== $panel_bg_image[0] ) {
								$img_meta = $this->get_image_meta( $panel_bg_image );

								// @codingStandardsIgnoreStart
								$modal_data->$key->$nested_key->panel_bg_image = $img_meta['path'];
								$modal_data->$key->$nested_key->panel_bg_image_sizes = $img_meta['sizes'];
								// @codingStandardsIgnoreEnd

							}
						}
					}

					if ( isset( $nested_value->module_image ) ) {

						$module_image = explode( '|', $nested_value->module_image );

						if ( isset( $module_image[1] ) && '0' !== $module_image[0] ) {
							$img_meta = $this->get_image_meta( $module_image );

							// @codingStandardsIgnoreStart
							$modal_data->$key->$nested_key->module_image = $img_meta['path'];
							$modal_data->$key->$nested_key->module_image_sizes = $img_meta['sizes'];
							// @codingStandardsIgnoreEnd
						}
					}
				}
			}

			return $modal_data;
		}

		/**
		 * Get image path and sizes array
		 *
		 * @param array $image image details array.
		 *
		 * @since 1.1.7
		 */
		public function get_image_meta( $image ) {

			$img_url = $image[1];

			$attach_id = $this->upload_remote_image_and_attach( $img_url );

			$sizes      = wp_get_attachment_metadata( $attach_id );
			$upload_dir = wp_upload_dir();
			$img_sizes  = array();

			if ( isset( $sizes['sizes'] ) ) {
				foreach ( $sizes['sizes'] as $key => $size ) {
					$img_sizes[ $key ] = array(
						'url'    => $upload_dir['url'] . '/' . $size['file'],
						'height' => $size['height'],
						'width'  => $size['width'],
					);
				}
			}

			$url = wp_get_attachment_url( $attach_id );

			$image[0] = $attach_id;
			$image[1] = $url;

			$img_meta = implode( '|', $image );

			return array(
				'path'  => $img_meta,
				'sizes' => $img_sizes,
			);

		}

		/**
		 * Process modal data to modify URLs
		 *
		 * @param string $image image details.
		 *
		 * @since 1.1.7
		 */
		public function upload_remote_image_and_attach( $image ) {

			$image = str_replace( array( 'http:', 'https:' ), '', $image );
			$image = str_replace( '//', 'http://', $image );
			$get   = wp_remote_get( $image );

			$type = wp_remote_retrieve_header( $get, 'content-type' );

			if ( ! $type ) {
				return false;
			}

			$mirror = wp_upload_bits( basename( $image ), null, wp_remote_retrieve_body( $get ) );

			$attachment = array(
				'post_title'     => basename( $image ),
				'post_mime_type' => $type,
			);

			$attach_id = wp_insert_attachment( $attachment, $mirror['file'] );

			require_once ABSPATH . 'wp-admin/includes/image.php';

			$attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );

			wp_update_attachment_metadata( $attach_id, $attach_data );

			return $attach_id;

		}

		/**
		 * Use this cloud templates
		 *
		 * @since 0.0.1
		 */
		public function use_this_cloud_template() {
			check_ajax_referer( 'cpro_create_new', 'security' );
			if ( ! current_user_can( 'access_cp_pro' ) ) {
				$data = array(
					'message' => __( 'You are not authorized to perform this action.', 'convertpro' ),
				);
				wp_send_json_error( $data );
			}

			$template_id   = isset( $_POST['template_id'] ) ? (int) $_POST['template_id'] : 0;
			$template_type = isset( $_POST['template_type'] ) ? sanitize_text_field( $_POST['template_type'] ) : 'modal_popup';
			$template_name = isset( $_POST['template_name'] ) ? sanitize_text_field( $_POST['template_name'] ) : 'Style 1';
			$style_title   = ( isset( $_POST['style_title'] ) && ! empty( $_POST['style_title'] ) ) ? sanitize_text_field( $_POST['style_title'] ) : $template_name;

			if ( 0 !== $template_id ) {
				$template_meta = self::get_cloud_template_meta( $template_id );
			}

			$style_id = '';

			// Gather post data.
			$cp_popup_post = array(
				'post_title'   => $style_title,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => CP_CUSTOM_POST_TYPE,
			);

			// Insert the post into the database.
			$style_id = wp_insert_post( $cp_popup_post );

			update_post_meta( $style_id, 'cp_module_type', $template_type );

			if ( $style_id > 0 ) {

				if ( 0 !== $template_id ) {
					foreach ( $template_meta as $key => $value ) {

						if ( is_string( $value ) ) {
							$value = addslashes( $value );
						}

						update_post_meta( $style_id, $key, $value );
					}
				}

				$term = term_exists( 'Your Designs', CP_CAMPAIGN_TAXONOMY );

				if ( 0 === $term || null === $term ) {

					$campaign_id = cp_create_campaign( 'Your Designs' );

					if ( ! is_wp_error( $campaign_id ) ) {

						$result = wp_set_object_terms( $style_id, $campaign_id, CP_CAMPAIGN_TAXONOMY, false );

						if ( is_wp_error( $result ) ) {
							wp_send_json_error();
						}
					} else {
						wp_send_json_error();
					}
				} else {

					$result = wp_set_object_terms( $style_id, (int) $term['term_id'], CP_CAMPAIGN_TAXONOMY, false );

					if ( is_wp_error( $result ) ) {
						wp_send_json_error();
					}
				}

				$ajax_result['status']   = 'success';
				$ajax_result['redirect'] = get_edit_post_link( $style_id, '' ) . '&type=' . $template_type . '&popup_title=' . $style_title;

				wp_send_json_success( $ajax_result );
			}

			$ajax_result['status'] = 'failed';
			wp_send_json_success( $ajax_result );
		}


		/**
		 * Remove Local Templates Data
		 *
		 * @since 0.0.1
		 */
		public function remove_local_templates() {
			check_ajax_referer( 'cpro_delete_template_data', 'security' );
			if ( ! current_user_can( 'access_cp_pro' ) ) {
				$data = array(
					'message' => __( 'You are not authorized to perform this action.', 'convertpro' ),
				);
				wp_send_json_error( $data );
			}

			delete_site_option( '_cp_v2_template_styles' );
			delete_site_option( '_cp_v2_cloud_templates' );
			delete_site_option( '_cp_v2_template_categories' );
		}
	}
}

/**
*  Kicking this off by calling 'get_instance()' method
*/
$cp_v2_cloud_templates = CP_V2_Cloud_Templates::get_instance();
