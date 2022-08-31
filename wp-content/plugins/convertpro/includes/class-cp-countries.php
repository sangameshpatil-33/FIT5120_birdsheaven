<?php
/**
 * Convert Pro countries
 *
 * @package Convert Pro
 * @version 1.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * The Convert Pro countries class stores country/state data.
 */
class CP_Countries {

	/**
	 * Locales list.
	 *
	 * @var array
	 */
	public $countries = array();

	/**
	 * Get all countries.
	 *
	 * @return array
	 */
	public function get_all_countries() {
		require_once CP_V2_BASE_DIR . 'includes/i18n/countries.php';
		$this->countries = bsf_all_countries();

		return $this->countries;
	}

	/**
	 * Get all european countries.
	 *
	 * @return array
	 */
	public function get_eu_countries() {
			require_once CP_V2_BASE_DIR . 'includes/i18n/countries.php';
			$this->countries = bsf_eu_countries();

		return $this->countries;
	}

}
