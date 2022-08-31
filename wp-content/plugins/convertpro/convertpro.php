<?php
/**
 * Plugin Name: Convert Pro
 * Plugin URI: https://www.convertpro.net
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Version: 1.7.4
 * Description: Convert Pro is an advanced lead generation popup plugin with a drag and drop editor that helps you create beautiful popups and opt-in forms to boost your website conversions. With Convert Pro you can build email lists, drive traffic, promote videos, offer lead magnets and a lot more.
 * Text Domain: convertpro
 *
 * @package ConvertPro
 */

add_action( 'plugins_loaded', 'cp_load_convertpro', 1 );

// Activation.
register_activation_hook( __FILE__, 'activation_convert_pro' );

if ( ! function_exists( 'cp_load_convertpro' ) ) {

	/**
	 * Function to load packages
	 *
	 * @since 1.0
	 */
	function cp_load_convertpro() {
		require_once 'classes/class-cp-v2-loader.php';
		require_once 'classes/class-bsf-updater.php';
	}
}

/**
 * Function for activation hook
 *
 * @since 1.0
 */
function activation_convert_pro() {

	update_option( 'convert_pro_redirect', true );
	update_site_option( 'bsf_force_check_extensions', true );

	delete_option( 'cpro_hide_branding' );
	delete_site_option( '_cpro_hide_branding' );

	// On Activation - MaxMind Geolite2 Convert Pro database create directory in uploads folder.
	create_cpro_maxmind_folder_on_activation();

	global $wp_version;
	$wp  = '3.5';
	$php = '5.3.2';
	if ( version_compare( PHP_VERSION, $php, '<' ) ) {
		$flag = 'PHP';
	} elseif ( version_compare( $wp_version, $wp, '<' ) ) {
		$flag = 'WordPress';
	} else {
		return;
	}
	$version = 'PHP' === $flag ? $php : $wp;
	$file    = dirname( __FILE__ );

	define( 'CP_V2_DIR_NAME', plugin_basename( $file ) );
	define( 'CP_PRO_NAME', 'Convert Pro' );
	deactivate_plugins( CP_V2_DIR_NAME );

	wp_die(
		'<p><strong>' . esc_attr( CP_PRO_NAME ) . ' </strong> requires <strong>' . esc_attr( $flag ) . '</strong> version <strong>' . esc_attr( $version ) . '</strong> or greater. Please contact your host.</p>',
		'Plugin Activation Error',
		array(
			'response'  => 200,
			'back_link' => true,
		)
	);
}


/**
 * Function to create convertpro_uploads folder for MaxMind Geolite2 database.
 *
 * @since 1.4.5
 */
function create_cpro_maxmind_folder_on_activation() {

	// Allow us to easily interact with the filesystem.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
	global $wp_filesystem;

			// Install files and folders for uploading files and prevent hotlinking.
	$upload_dir = wp_upload_dir();
	$files      = array(
		'base'    => $upload_dir['basedir'] . '/convertpro_uploads',
		'file'    => '.htaccess',
		'content' => 'deny from all',
	);
	if ( wp_mkdir_p( $files['base'] ) && ! file_exists( trailingslashit( $files['base'] ) . $files['file'] ) ) {
		$wp_filesystem->put_contents( $files['base'] . '/' . $files['file'], $files['content'], FS_CHMOD_FILE );
	}
}
