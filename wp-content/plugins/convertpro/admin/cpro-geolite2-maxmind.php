<?php
/**
 * MaxMind GeoLite2 Database Page.
 *
 * @package ConvertPro
 */

?>

<div class="cp-maxmind-geolit2-wrap">
	<form method="post" class="cp-settings-form">
	<?php
	if ( is_multisite() ) {
		$cp_maxmind_geolocation_license_key = get_site_option( '_convertpro_maxmind_geolocation_settings' );
	} else {
		$cp_maxmind_geolocation_license_key = get_option( 'convertpro_maxmind_geolocation_settings' );
	}
	?>
		<div id="cpro-geolite2-message" class="">
		</div>

	<h3 class="cp-gen-set-title" ><?php esc_html_e( 'Convert Pro MaxMind Geolocation', 'convertpro' ); ?></h3>
		<p>
		<?php
			esc_html_e( 'An integration for utilizing MaxMind to do Geolocation lookups. Please note that this integration will only do Country lookups.', 'convertpro' );
		?>
		</p>
		<table class="cp-postbox-table form-table">
			<caption></caption>
			<tbody>
				<tr>
					<th scope="row">
						<label for="option-admin-menu-maxmind-license-key-page"><?php esc_html_e( 'MaxMind License Key', 'convertpro' ); ?></label>
					</th>
					<td>
						<input type="password" id="cp_maxmind_geolocation_license_key" name="cp_maxmind_geolocation_license_key" value="<?php echo esc_attr( $cp_maxmind_geolocation_license_key['license_key'] ); ?>" placeholder="MaxMind License Key" />
						<p class="description">
							<?php
								esc_html_e( 'The key that will be used when dealing with MaxMind Geolocation services. You can read how to generate one in ', 'convertpro' );
							?>
							<a target="_blank" rel="noopener noreferrer" href="https://www.convertpro.net/docs/integrate-maxmind-geolocation-in-convert-pro/">
							<?php
								esc_html_e( 'MaxMind Geolocation Integration documentation', 'convertpro' );
							?>
							</a>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="option-admin-menu-db-path-page"><?php esc_html_e( 'Database File Path', 'convertpro' ); ?></label>
					</th>
					<td>
					<?php
						$geolite_path = new CP_V2_Maxmind_Geolocation();
					?>
						<input type="text" value="<?php echo esc_attr( $geolite_path->get_cpro_database_path() ); ?>" readonly />
						<p class="description">
							<?php esc_html_e( 'The location that the MaxMind database should be stored.', 'convertpro' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
		<input type="hidden" name="curr_tab" value="1">
		<input type="hidden" name="cp_geolite2" value="1">
		<input type="hidden" name="cp-update-settings-nonce" id="cp-update-settings-nonce" value="<?php echo esc_attr( wp_create_nonce( 'cp-update-settings-nonce' ) ); ?>" />
		<button type="submit" class="cp-btn-primary cp-md-btn cp-button-style button-update-settings cp-submit-settings"><?php esc_html_e( 'Save Settings', 'convertpro' ); ?></button>
	</form>
</div> <!-- End Wrapper -->
