<?php
/**
 * Theme/Plugin auto version update & backward compatibility.
 *
 * @package     ConvertPro Addon
 * @author      Brainstormforce
 * @link        https://www.convertpro.net
 * @since       ConvertPro 1.0.0
 */

if ( ! class_exists( 'CP_Addon_Auto_Update' ) ) :

	/**
	 * CP_Addon_Auto_Update initial setup
	 *
	 * @since 1.0.0
	 */
	class CP_Addon_Auto_Update {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			// Theme Updates.
			add_action( 'init', __CLASS__ . '::init' );

		}

		/**
		 * Implement plugin auto update logic.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function init() {

			do_action( 'cpro_addon_before_update' );

			// Get auto saved version number.
			$saved_version = get_option( 'cpro-addon-auto-version' );

			// Remove Mautic API option for new users.
			if ( version_compare( $saved_version, '1.2.2', '<' ) ) {
				add_option( 'cpro-remove-mautic-api-option', true );
			}

			// If equals then return.
			if ( version_compare( $saved_version, CP_ADDON_VER, '=' ) ) {
				return;
			}

			// Update auto saved version number.
			update_option( 'cpro-addon-auto-version', CP_ADDON_VER );

			do_action( 'cpro_addon_after_update' );

		}
	}

endif;

/**
 * Kicking this off by calling 'get_instance()' method
 */
CP_Addon_Auto_Update::get_instance();
