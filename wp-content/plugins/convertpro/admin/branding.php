<?php
/**
 * Btanding Page.
 *
 * @package ConvertPro
 */

?>

<div class="cp-branding-wrap">
	<h3 class="cp-gen-set-title" ><?php esc_html_e( 'Branding', 'convertpro' ); ?></h3>
	<p>
	<?php
		$branding = Cp_V2_Loader::get_branding();

		$plugin_name        = $branding['name'];
		$plugin_desc        = $branding['description'];
		$plugin_author_name = $branding['author'];
		$plugin_author_url  = $branding['author_url'];
		$kb_url             = $branding['kb_url'];
		$support_url        = $branding['support_url'];
		$image_url          = $branding['image_url'];
		$addon_desc         = $branding['addon_desc'];

		$kbval             = ( isset( $branding['kb_enabled'] ) && '1' === $branding['kb_enabled'] ) ? 1 : 0;
		$supportval        = ( isset( $branding['support_enabled'] ) && '1' === $branding['support_enabled'] ) ? 1 : 0;
		$imageval          = ( isset( $branding['image_enabled'] ) && '1' === $branding['image_enabled'] ) ? 1 : 0;
		$hide_branding_val = ( isset( $branding['hide_branding'] ) && '1' === $branding['hide_branding'] ) ? 1 : 0;
		$hide_ref_temp_val = ( isset( $branding['hide_refresh_temp'] ) && '1' === $branding['hide_refresh_temp'] ) ? 1 : 0;

		$uniqid = uniqid();
	?>
	</p>
	<form method="post" class="cp-settings-form">
		<div class="debug-section">
			<table class="cp-postbox-table form-table">
				<tr class="cp-settings-row">
					<th scope="row">
						<label for="cpro_branding_plugin_name"><?php esc_html_e( 'Plugin Name', 'convertpro' ); ?>
						</label>
					</th>
					<td>
						<input type='text' name="cpro_branding_plugin_name" value="<?php echo esc_attr( $plugin_name ); ?>" placeholder="<?php echo esc_attr( CP_PRO_NAME ); ?>" id="cpro_branding_plugin_name">
					</td>
				</tr>
				<tr class="cp-settings-row">
					<th scope="row">
						<label for="cpro_branding_plugin_desc"><?php esc_html_e( 'Plugin Description', 'convertpro' ); ?>
						</label>
					</th>
					<td>
						<textarea name="cpro_branding_plugin_desc" id="cpro_branding_plugin_desc" placeholder="<?php echo esc_attr( CPRO_DESCRIPTION ); ?>" cols="30" rows="8"><?php echo esc_textarea( $plugin_desc ); ?></textarea>
					</td>
				</tr>
				<tr class="cp-settings-row">
					<th scope="row">
						<label for="cpro_branding_plugin_author_name"><?php esc_html_e( 'Author / Agency Name', 'convertpro' ); ?>
						</label>
					</th>
					<td>
						<input type='text' name="cpro_branding_plugin_author_name" value="<?php echo esc_attr( $plugin_author_name ); ?>" placeholder="<?php echo esc_attr( CPRO_AUTHOR_NAME ); ?>" id="cpro_branding_plugin_author_name">
					</td>
				</tr>
				<tr class="cp-settings-row">
					<th scope="row">
						<label for="cpro_branding_plugin_author_url"><?php esc_html_e( 'Author / Agency URL', 'convertpro' ); ?>
						</label>
					</th>
					<td>
						<input type='text' name="cpro_branding_plugin_author_url" value="<?php echo esc_url( $plugin_author_url ); ?>" placeholder="<?php echo esc_url( CPRO_AUTHOR_URL ); ?>" id="cpro_branding_plugin_author_url">
					</td>
				</tr>
				<tr class="cp-settings-row">
					<th scope="row">
						<label for="cpro_branding_enable_kb"><strong><?php esc_html_e( 'Enable Knowledge Base URL', 'convertpro' ); ?></strong></label>
					</th>
					<td>
						<div class="cp-switch-wrapper">
							<input type="text"  id="cpro_branding_enable_kb" class="form-control cp-input cp-switch-input" name="cpro_branding_enable_kb" value="<?php echo esc_attr( $kbval ); ?>" />
							<input type="checkbox" <?php checked( $branding['kb_enabled'], '1' ); ?> id="cpro_branding_enable_kb_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $kbval ); ?>" >
							<label class="cp-switch-btn checkbox-label" data-on=<?php esc_attr_e( 'ON', 'convertpro' ); ?>  data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cpro_branding_enable_kb" for="cpro_branding_enable_kb_btn_<?php echo esc_attr( $uniq ); ?>"></label>
						</div>
					</td>
				</tr>
				<tr class="cp-settings-row <?php echo ( ! $kbval ) ? 'cp-hidden' : ''; ?> cpro_branding_url_kb-row">
					<th scope="row">
						<label for="cpro_branding_enable_kb"><strong><?php esc_html_e( 'Knowledge Base URL', 'convertpro' ); ?></strong>
							<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'Enable this option to display Knowledge Base link in Help tab.', 'convertpro' ); ?>"><i class="dashicons dashicons-editor-help"></i></span>
						</label>
					</th>
					<td>
						<input type='text' name="cpro_branding_url_kb" value="<?php echo esc_url( $kb_url ); ?>" placeholder="<?php echo esc_url( CP_KNOWLEDGE_BASE_URL ); ?>" id="cpro_branding_url_kb">
					</td>
				</tr>
				<tr class="cp-settings-row">
					<th scope="row">
						<label for="cpro_branding_enable_support"><strong><?php esc_html_e( 'Enable Contact Support URL', 'convertpro' ); ?></strong>
						</label>
					</th>
					<td>
						<div class="cp-switch-wrapper">
							<input type="text"  id="cpro_branding_enable_support" class="form-control cp-input cp-switch-input" name="cpro_branding_enable_support" value="<?php echo esc_attr( $supportval ); ?>" />
							<input type="checkbox" <?php checked( $branding['support_enabled'], '1' ); ?> id="cpro_branding_enable_support_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $supportval ); ?>" >
							<label class="cp-switch-btn checkbox-label" data-on=<?php esc_attr_e( 'ON', 'convertpro' ); ?>  data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cpro_branding_enable_support" for="cpro_branding_enable_support_btn_<?php echo esc_attr( $uniq ); ?>"></label>
						</div>
					</td>
				</tr>
				<tr class="cp-settings-row <?php echo ( ! $supportval ) ? 'cp-hidden' : ''; ?> cpro_branding_url_support-row">
					<th scope="row">
						<label for="cpro_branding_enable_support"><strong><?php esc_html_e( 'Contact Support URL', 'convertpro' ); ?></strong>
							<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'Enable this option to display support link in Help tab.', 'convertpro' ); ?>"><i class="dashicons dashicons-editor-help"></i></span>
						</label>
					</th>
					<td>
						<input type='text' name="cpro_branding_url_support" value="<?php echo esc_url( $support_url ); ?>" placeholder="<?php echo esc_url( CP_SUPPORT_URL ); ?>" id="cpro_branding_url_support">
					</td>
				</tr>
				<tr class="cp-settings-row cpro_hide_refresh_template-row">
					<th scope="row">
						<label for="cpro_hide_refresh_template"><strong><?php esc_html_e( 'Hide refresh template cloud option', 'convertpro' ); ?></strong>
							<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'Enable this option to hide refresh template cloud option for call-to-actions.', 'convertpro' ); ?>"><i class="dashicons dashicons-editor-help"></i></span>
						</label>
					</th>
					<td>
						<div class="cp-switch-wrapper">
							<input type="text"  id="cpro_hide_refresh_template" class="form-control cp-input cp-switch-input" name="cpro_hide_refresh_template" value="<?php echo esc_attr( $hide_ref_temp_val ); ?>" />
							<input type="checkbox" <?php checked( $branding['hide_refresh_temp'], '1' ); ?> id="cpro_hide_refresh_template_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $hide_ref_temp_val ); ?>" >
							<label class="cp-switch-btn checkbox-label" data-on=<?php esc_attr_e( 'ON', 'convertpro' ); ?>  data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cpro_hide_refresh_template" for="cpro_hide_refresh_template_btn_<?php echo esc_attr( $uniq ); ?>"></label>
						</div>
					</td>
				</tr>
				<?php if ( class_exists( 'CP_Addon_Loader' ) ) { ?>
					<tr class="cp-settings-row cpro_addon_branding_desc-row">
						<th scope="row">
							<label for="cpro_branding_enable_support"><strong><?php esc_html_e( 'Plugin Addon Description', 'convertpro' ); ?></strong>
							</label>
						</th>
						<td>
							<textarea name="cpro_addon_branding_plugin_desc" id="cpro_addon_branding_plugin_desc" placeholder="<?php echo esc_attr( CPRO_ADDON_DESC ); ?>" cols="30" rows="8"><?php echo esc_textarea( $addon_desc ); ?></textarea>
						</td>
					</tr>
				<?php } ?>

				<tr class="cp-settings-row">
					<th scope="row">
						<label for="cpro_branding_enable_image"><strong><?php esc_html_e( 'Enable Custom Image', 'convertpro' ); ?></strong>
							<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_html_e( 'Enable this option to display support link in Help tab.', 'convertpro' ); ?>"><i class="dashicons dashicons-editor-help"></i></span>
						</label>
					</th>
					<td>
						<div class="cp-switch-wrapper">
							<input type="text"  id="cpro_branding_enable_image" class="form-control cp-input cp-switch-input" name="cpro_branding_enable_image" value="<?php echo esc_attr( $imageval ); ?>" />
							<input type="checkbox" <?php checked( $branding['image_enabled'], '1' ); ?> id="cpro_branding_enable_image_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $imageval ); ?>" >
							<label class="cp-switch-btn checkbox-label" data-on=<?php esc_attr_e( 'ON', 'convertpro' ); ?>  data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cpro_branding_enable_image" for="cpro_branding_enable_image_btn_<?php echo esc_attr( $uniq ); ?>"></label>
						</div>
					</td>
				</tr>
				<tr class="cp-settings-row <?php echo ( ! $imageval ) ? 'cp-hidden' : ''; ?> cpro_branding_url_image-row">
					<th scope="row">
						<label for="cpro_branding_enable_image"><strong><?php esc_html_e( 'Custom Image URL', 'convertpro' ); ?></strong>
						</label>
					</th>
					<td>
						<input type='text' name="cpro_branding_url_image" value="<?php echo esc_url( $image_url ); ?>" placeholder="<?php echo esc_attr( CP_IMAGE_URL ); ?>" id="cpro_branding_url_image">
					</td>
				</tr>

				<tr class="cp-settings-row">
					<th scope="row">
						<label for="cpro_hide_branding"><strong><?php esc_html_e( 'Hide White Label Settings', 'convertpro' ); ?></strong>
							<span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php esc_attr_e( 'Enable this option to hide White Label settings. Re-activate the plugin to enable this form again.', 'convertpro' ); ?>"><i class="dashicons dashicons-editor-help"></i></span>
						</label>
					</th>
					<td>
						<div class="cp-switch-wrapper">
							<input type="text"  id="cpro_hide_branding" class="form-control cp-input cp-switch-input" name="cpro_hide_branding" value="<?php echo esc_attr( $hide_branding_val ); ?>" />
							<input type="checkbox" <?php checked( $branding['hide_branding'], '1' ); ?> id="cpro_hide_branding_btn_<?php echo esc_attr( $uniq ); ?>"  class="ios-toggle cp-switch-input switch-checkbox" value="<?php echo esc_attr( $hide_branding_val ); ?>" >
							<label class="cp-switch-btn checkbox-label" data-on=<?php esc_attr_e( 'ON', 'convertpro' ); ?>  data-off="<?php esc_attr_e( 'OFF', 'convertpro' ); ?>" data-id="cpro_hide_branding" for="cpro_hide_branding_btn_<?php echo esc_attr( $uniq ); ?>"></label>
						</div>
					</td>
				</tr>

			</table>
		</div>
		<p class="submit">
			<input type="hidden" name="curr_tab" value="1">
			<input type="hidden" name="cp_branding" value="1">
			<input type="hidden" name="cp-update-settings-nonce" id="cp-update-settings-nonce" value="<?php echo esc_attr( wp_create_nonce( 'cp-update-settings-nonce' ) ); ?>" />
			<button type="submit" class="cp-btn-primary cp-md-btn cp-button-style button-update-settings cp-submit-settings"><?php esc_html_e( 'Save Settings', 'convertpro' ); ?></button>
		</p>
	</form>

</div> <!-- End Wrapper -->
