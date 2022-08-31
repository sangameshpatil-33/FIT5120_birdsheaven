<?php
/**
 * Convert Pro Addon view Extension Form
 *
 * @package Convert Pro Addon
 */

add_thickbox();
?>
	<?php
	$all_extensions     = CP_Addon_Extension::get_extension();
	$enabled_extensions = CP_Addon_Extension::get_enabled_extension();
	?>
	<div class="cp-addon-wrap">
	<div id="poststuff">
		<div id="post-body" class="columns-1">
			<div id="post-body-content">
				<div class="cp-addon-listing-wrap">
					<ul class="cp-addon-list">
						<?php
						foreach ( $all_extensions as $extension => $value ) :
							if ( is_multisite() && 'white-label' === $extension ) {
								continue;
							}
							$btn_class        = 'activate-module';
							$extension_status = 'deactive';
							$btn_string       = __( 'Activate', 'convertpro-addon' );

							if ( array_key_exists( $extension, $enabled_extensions ) && ! empty( $enabled_extensions[ $extension ] ) ) {
								$extension_status = 'active';
								$btn_class        = 'deactivate-module';
								$btn_string       = __( 'Deactivate', 'convertpro-addon' );
							}
							?>

						<li id="<?php echo esc_attr( $extension ); ?>" class="<?php echo esc_attr( $extension_status ); ?>">
							<span class="inner">
								<span class="thumb">
									<img class="wp-ui-highlight" src="<?php echo esc_attr( $value['icon'] ); ?>" alt="wp-ui-highlight">
								</span>
								<span class="content">
									<span class="status wp-ui-highlight"><?php esc_html_e( 'Activated', 'convertpro-addon' ); ?></span>
									<h3><?php echo esc_html( $value['title'] ); ?></h3>
									<p><?php echo esc_html( $value['description'] ); ?></p>
									<span class="<?php echo esc_attr( $btn_class ); ?> button button-medium"><?php echo esc_html( $btn_string ); ?></span>
								</span>
							</span>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
		<!-- /post-body -->
		<br class="clear">
	</div>
</div>
