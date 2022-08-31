<?php
/**
 * Style options.
 *
 * @package ConvertPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'cp_style_dashboard' ) ) {

	/**
	 * Function Name: cp_style_dashboard.
	 * Function Description: Cp Style Dashboard.
	 *
	 * @param string $class string parameter.
	 * @param string $type string parameter.
	 */
	function cp_style_dashboard( $class, $type ) {

		if ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && ! wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) ) {
			die( 'No direct script access allowed!' );
		}

		$url            = '';
		$cp_module_type = $type;
		$type           = explode( '_', $type );
		$type           = array_map(
			function( $word ) {
				return ucfirst( $word );
			},
			$type
		);
		$class          = 'CP_' . implode( '_', $type );
		$type_object    = new $class();
		$settings       = $type_object->get_options();
		$style_id       = isset( $_GET['post'] ) ? esc_attr( sanitize_text_field( $_GET['post'] ) ) : get_the_ID();

		$style_id = apply_filters( 'cpro_call_to_action_id', $style_id );

		if ( empty( $style_id ) ) {
			$style_name = isset( $_GET['popup_title'] ) ? esc_attr( $_GET['popup_title'] ) : 'Style 1';
		} else {

			$style_name = get_the_title( $style_id );
			if ( 'Auto Draft' === $style_name ) {
				$style_name = 'Style 1';
			}
		}

		$style_status = 0;
		if ( '' !== $style_id ) {
			$style_status = get_post_meta( $style_id, 'live', true );
		}

		$cp_framework_inst            = new Cp_Framework();
		$styles                       = array();
		$style_view                   = 'edit';
		$style                        = '';
		$has_active_ab_test['status'] = false;
		if ( class_exists( 'CP_V2_AB_Test' ) ) {
			$ab_test_inst       = CP_V2_AB_Test::get_instance();
			$has_active_ab_test = $ab_test_inst->has_abtest_running( $style_id );
		}

		if ( ! empty( $settings ) ) {

			$sections_array = array();
			$new_options    = $settings['options'];
			$panel_list     = array();
			foreach ( $new_options as $key => $values ) {
				$temp_panel = array();
				$panel      = ( isset( $values['panel'] ) ) ? $values['panel'] : '';
				$section    = ( isset( $values['section'] ) ) ? $values['section'] : '';
				$panel_icon = ( isset( $values['section_icon'] ) ) ? $values['section_icon'] : false;
				$tags       = ( isset( $values['tags'] ) ) ? $values['tags'] : false;
				if ( ! in_array( $panel, $panel_list, true ) ) {
					$panel_data           = array(
						'panel'   => $panel,
						'section' => $section,
						'icon'    => $panel_icon,
					);
					$panel_list[ $panel ] = $panel_data;
				}

				$key        = strtolower( $section );
				$section_id = preg_replace( '![^a-z0-9]+!i', '-', $key );

				$section_icon = ( isset( $values['section_icon'] ) ) ? $values['section_icon'] : false;
				if ( ! isset( $sections_array[ $section ]['panels'][ $panel ] ) ) {
					$sections_array[ $section ]['panels'][ $panel ] = array();
				}
				array_push( $sections_array[ $section ]['panels'][ $panel ], $values );
				$sections_array[ $section ]['section_id'] = $section_id;
				if ( $section_icon ) {
					$sections_array[ $section ]['icon'] = $section_icon;
				}
			}
		}

		$mobile_resp      = get_post_meta( $style_id, 'cp_mobile_responsive', true ) !== false ? 'yes' : 'no';
		$mobile_generated = get_post_meta( $style_id, 'cp_mobile_generated', true ) !== false ? 'yes' : 'no';

		?>
		<div class="tooltip-wrapper right"><span class="stencil-tooltip" id="stencil-tooltip"></span></div>
		<div class="cp-horizontal-nav-bar" id="customize-footer-actions">
			<div class="cp-horizontal-nav-first">
				<div class="cp-horizontal-nav-top cp-customize-section">
					<?php $cp_count_inc = 1; ?>
					<?php
					foreach ( $sections_array as $key => $sections ) {

						switch ( strtolower( $key ) ) {
							case 'design':
								$section_name = 'Design';
								break;
							case 'configure':
								$section_name = 'Configure';
								break;
							case 'connect':
								$section_name = 'Connect';
								break;
						}

						$section_id   = ( isset( $sections['section_id'] ) ) ? $sections['section_id'] : '';
						$section_icon = ( isset( $sections['icon'] ) ) ? $sections['icon'] : '';
						if ( 'configure' === $section_id && true === $has_active_ab_test['status'] && $has_active_ab_test['cp_parent_style'] !== $style_id ) {
							continue;
						}

						?>
						<a href="#<?php echo esc_attr( $section_id ); ?>" data-section="<?php echo esc_attr( $section_name ); ?>" class="cp-section <?php echo esc_attr( $class ); ?>" data-section-id="<?php echo esc_attr( $section_id ); ?>">
								<span class="cp-tooltip-icon" data-position="bottom">
									<span class="cp-menu-icon"><?php echo esc_html( $cp_count_inc ); ?></span>
									<button><?php echo esc_html( $section_name ); ?></button>
								</span>
						</a>
						<?php
						$cp_count_inc++;
					}
					?>
				</div>
			</div>
			<div class="cp-view-wrap">
				<div class="cp-view-panel">
					<span class="cp-setting-menu">

						<a href="#" class="cp-info cp-setting-wrap cp-section cp-advanced-setting has-tip" id="cp-advanced-setting" data-position="right" title="Advanced" data-style="<?php echo esc_attr( $style ); ?>">
							<span class="cp-horizontal-nav-icon"><i class="dashicons dashicons-admin-generic"></i></span> 
						</a>
						<div id="cp-setting-panel">
							<?php
							do_action( 'cp_before_setting_panel_options' );

							if ( 'full_screen' !== $cp_module_type && 'welcome_mat' !== $cp_module_type ) {
								?>
								<a href="#" class="cp-section cp-active-style cp-multisteps-setting" data-section-id="multisteps">
								<span class="cp-multistep-nav-title"><span class="dashicons dashicons-yes cp-hide-icons"></span> <?php esc_html_e( 'Multi Step', 'convertpro' ); ?></span>
								<?php } ?>
								</a>

								<a href="#" class="cp-section cp-active-style cp-mobile-responsive <?php echo 'yes' === $mobile_resp ? 'cp-active-link-color' : ''; ?>" data-mobile-resp="<?php echo esc_attr( $mobile_resp ); ?>" data-section-id="mobile-resp">
								<span class="cp-mobile-resp-nav-title"><span class="dashicons dashicons-yes cp-hide-icons"></span> <?php esc_html_e( 'Mobile Editor', 'convertpro' ); ?></span>
							</a>
							<?php do_action( 'cp_after_setting_panel_options' ); ?>
						</div>
					</span>
					<a href="#" class="cp-responsive-device cp-desktop-device <?php echo 'yes' === $mobile_resp ? '' : 'cp-hidden'; ?>" data-device="desktop">
						<span class="dashicons dashicons-desktop"></span>
					</a>
					<a href="#" class="cp-responsive-device cp-ur-disabled <?php echo 'yes' === $mobile_resp ? '' : 'cp-hidden'; ?>" data-device="mobile">
						<span class="dashicons dashicons-smartphone"></span>
					</a>
					<a href="#" class="cp-regenerate-mobile"><?php esc_html_e( 'Regenerate Mobile View', 'convertpro' ); ?></a>


				</div>
			</div>
			<div class="cp-horizontal-nav-action-wrapper">
				<a class="cp-ur-buttons cp-undo-button cp-ur-disabled" href="#">
					<span class="dashicons dashicons-image-rotate"></span>
					<span class="cp-ur-text"><?php esc_html_e( 'Undo', 'convertpro' ); ?></span>
				</a>
				<a class="cp-ur-buttons cp-redo-button cp-ur-disabled" href="#">
					<span class="dashicons dashicons-image-rotate"></span>
					<span class="cp-ur-text"><?php esc_html_e( 'Redo', 'convertpro' ); ?></span>
				</a>

				<?php
				$branding   = Cp_V2_Loader::get_branding();
				$kbval      = ( isset( $branding['kb_enabled'] ) && '1' === $branding['kb_enabled'] ) ? 1 : 0;
				$supportval = ( isset( $branding['support_enabled'] ) && '1' === $branding['support_enabled'] ) ? 1 : 0;
				$hide_help  = ( ! ( $kbval || $supportval ) ) ? 'cp-hidden' : '';
				?>
				<span class="cp-question-dropdown <?php echo esc_attr( $hide_help ); ?>">
					<a href="#" class="cp-question-dropbtn cp-section cp-help has-tip" id="cp-get-help" data-position="left" title="Help" data-style="<?php echo esc_attr( $style ); ?>">
						<span class="cp-horizontal-nav-icon"><i class="cp-icon-question"></i></span> 
					</a>
					<div id="cp-question-dropdown" class="cp-question-content">
						<?php
						if ( $kbval ) {
							$kb_url = ( '' !== $branding['kb_url'] ) ? $branding['support_url'] : esc_url( CP_KNOWLEDGE_BASE_URL . '?utm_source=wp-dashboard&utm_medium=customizer&utm_campaign=knowledge-base' );
							?>
							<a rel="noopener" href="<?php echo esc_attr( esc_url( $kb_url ) ); ?>" target="_blank">
							<span class="cp-question-icon"><i class="dashicons dashicons-media-interactive"></i></span>
							<span class="cp-question-title"><?php esc_html_e( 'Knowledge base', 'convertpro' ); ?></span>
						</a>
							<?php
						}
						if ( $supportval ) {
							$support_url = ( '' !== $branding['support_url'] ) ? $branding['support_url'] : esc_url( CP_SUPPORT_URL . '?utm_source=wp-dashboard&utm_medium=customizer&utm_campaign=request-support' );
							?>
							<a href='<?php echo esc_attr( esc_url( $support_url ) ); ?>' target='_blank'>
							<span class="cp-question-icon"><i class="dashicons dashicons-email-alt"></i></span>
							<span class="cp-question-title"><?php esc_html_e( 'Submit a ticket', 'convertpro' ); ?></span>
						</a>
						<?php } ?>
					</div>
				</span>
				<a href="javascript:void(0)" class="cp-save" data-is-abtest="<?php echo ( true === $has_active_ab_test['status'] && $has_active_ab_test['cp_parent_style'] === $style_id ) ? 1 : 0; ?>">
					<span class="cp-horizontal-nav-title"><?php esc_html_e( 'Save', 'convertpro' ); ?></span>
				</a>
				<div class="cp-mapping-notice"></div>
				<div class="cp-info-slide-error-notice"></div>
				<div class="cp-view-status">

				<?php
					$home_page_url = CP_V2_Tab_Menu::get_page_url( 'dashboard' );
					$uid           = uniqid();
				?>
					<div class="cp-switch-wrapper">

					<?php
					if ( true === $has_active_ab_test['status'] ) {
						?>
							<label class="cp-style-abtest-running"><?php esc_html_e( 'A/B Test is running', 'convertpro' ); ?></label>
							<?php
					} else {
						$post_style_id = ( isset( $_GET['post'] ) ) ? intval( $_GET['post'] ) : '';
						?>
						<input type="text" id="cp_style_status" class="form-control cp-input cp-switch-input" name="style_status" data-style="<?php echo esc_attr( $post_style_id ); ?>" value="<?php echo esc_attr( $style_status ); ?>">
						<?php wp_nonce_field( 'cpro_publish', 'cpro_publish_new' ); ?>
						<input type="checkbox" <?php checked( $style_status, 1 ); ?> id="cp_style_status_btn_58d50f40d341a" class="ios-toggle cp-input cp-switch-input switch-checkbox cp-switch" value="1">
						<label class="cp-switch-btn checkbox-label" data-on="Published" data-off="Make Public" data-id="cp_style_status" for="cp_style_status_btn_58d50f40d341a"></label> 
						<?php
					}
					?>
					</div>
				</div>
				<span class="cp-close-menu">
					<a href="#" id="cp-close-icon">
						<i class="dashicons dashicons-no-alt"></i>
					</a>
					<div id="cp-close-panel">
						<a target="_blank" rel="noopener" href="<?php echo esc_url( home_url( '/' ) ); ?>" data-section-id="visit-site">
							<span class="cp-visit-site-nav-title">
								<span class="cp-close-style dashicons dashicons-external"></span><?php esc_html_e( 'View Site', 'convertpro' ); ?>
							</span>
						</a>
						<a href=<?php echo esc_attr( esc_url( $home_page_url ) ); ?> data-section-id="goto-dashboard">
							<span class="cp-dashboard-nav-title">
								<span class="cp-close-style dashicons dashicons-wordpress-alt"></span><?php esc_html_e( 'Go To Dashboard', 'convertpro' ); ?>
							</span>
						</a>
					</div>
				</span>
			</div>
		</div><!-- cp-horizontal-nav-bar -->
		<div class="customizer-wrapper cp-customizer-wrapper" style="display: none;">
			<div class="cp-edit-panel ep-draggable">
				<div class="cp-edit-panel-wrapper">
					<h3 class="cp-edit-element-title"><?php esc_html_e( 'Element Settings', 'convertpro' ); ?></h3>
					<div class="cp-edit-panel-content" id="cp-edit-panel-contents"></div>
					<div class="cp-edit-actions">
						<a class="cp-btn-default cp-primary-button cp-edit-popup-btn" href="#" data-type="close"><?php esc_html_e( 'Done', 'convertpro' ); ?>
						</a>
					</div>
				</div>
			</div>
			<div id="cp-designer-form" class="cp-panel-container">
				<form class="cp-cust-form" data-action="cp_update_style_settings" >
					<input type="hidden"  id="cp_style_id" value="<?php echo intval( $style_id ); ?>" />
					<input type="hidden"  id="cp_mobile_responsive" value="<?php echo esc_attr( $mobile_resp ); ?>" />
					<input type="hidden"  id="cp_mobile_generated" value="<?php echo esc_attr( $mobile_generated ); ?>" />
					<?php
					$param_fields_dir = CP_V2_BASE_DIR . 'framework/params';
					$results          = scandir( $param_fields_dir );
					$cp_params        = array();
					// iterate through all param directories.
					foreach ( $results as $result ) {

						if ( '.' === $result || '..' === $result ) {
							continue;
						}

						$param_file_name = str_replace( '_', '-', $result );

						$filepath = $param_fields_dir . '/' . $result . '/' . $param_file_name . '.php';

						if ( ! file_exists( $filepath ) ) {
							continue;
						}

						ob_start();
						include $filepath;
						$contents    = ob_get_clean();
						$cp_params[] = json_decode( $contents );
					}
					do_action( 'cp_after_load_params' );

					$param_data = wp_json_encode( $cp_params );
					$fields_dir = CP_V2_BASE_DIR . 'framework/fields';
					$results    = scandir( $fields_dir );
					$cp_fields  = array();

					cp_load_filesystem();
					global $cp_pro_filesystem;
					// iterate through all param directories.
					if ( is_array( $results ) ) {
						foreach ( $results as $result ) {

							if ( '.' === $result || '..' === $result || 'index.php' === $result ) {
								continue;
							}

							$filepath = $fields_dir . '/' . $result . '/template.html';

							if ( file_exists( $filepath ) ) {

								echo "<script type='text/template' class='cpro-field-template' id='field-template-" . esc_attr( $result ) . "'>";
								ob_start();
								$file_contents = $cp_pro_filesystem->get_contents(
									$filepath,
									FS_CHMOD_FILE
								);
								echo $file_contents; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
								$contents = ob_get_clean();
								echo $contents; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
								echo '</script>';
							}
						}
					}

					do_action( 'cp_after_load_fields' );

					$modal_data = get_post_meta( $style_id, 'cp_modal_data', true );

					$camp_ids = array();

					$cp_gfonts      = get_post_meta( $style_id, 'cp_gfonts', true );
					$cp_global_font = Cp_V2_Model::get_cp_global_fonts();

					if ( 'null' !== $cp_gfonts ) {

						$temp_cp_gfonts = json_decode( $cp_gfonts, true );

						if ( CP_V2_Fonts::is_google_font( $cp_global_font['family'] ) ) {
							$temp_cp_gfonts['panel_global_font'] = array(
								'family' => $cp_global_font['family'],
								'weight' => $cp_global_font['weight'],
							);

						} elseif ( isset( $temp_cp_gfonts['panel_global_font'] ) ) {
							unset( $temp_cp_gfonts['panel_global_font'] );
						}

						$cp_gfonts = wp_json_encode( $temp_cp_gfonts );
					}

					$cp_gfonts = 'null' === $cp_gfonts ? '' : $cp_gfonts;

					$cp_global_font_style  = '<style type="text/css" class="cp_global_font_css">';
					$cp_global_font_style .= '.cp-popup-wrapper .cp-popup-content {';
					$cp_global_font_style .= 'font-family:' . $cp_global_font['family'] . ';';
					$cp_global_font_style .= 'font-weight:' . $cp_global_font['weight'] . ';';
					$cp_global_font_style .= '}';
					$cp_global_font_style .= '</style>';

					echo $cp_global_font_style; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

					?>
					<input type="hidden" id="cp_style_title" value="<?php echo esc_attr( $style_name ); ?>">
					<input type="hidden" id="cp_params" value="<?php echo esc_attr( htmlspecialchars( $param_data, ENT_COMPAT, 'UTF-8' ) ); ?>">
					<input type="hidden" id="cp_params_url" value="<?php echo esc_attr( CP_V2_BASE_URL ) . 'framework/params/'; ?>">
					<input type="hidden" id="cp_fields_url" value="<?php echo esc_attr( CP_V2_BASE_URL ) . 'framework/fields/'; ?>">
					<input type="hidden" id="cp_admin_assets_url" value="<?php echo esc_attr( CP_V2_BASE_URL ) . 'assets/admin/'; ?>">
					<input type="hidden" name="cp_modal_data" id="cp_modal_data" value="<?php echo esc_attr( htmlspecialchars( $modal_data, ENT_QUOTES, 'UTF-8' ) ); ?>" >

					<input type="hidden" name="cp_gfonts" id="cp_fonts_list" value="<?php echo esc_attr( htmlspecialchars( $cp_gfonts, ENT_COMPAT, 'UTF-8' ) ); ?>" />

					<input type="hidden" name="cp-save-ajax-nonce" id="cp-save-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'cp-save-ajax-req-nonce' ) ); ?>" />
					<input type="hidden" name="cp_module_type" id="cp_module_type" value="<?php echo esc_attr( $cp_module_type ); ?>" >

					<div class="cp-vertical-nav">
						<div class="cp-vertical-nav-top cp-panel-list cp-section-container cp-customize-section">
							<span id="cp-dragger"></span>

					<?php
					foreach ( $panel_list as $key => $panel_data ) {
						$section_icon = ( isset( $panel_data['icon'] ) ) ? $panel_data['icon'] : 'dashicons dashicons-dashboard';
						$panel        = $key;
						$panel_slug   = trim( strtolower( str_replace( ' ', '_', $panel ) ) );
						$section      = $panel_data['section'];
						$section_slug = strtolower( str_replace( ' ', '-', $section ) );
						$panel_class  = '';
						if ( strtolower( $section ) !== 'design' ) {
							$panel_class = 'cp-hidden';
						}

						if ( trim( $panel_slug ) !== '' ) {
							?>
									<a href="#<?php echo esc_attr( $panel_slug ); ?>" data-panel="<?php echo esc_attr( $panel_slug ); ?>" class="cp-panel-link <?php echo esc_attr( $panel_class ); ?>" data-section-id="<?php echo esc_attr( $section_slug ); ?>">
										<span class="cp-element-panel-icon cp-tooltip-icon " data-position="right" title="<?php echo esc_attr( $panel ); ?>">
											<i class="<?php echo esc_attr( $section_icon ); ?>"></i>
											<span class="cp-panel-title"><?php echo esc_html( $panel ); ?></span>
										</span>
									</a>

									<?php
						}
					}
					?>
						</div>
						<div class="cp-vertical-nav-bottom">
							<div class="cp-bottom-icons cp-collapse-panel">
								<a href="#" class="cp-toggle-icon has-tip" data-position="right" title="Collapse">
									<i class="dashicons dashicons-arrow-left-alt"></i>
								</a>
							</div>
						</div>
					</div><!-- .cp-vertical-nav -->
					<div class="cp-customizer-tabs-wrapper <?php echo 'cp-module-type-' . esc_attr( $cp_module_type ) . '-wrap'; ?>">
						<div class="cp-section-search">
							<input type="text" id="field-search" placeholder="<?php esc_html_e( 'Search Shapes...', 'convertpro' ); ?>" />
							<span class="search-panel"><i class='dashicons dashicons-search'></i></span>
						</div>

							<?php
							$count = 0;
							foreach ( $sections_array as $key => $sections ) {

								$panels     = $sections['panels'];
								$section_id = $sections['section_id'];

								?>

							<div id="<?php echo esc_attr( $section_id ); ?>" class="cp-customizer-tab <?php echo esc_attr( $section_id ); ?>-content with-marker cp-tab-<?php echo esc_attr( $count ); ?>" data-section="<?php echo esc_attr( $section_id ); ?>" data-role="accordion" data-closeany="true">
								<?php

								$cnt = 0;
								foreach ( $panels as $panel_key => $panel ) {

									$panel_slug = str_replace( ' ', '_', strtolower( $panel_key ) );

									$columns_class = '';
									$display_title = false;
									if ( 'shapes' === $panel_slug ) {
										$columns_class = 'cp-svg-shapes';
									} elseif ( 'button' === $panel_slug ) {
										$columns_class = 'cp-preset-col-2';
									} elseif ( 'fields' === $panel_slug || 'elements' === $panel_slug ) {
										$columns_class = 'cp-field-col-2';
									} elseif ( 'text' === $panel_slug ) {
										$columns_class = 'cp-heading-col';
									} elseif ( 'form' === $panel_slug ) {
										$columns_class = 'cp-form-wrapper';
									} elseif ( 'connect' === $panel_slug ) {
										$columns_class = 'cp-connects-col';
									} elseif ( 'panel' === $panel_slug ) {
										$columns_class = 'cp-panel-wrapper';
										$display_title = true;
									} else {
										$display_title = true;
									}

									?>
									<div class="cp-panel-content clear <?php echo esc_attr( $columns_class ); ?>" data-panel="<?php echo esc_attr( $panel_slug ); ?>" >
										<div class="cp-panel-wrap">

										<?php
										if ( 'Launch' === $panel_key && ( 'modal_popup' === $cp_module_type || 'info_bar' === $cp_module_type || 'slide_in' === $cp_module_type
												|| 'welcome_mat' === $cp_module_type || 'full_screen' === $cp_module_type ) ) {
											?>
											<?php
											$default_rulset            = array();
											$default_rulset[0]['name'] = 'Ruleset 1';

											$configures         = get_post_meta( $style_id, 'configure', true );
											$configures_rulsets = isset( $configures['rulesets'] ) ? json_decode( $configures['rulesets'], true ) : array();
											$ruleset_key        = 0;

											?>
											<div class="cp-rulsets-wrap">
												<script type="text/template" id="ruleset-button-template">
													<div class="cp-rulsets" data-rulsets="{{ruleset}}">
														<div class="cp-rulset-text">
															<span>{{name}}</span>
														</div>
														<span class="cp-delete-ruleset dashicons dashicons-minus"></span>
													</div>
												</script>

											<?php

											$ruleset_hid_class = '';

											if ( 'welcome_mat' === $cp_module_type ) {
												$ruleset_hid_class = 'cp-hidden';
											}

											?>
												<div class="cpro-ruleset-info <?php echo esc_attr( $ruleset_hid_class ); ?>">
													<span class="rulset-title">
													<?php esc_html_e( 'Rulesets', 'convertpro' ); ?>
													</span>
													<span class="rulset-doc-link">
														<a target="_blank" rel="noopener" href="https://www.convertpro.net/docs/introduction-to-rules-sets-in-launch-settings-of-convert-pro" ><?php esc_html_e( 'Learn more about rulesets', 'convertpro' ); ?>				
														</a>
													</span>
													<span data-position="bottom" title="

													<?php
													esc_html_e(
														'Triggers within a Ruleset work as AND, 
Triggers from different Ruleset works as OR',
														'convertpro'
													);
													?>
" class="cp-tooltip-icon has-tip rulset-tip">
														<i class="dashicons dashicons-editor-help"></i>
													</span>
												</div>
												<div class="cp-rulsets-button <?php echo esc_attr( $ruleset_hid_class ); ?>">
													<?php
													if ( count( $configures_rulsets ) < 1 ) {

														?>
														<div class="cp-rulsets cp-rulsets-active" data-rulsets="0">
															<div class="cp-rulset-text">
																<span><?php echo esc_html( $default_rulset[0]['name'] ); ?></span>
															</div>
															<span class="cp-delete-ruleset dashicons dashicons-minus"></span>
														</div>

															<?php
													} else {
														?>
														<?php foreach ( $configures_rulsets as $ruleset_key => $ruleset ) { ?>
															<div class="cp-rulsets <?php echo ( 0 === $ruleset_key ) ? 'cp-rulsets-active' : ''; ?>" data-rulsets="<?php echo intval( $ruleset_key ); ?>">
																<div class="cp-rulset-text">

																<?php
																	$ruleset_name = isset( $ruleset['name'] ) ? $ruleset['name'] : 'Ruleset 1';
																?>
																	<span><?php echo esc_attr( $ruleset_name ); ?></span>
																</div>
																<span class="cp-delete-ruleset dashicons dashicons-minus"></span>
															</div>
														<?php } ?>
														<?php
													}
													?>
												</div>
												<div class="cp-rulset-action cp-add-ruleset <?php echo esc_attr( $ruleset_hid_class ); ?>" data-rulsets="<?php echo intval( $ruleset_key ); ?>">
													<span class="dashicons dashicons-plus"></span>
												</div>

													<?php
										}

												$html               = '';
												$categories         = array();
												$hidden_fields_html = '';
										foreach ( $panel as $key => $values ) {

											if ( isset( $values['category'] ) && '' !== $values['category'] ) {

												$category = $values['category'];
												if ( ! isset( $categories[ $category ] ) ) {
													$categories[ $category ] = array();
												}
												array_push( $categories[ $category ], $values );
											} else {

												$panel_content       = $cp_framework_inst->cp_framework_get_panel_content( $style_id, $values, $panel_slug, $display_title, $section_id );
												$html               .= $panel_content['html'];
												$hidden_fields_html .= $panel_content['hidden_fields_html'];
											}
										}
										?>

												<?php if ( is_array( $categories ) && count( $categories ) > 0 ) { ?>

													<div id="cp-accordion">

													<?php
													foreach ( $categories as $key => $category ) {

														$category_slug = strtolower( str_replace( ' ', '-', $key ) );
														?>
															<h3 class="cp-accordion-title <?php echo esc_attr( $category_slug ); ?>"><?php echo esc_html( $key ); ?></h3>
															<div class="cp-accordion-content <?php echo esc_attr( $category_slug ); ?>" data-acc-class="<?php echo esc_attr( $category_slug ); ?>">
																<div class="cp-accordion-wrap">
																<?php
																foreach ( $category as $cat_prop ) {

																	if ( 'launch' === $panel_slug ) {

																		if ( isset( $cat_prop['opts']['value'] ) ) {
																			$default_rulset[0][ $cat_prop['name'] ] = $cat_prop['opts']['value'];
																		} else {
																			$default_rulset[0][ $cat_prop['name'] ] = '';
																		}
																	}

																	$panel_content = $cp_framework_inst->cp_framework_get_panel_content( $style_id, $cat_prop, $panel_slug, $display_title, $section_id );
																	echo $panel_content['html']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
																	$hidden_fields_html .= $panel_content['hidden_fields_html'];
																}

																?>

																</div>
															</div>
														<?php } ?>
													</div><!-- #cp-accordion -->

													<?php
												}

												$html .= $hidden_fields_html;
												echo $html; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
												?>
												<?php if ( 'Launch' === $panel_key && ( 'modal_popup' === $cp_module_type || 'info_bar' === $cp_module_type || 'slide_in' === $cp_module_type || 'welcome_mat' === $cp_module_type || 'full_screen' === $cp_module_type ) ) { ?>
													<?php

													$configures_rulsets = count( $configures_rulsets ) < 1 ? $default_rulset : $configures_rulsets;

													?>
												<input class="input-hidden-ruleset" type="hidden" name="rulesets" value='<?php echo wp_json_encode( $configures_rulsets ); ?>'>
												<input class="input-hidden-default-ruleset" type="hidden" value='<?php echo wp_json_encode( $default_rulset ); ?>'>
											</div><!--Rulesets Wrap -->
										<?php } ?>
										</div>
									</div><!-- .cp-panel-content -->

										<?php
										$count++; }
								?>
							</div><!-- .cp-customizer-tab -->

								<?php
							}
							?>
					</div><!-- .cp-customizer-tabs-wrapper -->
				</form><!-- .cp-cust-form -->
			</div><!-- .cp-panel-container -->

					<?php
					if ( class_exists( 'Cp_V2_Services_Loader' ) ) {
						require_once 'connect-popup.php';
					}
					?>
			<div class="cp-steps-wrapper cp-hidden">

			<?php
				$step_count = 1;
				$modal_data = json_decode( $modal_data );
			if ( null !== $modal_data ) {
				$step_count = count( get_object_vars( $modal_data ) );
				if ( isset( $modal_data->common ) ) {
					--$step_count;
				}
			}

			if ( null === $modal_data ) {
				$modal_data = array(
					0 => array(
						'panel-1' => array(),
					),
				);
			}

				$step_to_navigate = isset( $_GET['step'] ) ? (int) $_GET['step'] - 1 : 0;

			if ( isset( $_GET['step'] ) && '' !== $_GET['step'] ) {
				$step_to_navigate = isset( $modal_data->$step_to_navigate ) ? $step_to_navigate : 0;
			}

			?>
				<div class="panel-step-list">

				<?php
				foreach ( $modal_data as $key => $value ) {

					if ( 'common' !== $key ) {
						$panel_id     = $key + 1;
						$active_class = '';

						if ( ( $step_to_navigate + 1 ) === $panel_id ) {
							$active_class = 'cp-active-step';
						}

						$no_prev_step_class = '';

						if ( 0 === $key ) {
							$no_prev_step_class = 'no-previous-step';
						}

						?>
							<span class="cp_step_button <?php echo esc_attr( $active_class ); ?>" data-step="<?php echo esc_attr( $panel_id ); ?>" id="cp_step_<?php echo esc_attr( $panel_id ); ?>" value="<?php echo esc_attr( $panel_id ); ?>"><?php echo esc_html( $panel_id ); ?></span>
						<?php } ?>
					<?php } ?>

					<!-- <a href="#" class="cp-tooltip-icon has-tip" data-position="bottom" title="Add Step" id="cp-add-step"><span class="multisteps-panel-icon">
					<i class="cp-icon-plus"></i></span></a> -->
					<a href="#" class="cp-tooltip-icon has-tip" data-position="bottom" title="Add Step" id="cp-clone-step"><span class="multisteps-panel-icon">
					<i class="cp-icon-plus"></i></span></a>
					<a href="#" class="cp-tooltip-icon has-tip" data-position="bottom" title="Delete Step" id="cp-delete-step"><span class="multisteps-panel-icon">
					<i class="cp-icon-trash 
					<?php
					if ( 1 === $panel_id ) {
						echo 'no-previous-step'; }
					?>
"></i></span></a>
					<a href="#" class="cp-multistep-draggable" id="#"><span class="multisteps-panel-icon">
					<i class="cp-icon-move"></i></span></a>
				</div>
			</div>
			<!-- cp-layers-wrapper -->
			<div class="cp-layer-wrapper cp-hidden">
				<div class="cp-layer-button cp-layer-draggable">
					<a href="#"><i class="cp-icon-move"></i></a>
				</div>
				<div id="cp-layer-position" class="cp-layer-button bring-forward">
					<a class="cp-tooltip-icon has-tip" data-position="left" title="<?php esc_attr_e( 'Bring Forward', 'convertpro' ); ?>" href="#"><i class="cp-icon-plus"></i></a>
				</div>
				<div class="cp-layer-button send-backward">
					<a class="cp-tooltip-icon has-tip" data-position="left" title="<?php esc_attr_e( 'Send Backward', 'convertpro' ); ?>" href="#"><i class="cp-icon-minus"></i></a>
				</div>
				<div class="cp-layer-button distribute-horizontally">
					<a class="cp-tooltip-icon has-tip" data-position="left" title="<?php esc_attr_e( 'Distribute Horizontally', 'convertpro' ); ?>" href="#"><i class="dashicons dashicons-image-flip-horizontal"></i></a>
				</div>
				<div class="cp-layer-button distribute-vertically">
					<a class="cp-tooltip-icon has-tip" data-position="left" title="<?php esc_attr_e( 'Distribute Vertically', 'convertpro' ); ?>" href="#"><i class="dashicons dashicons-image-flip-vertical"></i></a>
				</div>
				<div class="cp-layer-button cp-clone-field">
					<a class="cp-tooltip-icon has-tip" data-position="left" title="<?php esc_attr_e( 'Clone Field', 'convertpro' ); ?>" href="#"><i class="cp-icon-clone"></i></a>
				</div>
				<div class="cp-layer-button cp-delete-item">
					<a class="cp-tooltip-icon has-tip" data-position="left" title="<?php esc_attr_e( 'Delete Field', 'convertpro' ); ?>" href="#"><i class="cp-icon-trash"></i></a>
				</div>
				<div class="cp-layer-button hide-on-mobile">
					<a class="cp-tooltip-icon has-tip" data-position="left" title="<?php esc_attr_e( 'Hide On Mobile', 'convertpro' ); ?>" href="#"><i class="dashicons dashicons-hidden"></i></a>
				</div>
				<div class="cp-layer-button show-on-mobile">
					<a class="cp-tooltip-icon has-tip" data-position="left" title="<?php esc_attr_e( 'Show On Mobile', 'convertpro' ); ?>" href="#"><i class="dashicons dashicons-visibility"></i></a>
				</div>
			</div>
			<!-- cp-layers-wrapper -->
			<div class="cp-design-content">
				<div class="cp-live-design-area">
					<div class="cp-popup-container cpro-open cp-module-<?php echo esc_attr( $cp_module_type ); ?>">
						<div class="cp-popup-wrapper">
							<div class="panel-wrapper cp-popup cpro-animate-container cp-<?php echo esc_attr( $cp_module_type ); ?>" tabindex="1">
								<?php

								foreach ( $modal_data as $key => $value ) {

									$class = 'cp-hidden';

									if ( 'common' === $key ) {
										continue;
									}

									if ( ! isset( $_GET['step'] ) ) {
										if ( 0 === (int) $key ) {
											$class = '';
										}
									} elseif ( (int) $key === $step_to_navigate ) {
										$class = '';
									}
									$panel_id = $key + 1;

									?>
									<div id="panel-<?php echo esc_attr( $panel_id ); ?>" class="cp-popup-content panel <?php echo esc_attr( $class ); ?> cp-panel-data cp-target cp-<?php echo esc_attr( $cp_module_type ); ?>" data-type="panel">
										<div class="cp-show-panel-settings" ><i class="dashicons dashicons-admin-generic"></i></div>
										<?php do_action( 'cp_get_grid_svg' ); ?>
										<div class="panel-content-wrapper panel-<?php echo esc_attr( $panel_id ); ?>-content-wrapper">
											<div id="guide-h" class="guide"></div>
											<div id="guide-v" class="guide"></div>
											<div class="cp-panel-item default-cp-panel-item"></div>
											<div id="cp-group-grid" class="cp-group-grid">
												<div class="cp-ghost-select"><span></span></div>
											</div>
										</div>
									</div><!-- panel -->
									<?php

								}

								?>

								<input type="hidden" id="cp_step_count" value="<?php echo esc_attr( $step_count ); ?>" >
							</div> <!-- panel-wrapper -->
						</div> <!--.cp-popup-wrapper  -->

						<?php

						$credit_enable = esc_attr( get_option( 'cp_credit_option', '0' ) );

						if ( ( 'modal_popup' === $cp_module_type || 'full_screen' === $cp_module_type ) && $credit_enable ) {
							/* translators: %s CPRO_BRANDING_NAME */
							$credit_text         = apply_filters( 'cppro_credit_text', sprintf( esc_attr__( 'Powered by %s', 'convertpro' ), CPRO_BRANDING_NAME ) );
							$link_color          = cpro_get_style_settings( $style_id, 'design', 'credit_link_color' );
							$branding_agency_url = ( ! is_multisite() ) ? esc_url( get_option( 'cpro_branding_plugin_author_url' ) ) : esc_url( get_site_option( '_cpro_branding_plugin_author_url' ) );
							if ( defined( 'CPRO_CUSTOM_AUTHOR_URL' ) ) {
								$branding_agency_url = esc_url( CPRO_CUSTOM_AUTHOR_URL );
							}
							$cp_powered_by_url = false === $branding_agency_url ? CP_POWERED_BY_URL : $branding_agency_url;
							?>
							<div class="cp-credit-link cp-responsive">

								<a class="cp-credit-link" style="color: <?php echo esc_attr( $link_color ); ?>;" href="<?php echo esc_attr( esc_url( $cp_powered_by_url ) ); ?>" target="_blank" rel="noopener"><span> <?php echo esc_html( $credit_text ); ?> </span></a>
							</div>
							<?php
						}
						?>
					</div><!-- .cp-popup-container -->
					<div class="design-area-loading">
						<!-- <span class="spinner"></span> -->
						<div class="cp-absolute-loader" style="visibility: visible;">
							<div class="cp-loader">
								<h2 class="cp-loader-text"><?php esc_html_e( 'Loading...', 'convertpro' ); ?></h2>
								<div class="cp-loader-wrap">
									<div class="cp-loader-bar">
										<div class="cp-loader-shadow"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div><!-- cpro-onload -->
			</div><!-- .cp-live-design-area -->
		</div><!-- .cp-design-content -->
		<div class="cp-md-modal cp-mb-view-dialog cp-md-effect-2" id="cp-md-modal-2">
			<div class="cp-md-content">
				<div class="cp-md-modal-content">
					<div class="cp-md-modal-body" data-at="cp-md-modal_body">
						<div class="cp-md-form-integration cp-md-rightcolumn1">
							<div class="cp-md-modal-header">
								<h3><?php esc_html_e( 'Enable Mobile Editor', 'convertpro' ); ?></h3>
							</div>
							<div class="cp-md-contents">
								<p><?php esc_html_e( 'You are about to enable the mobile editor. This allows you to create a different design for smaller screens by adjusting the position of elements or hiding some of them.', 'convertpro' ); ?></p>
								<p>

								<?php

									/* translators: %s Convert Pro Name. */
									echo sprintf( esc_html__( 'If you would rather like ', 'convertpro' ) );
									echo esc_html( CPRO_BRANDING_NAME );
									echo sprintf( esc_html__( ' to manage this automatically, please click CANCEL.', 'convertpro' ) );
								?>
								</p>
							</div>
						</div>
					</div>
					<div class="cp-md-modal-footer">
						<div class="cp-md-info-wrap">
							<a href="#" class="cp-shrink-mob-opt" target="_blank"><?php esc_html_e( 'CANCEL', 'convertpro' ); ?></a>
						</div>
						<button type="button" class="cp-btn-default cp-primary-button cp-switch-mobile" style="display: block;"><?php esc_html_e( 'CREATE DIFFERENT CALL-TO-ACTION FOR SMALLER SCREENS', 'convertpro' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php /* Regenerate popup design */ ?>
		<div class="cp-md-modal cp-mb-regnerate-mobile-dialog cp-md-effect-2" id="cp-md-modal-2">
			<div class="cp-md-content">
				<div class="cp-md-modal-content">
					<div class="cp-md-modal-body" data-at="cp-md-modal_body">
						<div class="cp-md-form-integration cp-md-rightcolumn1">
							<div class="cp-md-modal-header">
								<h3><?php esc_html_e( 'Reset Mobile View', 'convertpro' ); ?></h3>
							</div>
							<div class="cp-md-contents">
								<p><?php esc_html_e( 'This action will regenerate the mobile view of your current step based on desktop design.', 'convertpro' ); ?></p>
								<p><?php esc_html_e( 'Any mobile specific changes you have made will be lost.', 'convertpro' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="cp-md-modal-footer">
						<div class="cp-md-info-wrap">
							<a href="#" rel="noopener" class="cp-dialog-regnerate-mobile-cancel" target="_blank"><?php esc_html_e( 'CANCEL', 'convertpro' ); ?></a>
						</div>
						<button type="button" class="cp-btn-default cp-primary-button cp-dialog-regnerate-mobile" style="display: block;"><?php esc_html_e( 'I understand, Continue!', 'convertpro' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<div class="cp-md-overlay"></div>
		<div class="cp-switch-screen-loader">
			<div class="loading">
				<div class="loading_msg"><?php esc_html_e( 'Loading...', 'convertpro' ); ?><span class="loading-info"></span></div>
			</div>
		</div>

			<?php
	}
}

if ( ! function_exists( 'cp_generate_sp_id' ) ) {
	/**
	 * Function Name: cp_generate_sp_id.
	 * Function Description: cp generate sp id.
	 *
	 * @param string $key string parameter.
	 */
	function cp_generate_sp_id( $key ) {

		$key = strtolower( $key );
		$key = preg_replace( '![^a-z0-9]+!i', '-', $key );
		return $key;
	}
}
