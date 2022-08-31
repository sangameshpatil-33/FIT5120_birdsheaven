<?php
/**
 * Convert Pro Addon loader Class
 *
 * @package Convert Pro Addon
 */

/**
 * Provide Extension related data.
 *
 * @since 1.0
 */
final class CP_Addon_Module_Loader {

	/**
	 * Construct
	 */
	public function __construct() {

		$this->load_extensions();
	}

	/**
	 * Load Extensions
	 *
	 * @return void
	 */
	public function load_extensions() {

		$enabled_extension = CP_Addon_Extension::get_enabled_extension();

		if ( 0 < count( $enabled_extension ) ) {

			foreach ( $enabled_extension as $slug => $value ) {

				if ( false === filter_var( ( ! empty( $value ) ), FILTER_VALIDATE_BOOLEAN ) ) {
					continue;
				}

				$extension_path = CP_ADDON_DIR . 'addons/' . esc_attr( $slug ) . '/cpro-' . esc_attr( $slug ) . '.php';
				// Check for the extension.
				if ( file_exists( $extension_path ) ) {
					require_once $extension_path;
				}
			}
		}
	}
}

new CP_Addon_Module_Loader();

