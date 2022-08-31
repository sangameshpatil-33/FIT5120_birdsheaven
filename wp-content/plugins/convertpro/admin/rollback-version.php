<?php
/**
 * RollBack Version Page.
 *
 * @package ConvertPro
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
?>

<div class="cp-license-wrap cp-rollback-wrap">
	<h3 class="cp-gen-set-title"><?php esc_html_e( 'Rollback Version', 'convertpro' ); ?></h3>
	<p>
	<?php
	$bsf_product_id = 'convertpro';
	if ( ! bsf_display_rollback_version_form( $bsf_product_id ) ) {
		esc_html_e( 'Please activate the license for Rollback Feature.', 'convertpro' );
	} else {
		?>
	</p>
	<table class="cp-postbox-table form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php echo esc_html( CPRO_BRANDING_NAME ); ?>
				</th>
				<td>
				<?php
					$product_id = 'convertpro'; // e.g. convertpro.
					bsf_get_version_rollback_form( $product_id );
				?>
				</td>
			</tr>
			<?php

			if ( defined( 'CP_ADDON_VER' ) ) {
				?>
			<tr>
				<th scope="row">
					<?php echo esc_html( CPRO_BRANDING_NAME . ' Addon' ); ?>
				</th>
				<td>
					<?php
						$product_id = 'convertpro-addon'; // e.g. convertpro-addon.
						bsf_get_version_rollback_form( $product_id );
					?>
				</td>
			</tr>
				<?php
			}
			?>
		</tbody>
	</table>
		<?php
	}
	?>
</div>

