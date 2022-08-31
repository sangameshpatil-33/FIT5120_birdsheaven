<?php
/**
 * Create new design file.
 *
 * @package ConvertPro
 */

if ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && ! wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) ) {
	die( 'No direct script access allowed!' );
}

$view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : '';

if ( 'template' === $view ) {
		require_once CP_V2_BASE_DIR . 'admin/template.php';
} else {

	// load popup types.
	$types_dir = CP_FRAMEWORK_DIR . 'types/';

	$types = array(
		'modal_popup',
		'info_bar',
		'slide_in',
		'before_after',
		'inline',
		'widget',
		'welcome_mat',
		'full_screen',
	);

	foreach ( $types as $popup_type ) {
		$file_path = str_replace( '_', '-', $popup_type );
		$file_path = 'class-cp-' . $file_path;
		if ( file_exists( $types_dir . $file_path . '.php' ) ) {
			require_once $types_dir . $file_path . '.php';
		}
	}

	?>

<div class="wrap about-wrap about-cp bend">
	<h2 class="cp-sub-head"><?php esc_html_e( 'Select a Call-to-action Type', 'convertpro' ); ?></h2>
	<div class="wrap-container">

		<div class="bend-content-wrap">

			<div class="container cp-dashboard-content">
				<?php
				$popup_types = cp_Framework::$types;

				$template_page_url = CP_V2_Tab_Menu::get_page_url( 'create-new' );
				?>

				<div class="cp-popup-container">
					<?php
					foreach ( $popup_types as $slug => $settings ) {

						$template_page_url = add_query_arg(
							array(
								'view' => 'template',
								'type' => $slug,
							),
							$template_page_url
						);
						?>
					<div class="cp-col-4 cp-popup-style">
						<div class="cp-popup-type-content">
							<a href="<?php echo esc_url( $template_page_url ); ?>">
								<?php
									$popup_title = $settings['title'];
									$description = $settings['description'];
								?>
								<h3 class="cp-popup-title"><?php echo esc_html( $popup_title ); ?> </h3>
								<p class="cp-type-description"><?php echo esc_html( $description ); ?></p>
								<button class="cp-button-style cp-btn-block cp-btn-primary"><?php esc_html_e( 'Select', 'convertpro' ); ?></button>
							</a>
						</div>
					</div>

					<?php } ?> 
				</div>
			</div><!-- cp-started-content -->
		</div><!-- bend-content-wrap -->
	</div><!-- .wrap-container -->
</div>
	<?php if ( isset( $_GET['cp_debug'] ) ) { ?>
	<div class="cp-clear-template-data">
		<a href="#" style="position: absolute; bottom: 10px; right: 10px; top: auto; left: auto;" data-modal-type="all" class="cp-btn-primary cp-sm-btn cp-button-style cp-remove-local-templates"><?php esc_html_e( 'Delete Template Data', 'convertpro' ); ?></a>
		<?php wp_nonce_field( 'cpro_delete_template_data', 'cpro_delete_template_data_nonce' ); ?>
	</div>
	<?php } ?>
	<?php do_action( 'cppro_create_new_footer' ); ?>
<?php }
?>
