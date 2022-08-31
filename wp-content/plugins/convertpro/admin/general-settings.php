<?php
/**
 * Settings Admin Page.
 *
 * @package ConvertPro
 */

?>
<div class="wrap about-wrap ab-test-cp bend">
	<div class="cp-gen-set-tabs">
		<nav class="cp-gen-set-menu">
			<?php
			$nav_menus = array(
				'general'        => array(
					'label' => __( 'General', 'convertpro' ),
					'icon'  => 'admin-tools',
				),
				'license'        => array(
					'label' => __( 'License', 'convertpro' ),
					'icon'  => 'awards',
				),
				'rollback-ver'   => array(
					'label' => __( 'Rollback Version', 'convertpro' ),
					'icon'  => 'image-rotate',
				),
				'addons'         => array(
					'label' => __( 'Addons', 'convertpro' ),
					'icon'  => 'admin-plugins',
				),
				'email-template' => array(
					'label' => __( 'Email Notification', 'convertpro' ),
					'icon'  => 'email',
				),
				'recaptcha'      => array(
					'label' => __( 'Recaptcha', 'convertpro' ),
					'icon'  => 'update',
				),
				'geolite2'       => array(
					'label' => __( 'MaxMind Geolocation', 'convertpro' ),
					'icon'  => 'location',
				),
				'advanced'       => array(
					'label' => __( 'Advanced', 'convertpro' ),
					'icon'  => 'admin-generic',
				),
				'branding'       => array(
					'label' => __( 'Branding', 'convertpro' ),
					'icon'  => 'tag',
				),
			);

			$hide_branding = ( is_multisite() ) ? get_site_option( '_cpro_hide_branding' ) : get_option( 'cpro_hide_branding' );

			if ( '1' === $hide_branding ) {
				unset( $nav_menus['branding'] );
			}

			if ( defined( 'CP_HIDE_WHITE_LABEL' ) && CP_HIDE_WHITE_LABEL ) {
				unset( $nav_menus['branding'] );
			}

			foreach ( $nav_menus as $slug => $nav_menu ) {
				do_action( 'cp_before_' . $slug . '_nav_menu' );
				?>
			<a href="#<?php echo esc_attr( $slug ); ?>" class="cp-settings-nav selected"><span class="cp-gen-set-icon"><em class="dashicons dashicons-<?php echo esc_attr( $nav_menu['icon'] ); ?>"></em></span><?php echo esc_html( $nav_menu['label'] ); ?></a>
				<?php
				do_action( 'cp_after_' . $slug . '_nav_menu' );
			}
			do_action( 'cp_general_set_navigation' );
			?>
		</nav>
		<div class="cp-gen-set-content visible">
			<div class="cp-settings-container">
				<h3 class="cp-gen-set-title"><?php esc_html_e( 'General Settings', 'convertpro' ); ?></h3>
				<form method="post" class="cp-settings-form">
				<?php
				$menu_position       = esc_attr( get_option( 'bsf_menu_position' ) );
				$menu_position       = ( ! $menu_position ) ? self::$default_menu_position : $menu_position;
				$dev_mode_option     = esc_attr( get_option( 'cp_dev_mode' ) );
				$antispam_enabled    = esc_attr( get_option( 'cp_antispam_enabled' ) );
				$cp_mx_valid_enabled = get_option( 'cp_mx_valid_enabled', 0 );
				$beta_update_option  = esc_attr( get_option( 'cpro_beta_updates' ) );
				$user_inactivity     = esc_attr( get_option( 'cp_user_inactivity' ) );
				$cp_access_roles     = get_option( 'cp_access_role' );
				$cp_credit_option    = esc_attr( get_option( 'cp_credit_option' ) );
				$image_on_ready      = esc_attr( get_option( 'cpro_image_on_ready' ) );
				?>
					<table class="cp-postbox-table form-table">
						<caption></caption>
						<tbody>
						<?php
						// Get list of current General entries.
						$entries = array();
						foreach ( $GLOBALS['menu'] as $entry ) {
							if ( false !== strpos( $entry[2], '.php' ) ) {
								$entries[ $entry[2] ] = $entry[0];
							}
						}

						// Remove <span> elements with notification bubbles (e.g. update or comment count).
						if ( isset( $entries['plugins.php'] ) ) {
							$entries['plugins.php'] = preg_replace( '/ <span.*span>/', '', $entries['plugins.php'] );
						}
						if ( isset( $entries['edit-comments.php'] ) ) {
							$entries['edit-comments.php'] = preg_replace( '/ <span.*span>/', '', $entries['edit-comments.php'] );
						}

						$entries['top']    = __( 'Top-Level (top)', 'convertpro' );
						$entries['middle'] = __( 'Top-Level (middle)', 'convertpro' );
						$entries['bottom'] = __( 'Top-Level (bottom)', 'convertpro' );

						$select_box = '<select name="bsf_menu_position" >' . "\n";
						foreach ( $entries as $cp_page => $entry ) {
							$select_box .= '<option ' . selected( $cp_page, $menu_position, false ) . ' value="' . $cp_page . '">' . $entry . "</option>\n";
						}
						$select_box .= "</select>\n";

						$dmval             = ! $dev_mode_option ? 0 : 1;
						$antispam_val      = ! $antispam_enabled ? 1 : $antispam_enabled;
						$image_on_readyval = ! $image_on_ready ? 0 : 1;
						$betaval           = ! $beta_update_option ? 0 : 1;
						$uniq              = uniqid();
						$crval             = ( ! $cp_credit_option || '0' === $cp_credit_option ) ? 0 : 1;

						if ( '' === $user_inactivity ) {
							$user_inactivity = '60';
						}
						?>
							<tr>
								<th scope="row">
									<label for="option-admin-menu-global-font"><?php esc_html_e( 'Global Font ', 'convertpro' ); ?></label>
									<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_html_e( 'Controls font of your call-to-action. This font will be overwritten by individual element\'s typography option.', 'convertpro' ); ?>"><em class="dashicons dashicons-editor-help"></em></span>
								</th>
								<td>
								<?php
								$font_options     = CP_V2_Fonts::cp_get_fonts();
								$output           = '';
								$font_weights_arr = '';
								$cp_global_font   = Cp_V2_Model::get_cp_global_fonts();
								$sel_font_family  = $cp_global_font['family'];
								$sel_font_weight  = $cp_global_font['weight'];
								?>
									<div class="cp-global-font-field">
										<input type="hidden" id="cp_global_font" name="cp_global_font" class="cp-input" value="" >
										<select for="cp_global_font"  class="cp-font-family" >
								<?php foreach ( $font_options as $key => $font ) { ?>
											<optgroup label="<?php echo esc_attr( $key ); ?>">
											<?php
											foreach ( $font as $font_family => $font_weights ) {
												$inherit_key = array_search( 'Inherit', $font_weights, true );
												unset( $font_weights[ $inherit_key ] );
												$selected = $sel_font_family === $font_family ? 1 : '';
												?>
												<option value="<?php echo esc_attr( $font_family ); ?>" <?php selected( $selected, 1 ); ?> data-weight="<?php echo esc_attr( implode( ',', $font_weights ) ); ?>"><?php echo esc_html( ucfirst( $font_family ) ); ?></option>
												<?php
												if ( '' !== $selected ) {
													$font_weights_arr = $font_weights;
												}
											}
											?>
											</optgroup>
									<?php
								}
								?>
										</select>
										<select for="cp_global_font" class="cp-font-weights">
								<?php
								if ( '' !== $font_weights_arr ) {
									foreach ( $font_weights_arr as $weight ) {
										$selected = $sel_font_weight === $weight ? 1 : '';
										?>
											<option value="<?php echo esc_attr( $weight ); ?>" <?php selected( $selected, 1 ); ?>><?php echo esc_html( $weight ); ?></option>
										<?php
									}
								}
								?>
										</select>
									</div>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="option-admin-menu-inactive-page"><?php esc_html_e( 'User Inactivity Time ', 'convertpro' ); ?></label>
									<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'This is the time considered to track user inactivity, when you activate the user inactivity trigger.', 'convertpro' ); ?>"><em class="dashicons dashicons-editor-help"></em>
									</span>
								</th>
								<td> 
									<input type="number" id="cp_user_inactivity" name="cp_user_inactivity" min="1" max="10000" value="<?php echo esc_attr( $user_inactivity ); ?>"/> <span class="description"><?php esc_html_e( ' Seconds', 'convertpro' ); ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="cp_credit_option"><strong><?php esc_html_e( 'Show Credit Link', 'convertpro' ); ?></strong>
										<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'By enabling this, you agree to display a tiny credit link over the overlay when a popup is displayed.', 'convertpro' ); ?>"><em class="dashicons dashicons-editor-help"></em></span>
									</label>
								</th>
								<td>
									<div class="cp-switch-wrapper">
										<input type="text"  id="cp_credit_option" class="form-control cp-input cp-switch-input" name="cp_credit_option" value="<?php echo esc_attr( $crval ); ?>" />
										<input type="checkbox" <?php checked( $crval, 1 ); ?> id="cp_credit_option_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $crval ); ?>" >
										<label class="cp-switch-btn checkbox-label" data-on=<?php esc_attr_e( 'ON', 'convertpro' ); ?>  data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cp_credit_option" for="cp_credit_option_btn_<?php echo esc_attr( $uniq ); ?>"></label>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
					<input type="hidden" name="curr_tab" value="0">
					<input type="hidden" name="cp-update-settings-nonce" id="cp-update-settings-nonce" value="<?php echo esc_attr( wp_create_nonce( 'cp-update-settings-nonce' ) ); ?>" />
					<button type="submit" class="cp-btn-primary cp-md-btn cp-button-style button-update-settings cp-submit-settings"><?php esc_html_e( 'Save Settings', 'convertpro' ); ?></button>
				</form>
			</div>
		</div>
		<div class="cp-gen-set-content">
			<?php require_once CP_V2_BASE_DIR . 'admin/license.php'; ?>
		</div>
		<div class="cp-gen-set-content">
			<?php require_once CP_V2_BASE_DIR . 'admin/rollback-version.php'; ?>
		</div>
		<div class="cp-gen-set-content cp-addon-tab">
			<div class="cp-settings-container">
			<?php
				$addon_content = apply_filters( 'cp_general_addon_page', '' );
			if ( '' === $addon_content ) {
				require_once CP_V2_BASE_DIR . 'admin/add-ons.php';
			} else {
				echo $addon_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
			</div>
		</div>
		<?php do_action( 'cp_after_addons_content' ); ?>
		<div class="cp-gen-set-content">
			<?php require_once CP_V2_BASE_DIR . 'admin/email-template.php'; ?>
		</div>
		<?php
		do_action( 'cp_after_email_template_content' );

		$display_adv_settings = false;

		?>
		<div class="cp-gen-set-content">
			<?php require_once CP_V2_BASE_DIR . 'admin/google-recaptcha.php'; ?>
		</div>
		<div class="cp-gen-set-content">
			<?php require_once CP_V2_BASE_DIR . 'admin/cpro-geolite2-maxmind.php'; ?>
		</div>
		<div class="cp-gen-set-content">
			<div class="cp-settings-container">
				<?php
				if ( current_user_can( 'manage_options' ) ) {
					$display_adv_settings = true;
					$antispam_enabled     = sanitize_title( get_option( 'cp_antispam_enabled' ) );
					$antispam_val         = ( '0' === $antispam_enabled ) ? 0 : 1;
					?>
				<h3 class="cp-gen-set-title"><?php esc_html_e( 'Advanced Settings', 'convertpro' ); ?></h3>
				<form method="post" class="cp-settings-form">
					<div class="debug-section cp-access-roles">
						<table class="cp-postbox-table form-table">
							<caption></caption>
							<tr>
								<th scope="row">
									<label for="option-admin-menu-parent-page"><?php esc_html_e( 'Admin Menu Position ', 'convertpro' ); ?>
										<?php /* translators: %s: Convert Pro Name */ ?>
										<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php echo sprintf( esc_attr__( '%s will be listed under the menu you select here.', 'convertpro' ), esc_attr( CPRO_BRANDING_NAME ) ); ?>"><em class="dashicons dashicons-editor-help"></em></span>
									</label>
								</th>
								<td>
								<select name="bsf_menu_position">
								<?php
								foreach ( $entries as $cp_page => $entry ) {
									?>
										<option <?php selected( $cp_page, $menu_position ); ?> value="<?php echo esc_attr( $cp_page ); ?>"><?php echo esc_html( $entry ); ?></option>
										<?php
								}
								?>
								</select>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<?php /* translators: %s percentage */ ?>
									<label for="cp-access-user-role"><strong><?php echo sprintf( esc_html__( 'Allow %s For', 'convertpro' ), esc_attr( CPRO_BRANDING_NAME ) ); ?></strong><?php /* translators: %s percentage */ ?>
										<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php echo sprintf( esc_html__( 'The site administrator has complete access to %s. Select the user roles you wish to grant access to.', 'convertpro' ), esc_attr( CPRO_BRANDING_NAME ) ); ?>"><em class="dashicons dashicons-editor-help"></em></span>
									</label>
								</th>
								<td>
									<ul class="checkbox-grid">
									<?php
									// Get saved access roles.
									global $wp_roles;
									$roles = $wp_roles->get_names();

									unset( $roles['administrator'] );
									if ( ! $cp_access_roles ) {
										$cp_access_roles = array();
									}

									foreach ( $roles as $key => $cp_access_role ) {
										$checked = ( in_array( $key, $cp_access_roles, true ) ) ? 1 : '';
										?>
										<li>
											<input type="checkbox" name="cp_access_role[]" <?php checked( $checked, 1 ); ?> value="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" />
											<label class="cp-role-label" for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $cp_access_role ); ?></label>
										</li>
									<?php } ?>
									</ul>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="option-admin-menu-developer-page"><?php esc_attr_e( 'Add Antispam field to the form', 'convertpro' ); ?>
										<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'Enabling this will add an antispam field to all call-to-action forms. Convert Pro uses Honeypot field technique to fight spam.', 'convertpro' ); ?>"><em class="dashicons dashicons-editor-help"></em></span>
									</label>
								</th>
								<td>
									<div class="cp-switch-wrapper">
										<input type="text"  id="cp_antispam_enabled" class="form-control cp-input cp-switch-input" name="cp_antispam_enabled" value="<?php echo esc_attr( $antispam_val ); ?>" />
										<input type="checkbox" <?php checked( $antispam_val, 1 ); ?> id="cp_antispam_enabled_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $antispam_val ); ?>" >
										<label class="cp-switch-btn checkbox-label" data-on=<?php esc_attr_e( 'ON', 'convertpro' ); ?>  data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cp_antispam_enabled" for="cp_antispam_enabled_btn_<?php echo esc_attr( $uniq ); ?>"></label>
									</div>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="option-admin-menu-developer-page"><?php esc_attr_e( 'MX Record Validation For Email', 'convertpro' ); ?>
										<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'Enabling this will check whether the entered Email ID is valid or fake.', 'convertpro' ); ?>"><em class="dashicons dashicons-editor-help"></em></span>
									</label>
								</th>
								<td>
									<div class="cp-switch-wrapper">
										<input type="text" id="cp_mx_valid_enabled" class="form-control cp-input cp-switch-input" name="cp_mx_valid_enabled" value="<?php echo esc_attr( $cp_mx_valid_enabled ); ?>" />
										<input type="checkbox" <?php checked( (int) $cp_mx_valid_enabled, 1 ); ?> id="cp_mx_valid_enabled_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $cp_mx_valid_enabled ); ?>" >
										<label class="cp-switch-btn checkbox-label" data-on="<?php esc_attr_e( 'ON', 'convertpro' ); ?>" data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cp_mx_valid_enabled" for="cp_mx_valid_enabled_btn_<?php echo esc_attr( $uniq ); ?>"></label>
									</div>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="option-admin-menu-developer-page"><?php esc_html_e( 'Developer Mode ', 'convertpro' ); ?>
										<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'Enabling this will help you debug an issue with a particular design by viewing the respective CSS/JS file associated with it.', 'convertpro' ); ?>"><em class="dashicons dashicons-editor-help"></em></span>
									</label>
								</th>
								<td>
									<div class="cp-switch-wrapper">
										<input type="text"  id="cp_dev_mode" class="form-control cp-input cp-switch-input" name="cp_dev_mode" value="<?php echo esc_attr( $dmval ); ?>" />
										<input type="checkbox" <?php checked( $dmval, 1 ); ?> id="cp_dev_mode_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $dmval ); ?>" >
										<label class="cp-switch-btn checkbox-label" data-on=<?php esc_attr_e( 'ON', 'convertpro' ); ?>  data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cp_dev_mode" for="cp_dev_mode_btn_<?php echo esc_attr( $uniq ); ?>"></label>
									</div>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="option-admin-menu-developer-page"><?php esc_html_e( 'Allow Beta Updates ', 'convertpro' ); ?>
										<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'Enable this option to receive update notifications for beta versions.', 'convertpro' ); ?>"><em class="dashicons dashicons-editor-help"></em></span>
									</label>
								</th>
								<td>
									<div class="cp-switch-wrapper">
										<input type="text"  id="cpro_beta_updates" class="form-control cp-input cp-switch-input" name="cpro_beta_updates" value="<?php echo esc_attr( $betaval ); ?>" />
										<input type="checkbox" <?php checked( $betaval, 1 ); ?> id="cpro_beta_updates_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $betaval ); ?>" >
										<label class="cp-switch-btn checkbox-label" data-on=<?php esc_attr_e( 'ON', 'convertpro' ); ?>  data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cpro_beta_updates" for="cpro_beta_updates_btn_<?php echo esc_attr( $uniq ); ?>"></label>
									</div>
								</td>
							</tr>
							<?php
							if ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) && isset( $_GET['author'] ) ) {
								?>
								<tr>
									<th scope="row">
										<label for="option-lazy-load"><?php esc_html_e( 'Allow images load on document ready ', 'convertpro' ); ?>
											<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'Enable this option to load images on load of document.', 'convertpro' ); ?>"><em class="dashicons dashicons-editor-help"></em></span>
										</label>
									</th>
									<td>
										<div class="cp-switch-wrapper">
											<input type="text"  id="cpro_image_on_ready" class="form-control cp-input cp-switch-input" name="cpro_image_on_ready" value="<?php echo esc_attr( $image_on_readyval ); ?>" />
											<input type="checkbox" <?php checked( $image_on_readyval, 1 ); ?> id="cpro_image_on_ready_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $image_on_readyval ); ?>" >
											<label class="cp-switch-btn checkbox-label" data-on=<?php esc_attr_e( 'ON', 'convertpro' ); ?>  data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cpro_image_on_ready" for="cpro_image_on_ready_btn_<?php echo esc_attr( $uniq ); ?>"></label>
										</div>
									</td>
								</tr>
									<?php
							}
							?>
						</table>
					</div>
					<p class="submit">
						<input type="hidden" name="curr_tab" value="1">
						<input type="hidden" name="cp-update-settings-nonce" id="cp-update-settings-nonce" value="<?php echo esc_attr( wp_create_nonce( 'cp-update-settings-nonce' ) ); ?>" />
						<button type="submit" class="cp-btn-primary cp-md-btn cp-button-style button-update-settings cp-submit-settings"><?php esc_html_e( 'Save Settings', 'convertpro' ); ?></button>
					</p>
					<?php
				}
				?>
				</form>
				<div class="cp-cache-section cp-gen-set-content 
				<?php
				if ( $display_adv_settings ) {
					echo 'cp-border-top';
				}
				?>
">
					<h3 class="cp-gen-set-title"><?php esc_html_e( 'Cache', 'convertpro' ); ?></h3>
					<p><?php esc_html_e( 'HTML data of your call-to-action is dynamically generated and cached each time you create or edit a call-to-action. There might be chances that cache needs to be refreshed when you update to the latest version or migrate your site. If you are facing any issues, please try clearing the cache by clicking the button below.', 'convertpro' ); ?></p>
					<button class="cp-btn-primary cp-md-btn cp-button-style cp-refresh_html">
					<?php esc_html_e( 'Clear Cache', 'convertpro' ); ?></button>
				</div>
			</div>
		</div>
		<div class="cp-gen-set-content">
			<?php require_once CP_V2_BASE_DIR . 'admin/branding.php'; ?>
		</div>
		<?php do_action( 'cp_after_advanced_settings_content' ); ?>
		<?php do_action( 'cp_general_set_content' ); ?>
	</div>
</div> <!-- End Wrapper -->
