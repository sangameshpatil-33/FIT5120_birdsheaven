<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

// Add new input type "target_geo_rule".
if ( function_exists( 'cp_add_input_type' ) ) {
	cp_add_input_type( 'target_geo_rule', 'cp_v2_target_geo_rule_settings_field' );
	add_action( 'admin_enqueue_scripts', 'framework_target_geo_rule_admin_styles' );
}

/**
 * Function Name: framework_target_geo_rule_admin_styles.
 * Function Description: framework_target_geo_rule_admin_styles.
 *
 * @param string $hook string parameter.
 */
function framework_target_geo_rule_admin_styles( $hook ) {
	$dev_mode = get_option( 'cp_dev_mode' );
	if ( '1' === $dev_mode ) {
		wp_enqueue_script( 'cp-target_geo_rule-script', plugins_url( 'target_geo_rule.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
	}
}

/**
 * Function Name: cp_v2_target_geo_rule_settings_field.
 * Function Description: Function to handle new input type.
 *
 * @param string $name string parameter.
 * @param string $settings string parameter.
 * @param string $value string parameter.
 */
function cp_v2_target_geo_rule_settings_field( $name, $settings, $value ) {
	$input_name     = $name;
	$type           = isset( $settings['type'] ) ? $settings['type'] : 'target_geo_rule';
	$class          = isset( $settings['class'] ) ? $settings['class'] : '';
	$rule_type      = isset( $settings['rule_type'] ) ? $settings['rule_type'] : 'target_geo_rule';
	$add_rule_label = isset( $settings['add_rule_label'] ) ? $settings['add_rule_label'] : __( 'Add Rule', 'convertpro' );
	$saved_values   = json_decode( $value, true );
	$output         = '';

	$selection_options = array(
		'basic-geo' => array(
			'label' => __( 'Basic', 'convertpro' ),
			'value' => array(
				'basic-all-countries' => __( 'All Countries', 'convertpro' ),
				'basic-eu'            => __( 'Only EU Countries', 'convertpro' ),
				'basic-non-eu'        => __( 'Non EU Countries', 'convertpro' ),
			),
		),
	);

	$selection_options['specific-geo-target'] = array(
		'label' => __( 'Specific Target', 'convertpro' ),
		'value' => array(
			'specifics-geo' => __( 'Target Specific Countries', 'convertpro' ),
		),
	);

	/* WP Template Format */
	$output         .= '<script type="text/html" id="tmpl-cp-target-geo-rule-condition">';
		$output     .= '<div class="cp-target-geo-rule-condition cp-target-geo-rule-{{data.id}}" data-rule="{{data.id}}" >';
			$output .= '<span class="target_geo_rule-condition-delete dashicons dashicons-no-alt"></span>';
			/* Condition Selection */
			$output         .= '<div class="target_geo_rule-condition-wrap" >';
				$output     .= '<select name="' . esc_attr( $input_name ) . '_on" class="target_geo_rule-condition form-control cp-input">';
					$output .= '<option value="">' . __( 'Select', 'convertpro' ) . '</option>';

	foreach ( $selection_options as $group => $group_data ) {

			$output .= '<optgroup label="' . $group_data['label'] . '">';
		foreach ( $group_data['value'] as $opt_key => $opt_value ) {
			$output .= '<option value="' . $opt_key . '">' . $opt_value . '</option>';
		}
		$output .= '</optgroup>';
	}
				$output .= '</select>';
			$output     .= '</div>';

			/* specific-countries page selection */
			$output     .= '<div class="target_geo_rule-specific-countries-page-wrap" style="display:none">';
				$output .= '<select name="' . esc_attr( $input_name ) . '_on_specifics-geo_{{data.id}}" class="target-geo-rule-select2 target_geo_rule-specific-countries-page form-control cp-input " multiple="multiple">';
				$output .= '</select>';
			$output     .= '</div>';
		$output         .= '</div>';
	$output             .= '</script>';

	/* Wrapper Start */
	$output     .= '<div class="cp-target-geo-rule-wrapper cp-target-geo-rule-' . $rule_type . '-on-wrap" data-type="' . $rule_type . '">';
		$output .= '<input type="hidden" class="form-control cp-input cp-target_geo_rule-input" name="' . esc_attr( $input_name ) . '" value=' . $value . ' />';

		$output     .= '<div class="cp-target-geo-rule-selector-wrapper cp-target-geo-rule-' . $rule_type . '-on">';
			$output .= cp_v2_generate_target_geo_rule_selector( $rule_type, $selection_options, $input_name, $saved_values, $add_rule_label );
		$output     .= '</div>';

	/* Wrapper end */
	$output .= '</div>';

	return $output;

	/* ======================================================================= */

}

/**
 * Function Name: cp_v2_generate_target_geo_rule_selector.
 * Function Description: Post type object options.
 *
 * @param object $type rule parameter.
 * @param object $selection_options options for selection.
 * @param object $input_name input name.
 * @param object $saved_values saved settings value.
 * @param object $add_rule_label label.
 */
function cp_v2_generate_target_geo_rule_selector( $type, $selection_options, $input_name, $saved_values, $add_rule_label ) {

	$output = '<div class="target_geo_rule-builder-wrap">';

	if ( ! is_array( $saved_values ) || ( is_array( $saved_values ) && empty( $saved_values ) ) ) {

		$saved_values    = array();
		$saved_values[0] = array(
			'type'     => '',
			'specific' => null,
		);
	}
	foreach ( $saved_values as $index => $data ) {

		$output .= '<div class="cp-target-geo-rule-condition cp-target-geo-rule-' . $index . '" data-rule="' . $index . '" >';
			/* Condition Selection. */
			$output         .= '<span class="target_geo_rule-condition-delete dashicons dashicons-no-alt"></span>';
			$output         .= '<div class="target_geo_rule-condition-wrap" >';
				$output     .= '<select name="' . esc_attr( $input_name ) . '_on" class="target_geo_rule-condition form-control cp-input">';
					$output .= '<option value="">' . __( 'Select', 'convertpro' ) . '</option>';

		foreach ( $selection_options as $group => $group_data ) {

				$output .= '<optgroup label="' . $group_data['label'] . '">';
			foreach ( $group_data['value'] as $opt_key => $opt_value ) {
				$output .= '<option value="' . $opt_key . '" ' . selected( $data['type'], $opt_key, false ) . '>' . $opt_value . '</option>';
			}
			$output .= '</optgroup>';
		}
				$output .= '</select>';
			$output     .= '</div>';

			/* specific-countries page selection */
			$output     .= '<div class="target_geo_rule-specific-countries-page-wrap" style="display:none">';
				$output .= '<select name="' . esc_attr( $input_name ) . '_on_specifics-geo_' . $index . '" class="target-geo-rule-select2 target_geo_rule-specific-countries-page form-control cp-input " multiple="multiple">';

		if ( null !== $data['specific'] && is_array( $data['specific'] ) ) {

			require_once CP_V2_BASE_DIR . 'includes/class-cp-countries.php';

			$get_all_countries = new CP_Countries();

			$arr_all_countries = $get_all_countries->get_all_countries();

			foreach ( $data['specific'] as $data_key => $sel_value ) {

				foreach ( $arr_all_countries as $key => $value ) {
					if ( $sel_value === $key ) {
						$output .= '<option value="' . $sel_value . '" ' . selected( $sel_value, $key, false ) . ' >' . $value . '</option>';
					}
				}
			}
		}

				$output .= '</select>';
			$output     .= '</div>';
		$output         .= '</div>';

		$new_index = $index + 1;
	}

	$output .= '</div>';

	/* Add new rule */
	$output     .= '<div class="target_geo_rule-add-rule-wrap">';
		$output .= '<a href="#" class="button" data-rule-id="' . $index . '" data-rule-type="' . $type . '">' . $add_rule_label . '</a>';
	$output     .= '</div>';

	if ( 'display' === $type ) {
		/* Add new rule */
		$output     .= '<div class="target_geo_rule-add-exclusion-rule">';
			$output .= '<a href="#" class="button">' . __( 'Add Exclusion Rule', 'convertpro' ) . '</a>';
		$output     .= '</div>';
	}

	return $output;
}
