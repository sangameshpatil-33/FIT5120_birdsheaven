<?php
/**
 * Google Analytics Settings View
 *
 * @package convertpro
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

$analytics_data     = get_option( 'cp_ga_analytics_data' );
$cp_ga_identifier   = get_option( 'cp-ga-identifier' ) ? esc_attr( get_option( 'cp-ga-identifier' ) ) : '';
$cp_ga_auth_type    = get_option( 'cp-ga-auth-type' ) ? esc_attr( get_option( 'cp-ga-auth-type' ) ) : 'universal-ga';
$cp_ga_anonymous_ip = get_option( 'cp-ga-anonymous-ip' ) ? esc_attr( get_option( 'cp-ga-anonymous-ip' ) ) : '';
?>

<div class="cp-gen-set-content">
	<div class="cp-settings-container">
		<h3 class="cp-gen-set-title"><?php esc_html_e( 'Step 1 - Enable website tracking', 'convertpro-addon' ); ?></h3>
		<p>
		<?php
		/* translators: %s Product name */
			echo sprintf( esc_html__( '%s needs a Universal Google Analytics tracking code on your website for tracking impressions & conversions. Please select a method to add the code.', 'convertpro-addon' ), esc_attr( CPRO_BRANDING_NAME ) );
		?>
		</p>
		<form method="post" class="cp-settings-form">
			<div class="debug-section cp-access-roles">
				<table class="cp-postbox-table form-table">
					<tr class="cp-settings-row">
						<th scope="row">
							<label for="cp-ga-auth-type"><?php esc_html_e( 'Tracking Code Info', 'convertpro-addon' ); ?>
							</label>
						</th>
						<td>
							<select name="cp-ga-auth-type" id="cp-ga-auth-type">
								<option value="universal-ga" <?php selected( $cp_ga_auth_type, 'universal-ga' ); ?>><?php esc_html_e( 'Already added Universal Google Analytics code', 'convertpro-addon' ); ?></option>
								<option value="gtm-code" <?php selected( $cp_ga_auth_type, 'gtm-code' ); ?>><?php esc_html_e( 'Already added Google Tag Manager code', 'convertpro-addon' ); ?></option>
								<option value="gtag" <?php selected( $cp_ga_auth_type, 'gtag' ); ?>><?php esc_html_e( 'Already added Global Site Tag (gtag) code', 'convertpro-addon' ); ?></option>
								<option value="manual" <?php selected( $cp_ga_auth_type, 'manual' ); ?>><?php esc_html_e( 'Add Google Analytics Tracking ID', 'convertpro-addon' ); ?></option>
							</select>
						</td>
					</tr>
					<tr class="cp-settings-row">
						<th scope="row">
							<label for="cp-ga-anonymous-ip"><?php esc_html_e( 'Anonymize IP for event tracking', 'convertpro-addon' ); ?>
							</label>
						</th>
						<td>
							<?php
							if ( ( '' === $cp_ga_anonymous_ip ) || ( 'checked' === $cp_ga_anonymous_ip ) ) {
								?>
									<input type="hidden" name="cp-ga-anonymous-ip" value="unchecked">
								<?php
							}
							?>
							<input type='checkbox' name="cp-ga-anonymous-ip" value="checked" id="cp-ga-anonymous-ip" <?php echo ( ( '' === $cp_ga_anonymous_ip ) || ( 'checked' === $cp_ga_anonymous_ip ) ) ? 'checked' : ''; ?>>
							<?php
								$learn_ip_url = add_query_arg(
									array(
										'hl' => 'en',
									),
									'https://support.google.com/analytics/answer/2763052'
								);
								?>
							<span class="help-link" style="margin-left: 15px;"><a target='_blank' rel="noopener" href=<?php echo esc_url( $learn_ip_url ); ?>><?php esc_html_e( 'Learn more about IP Anonymization in Analytics.', 'convertpro-addon' ); ?></a>
							</span>
						</td>
					</tr>
					<tr class="cp-settings-row" data-dep-element='cp-ga-auth-type' data-dep-val='gtm-code'>
						<th scope="row">
							<label for="cp-ga-identifier"><?php esc_html_e( 'Tag Manager Configurations', 'convertpro-addon' ); ?>
							</label>
						</th>
						<td>
							<span>
								<?php
								echo esc_html__( 'Please follow the steps to ', 'convertpro-addon' );
								$setup_cp_ga_url = add_query_arg(
									array(),
									'https://www.convertpro.net/docs/setup-convert-pro-events-google-tag-manager/'
								);
								?>
								<a target="_blank" rel="noopener" href=<?php echo esc_url( $setup_cp_ga_url ); ?>>
								<?php
								/* translators: %s Link */
								echo sprintf( esc_html__( 'Setup %1$s Events in Google Tag Manager.', 'convertpro-addon' ), esc_attr( CPRO_BRANDING_NAME ) );
								?>
								</a>
								<?php
								echo esc_html__( ' This is a must when you want to integrate with Google Analytics.', 'convertpro-addon' );
								?>
							</span>
						</td>

					</tr>
					<tr class="cp-settings-row" data-dep-element='cp-ga-auth-type' data-dep-val='manual'>
						<th scope="row">
							<label for="cp-ga-identifier"><?php esc_html_e( 'Google Analytics Tracking ID', 'convertpro-addon' ); ?>
							</label>
						</th>
						<td>
							<input type='text' name="cp-ga-identifier" value="<?php echo esc_attr( $cp_ga_identifier ); ?>" id="cp-ga-identifier">
							<?php
								$find_tracking_id_url = add_query_arg(
									array(
										'hl' => 'en#trackingID',
									),
									'https://support.google.com/analytics/answer/1008080'
								);
								?>
							<span class="help-link" style="
									margin-left: 15px;
								"><a target='_blank' rel="noopener" href=<?php echo esc_url( $find_tracking_id_url ); ?>><?php esc_html_e( 'Where Can I find this?', 'convertpro-addon' ); ?></a>
							</span>
						</td>
					</tr>
				</table>
			</div>
			<p class="submit">
				<input type="hidden" name="curr_tab" value="1">
				<input type="hidden" name="cp-update-settings-nonce" id="cp-update-settings-nonce" value="<?php echo esc_attr( wp_create_nonce( 'cp-update-settings-nonce' ) ); ?>" />
				<button type="submit" class="cp-btn-primary cp-md-btn cp-button-style button-update-settings cp-submit-settings"><?php esc_html_e( 'Save Settings', 'convertpro-addon' ); ?></button>
			</p>
		</form>
	</div>
	<div class="cp-ga-auth-container">
		<h3 class="cp-gen-set-title">
		<?php
		/* translators: %s Product Name */
					echo sprintf( esc_html__( 'Step 2 - Authorize %s to view Google Analytics data', 'convertpro-addon' ), esc_attr( CPRO_BRANDING_NAME ) );
		?>
					</h3>

		<?php if ( false === $analytics_data ) { ?>
			<div class="cp-modal-content">
				<div class="cp-ga-code-container">
				<p>
				<?php
				/* translators: %s Product Name */
				echo sprintf( esc_html__( 'Allow %s to fetch analytics data from your Google Analytics account.', 'convertpro-addon' ), esc_attr( CPRO_BRANDING_NAME ) );

				$ga_details_nonce = wp_create_nonce( 'cp-auth-ga-access-action' );
				$ga_inst          = new CP_V2_GA();
				$auth_url         = $ga_inst->generate_auth_url();
				echo esc_html_e( ' Get a Google Analytics access code from ', 'convertpro-addon' );
				?>
					<a target='_blank' rel='noopener' href=<?php echo esc_attr( esc_url( $auth_url ) ); ?>><?php esc_html_e( 'here', 'convertpro-addon' ); ?></a>
					<?php
					echo esc_html_e( ', and paste it below.', 'convertpro-addon' );
					?>
					</p>
					<div class="cp-form-error cp-notification-message">
						<label class="cp-error"></label>
					</div>
					<table class="cp-postbox-table form-table auth-input-box">
						<tbody>
							<tr class="cp-settings-row">
								<th scope="row">
									<label for="cp-ga-access-code"><?php esc_html_e( 'Authorization Code', 'convertpro-addon' ); ?></label>
								</th>
								<td>
									<input type="textbox" class="cp-ga-access-code" name="cp-ga-access-code" id="cp-ga-access-code" placeholder="<?php esc_attr_e( 'Enter access code here', 'convertpro-addon' ); ?>">
									<input type="hidden" id="cp-ga-save-nonce" value="<?php echo esc_attr( $ga_details_nonce ); ?>">
								</td>
							</tr>
							<tr class="cp-settings-row accounts-option" style="display: none;">
								<th scope="row">
									<label for="cp-ga-access-code"><?php esc_html_e( 'Select Profile/View', 'convertpro-addon' ); ?></label>
								</th>
								<td>
									<select name="cp-ga-profile" id="cp-ga-profile">
									</select>
								</td>
							</tr>
						</tbody>
					</table>

					<div class="cp-modal-button cp-action-row">
						<button class="cp-auth-ga-access cp-md-btn cp-button-style cp-btn-primary"><?php esc_html_e( 'NEXT', 'convertpro-addon' ); ?></button>
						<button class="cp-save-ga-details cp-md-btn cp-button-style cp-btn-primary" style="display: none;" data-inprogress="<?php esc_attr_e( 'Saving...', 'convertpro-addon' ); ?>" data-title="<?php esc_attr_e( 'Save', 'convertpro-addon' ); ?>"><?php esc_html_e( 'Save', 'convertpro-addon' ); ?></button>
						<?php
						wp_nonce_field( 'cp_save_ga_details', 'cp_save_ga_details_nonce' );
						?>
					</div>
				</div>    
			</div><!-- End Wrapper -->
			<?php
		} else {
			$ga_profile     = get_option( '_cpro_ga_profile' );
			$ga_credentials = get_option( 'cp_ga_credentials' );
			$profile        = '';
			$profile_view   = isset( $ga_credentials['profile'] ) ? str_replace( 'ga:', '', $ga_credentials['profile'] ) : '';
			$timezone       = isset( $ga_credentials['timezone'] ) ? $ga_credentials['timezone'] : '';

			if ( false !== $ga_profile && '' !== $ga_profile ) {
				$profile = $ga_profile;
			}
			?>
	<p>
			<?php
				esc_html_e( 'You have authenticated with ', 'convertpro-addon' );
			?>
			<b><?php echo esc_attr( $profile ); ?></b>
			<?php
				esc_html_e( '\'s Google Analytics account.', 'convertpro-addon' );
			?>
	</p>
			<?php if ( '' !== $profile_view ) { ?>
		<span class="cpro-profile-view">
		<b><?php esc_html_e( 'View ID: ', 'convertpro-addon' ); ?></b>
				<?php
				echo esc_attr( $profile_view );
				?>
		</span>
				<?php
			}

			if ( '' !== $timezone ) {
				?>
		<span class="cpro-ga-timezone" style="display: block; margin: 20px 0 20px">
		<b><?php esc_html_e( 'Timezone: ', 'convertpro-addon' ); ?></b>
				<?php
				echo esc_attr( $timezone );
				?>
		</span>
				<?php } ?>
		<span class="cp-ga-delete-wrap">
		<a href="javascript:void(0);" class="cp-delete-ga-integration">
			<?php
			esc_html_e( 'Remove Google Analytics Integration', 'convertpro-addon' );
			wp_nonce_field( 'cp_delete_ga_integration', 'cp_delete_ga_integration_nonce' );
			?>
		</a>
		</span>
		<?php } ?>
	</div>
</div>
