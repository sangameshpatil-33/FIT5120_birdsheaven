<?php
/**
 * Google Analytics Settings View
 *
 * @package Convert Pro
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

$ga_loader     = Cp_GA_Loader::get_instance();
$analyics_data = $ga_loader->calculate_analytics_data();

$todays_total_impressions = $analyics_data['total_impressions'];
$todays_total_comversions = $analyics_data['total_conversions'];
$todays_conversion_rate   = $analyics_data['total_conversion_rate'];

?>

<div class="cp-popup-container">
	<div class="cp-col-3 cp-stat-v2">
		<div class="cp-impression-info">
			<div class="cp-visual">
				<i class="dashicons dashicons-chart-pie"></i>
			</div>
			<div class="cp-impression-count"><?php echo esc_html( $todays_total_impressions ); ?></div>
			<div class="cp-impression-title"><?php esc_html_e( 'Impressions', 'convertpro-addon' ); ?></div>
		</div>
	</div> 
	<div class="cp-col-3 cp-stat-v2">
		<div class="cp-impression-info">
			<div class="cp-visual">
				<i class="dashicons dashicons-chart-bar"></i>
			</div>
			<div class="cp-impression-count"><?php echo esc_html( $todays_total_comversions ); ?></div>
			<div class="cp-impression-title"><?php esc_html_e( 'Conversions', 'convertpro-addon' ); ?></div>
		</div>
	</div>
	<div class="cp-col-3 cp-stat-v2">
		<div class="cp-impression-info">
			<div class="cp-visual">
				<i class="dashicons dashicons-groups"></i>
			</div>
			<div class="cp-impression-count"><?php echo esc_html( $todays_conversion_rate ); ?></div>
			<div class="cp-impression-title"><?php esc_html_e( 'Conversion Rate', 'convertpro-addon' ); ?></div>
		</div>
	</div>
</div> <!-- End Impression -->

<div id="cp-dashboard-modal" class="cp-ga-modal cp-common-modal">
	<div class="cp-md-content cp-save-animate-container cp-animated">
		<div class="cp-close-wrap"><i class="dashicons dashicons-no-alt"></i></div>
		<div class="cp-form-error cp-notification-message">
			<label class="cp-error"></label>
		</div>         
		<div class="cp-modal-header">
			<h3 class="cp-md-modal-title"><?php esc_html_e( 'Google Analytics Authorization', 'convertpro-addon' ); ?></h3>
		</div>
		<div class="cp-modal-content">
			<div class="cp-modal-description">
				<p>
				<?php
					/* translators: %s percentage */
				echo sprintf( esc_html__( 'Allow %s to access your Analytics account to sync Analytics data.', 'convertpro-addon' ), esc_attr( CPRO_BRANDING_NAME ) );
				?>
				</p>
			</div>
			<div class="cp-ga-code-container">
				<p>
				<?php

				$ga_details_nonce = wp_create_nonce( 'cp-auth-ga-access-action' );
				$ga_inst          = new CP_V2_GA();
				$auth_url         = $ga_inst->generate_auth_url();
				esc_html_e( 'Get a Google Analytics access code from ', 'convertpro-addon' );
				?>
				<a target='_blank' rel='noopener' href=<?php echo esc_attr( esc_url( $auth_url ) ); ?> ><?php esc_html_e( 'here', 'convertpro-addon' ); ?></a> 
				<?php
				esc_html_e( ', and paste it below.', 'convertpro-addon' );
				?>
				</p>
				<div class="cp-ga-input-wrap">
					<input type="textbox" class="cp-ga-access-code" name="cp-ga-access-code" placeholder="<?php esc_attr_e( 'Enter access code here', 'convertpro-addon' ); ?>">
					<input type="hidden" id="cp-ga-save-nonce" value="<?php echo esc_attr( $ga_details_nonce ); ?>">
				</div>
				<div class="cp-modal-button cp-action-row">
					<button class="cp-auth-ga-access cp-md-btn cp-button-style cp-btn-primary"><?php esc_html_e( 'AUTHENTICATE MY ACCOUNT', 'convertpro-addon' ); ?></button>
				</div>
			</div>    
		</div><!-- End Wrapper -->
	</div>
</div> <!-- Modal Popup -->

<div id="cp-ga-dashboard-modal" class="cp-dashboard-modal cp-common-modal cp-ga-style-analytics">
	<div class="cp-md-content cp-save-animate-container cp-animated">
		<div class="cp-close-wrap"><i class="dashicons dashicons-no-alt"></i></div>
		<div class="cp-modal-header">   
			<h3 class="cp-md-modal-title"><?php esc_html_e( 'Google Analytics', 'convertpro-addon' ); ?></h3>
		</div>
		<div class="cp-ga-modal-content">
			<div class="cp-ga-filter-wrap"> <!-- DateWise Start -->
				<div class="cp-ga-style-filter cpro-reportrange" id="cpro-reportrange">
					<i class="dashicons dashicons-calendar-alt"></i>
					<span></span>
					<input type="hidden" name="cpro-analytics-dates" id="cpro-analytics-dates" data-start-date="" data-end-date="" value="">
				</div>
				<div class="cp-ga-style-filter" id="cpro-impressions-count">
					<?php esc_html_e( 'Impressions: ', 'convertpro-addon' ); ?>
					<span></span>
				</div>
				<div class="cp-ga-style-filter" id="cpro-conversions-count">
					<?php esc_html_e( 'Conversions: ', 'convertpro-addon' ); ?>
					<span></span>
				</div>
			</div><!-- DateWise End -->
			<div class="cp_ga_chart_wrap">
				<div class="edit-screen-overlay" style="overflow: hidden;background: #FCFCFC;width: 100%;height: 100%;top: 0;left: 0;z-index: 9999999;">
					<div class="cp-absolute-loader" style="visibility: visible;overflow: hidden;">
						<div class="cp-loader">
							<h2 class="cp-loader-text"><?php esc_html_e( 'Loading...', 'convertpro-addon' ); ?></h2>
							<div class="cp-loader-wrap">
								<div class="cp-loader-bar">
									<div class="cp-loader-shadow"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="cp_ga_chart_div" ></div>
			</div>
		</div><!-- End Wrapper -->
	</div>
</div>
