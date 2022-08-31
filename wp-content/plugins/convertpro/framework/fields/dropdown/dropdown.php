<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

// Add new input type "dropdown".
if ( function_exists( 'cp_add_input_type' ) ) {
	cp_add_input_type( 'dropdown', 'cp_v2_dropdown_settings_field' );
}

/**
 * Function Name: cp_v2_dropdown_settings_field.
 * Function Description: Function to handle new input type.
 *
 * @param string $name string parameter.
 * @param string $settings string parameter.
 * @param string $value string parameter.
 */
function cp_v2_dropdown_settings_field( $name, $settings, $value ) {
	$input_name  = $name;
	$type        = isset( $settings['type'] ) ? $settings['type'] : '';
	$class       = isset( $settings['class'] ) ? $settings['class'] : '';
	$options     = isset( $settings['options'] ) ? $settings['options'] : '';
	$multiselect = isset( $settings['multiselect'] ) ? ' multiple ' : '';

	$css_selector = isset( $settings['css_selector'] ) ? $settings['css_selector'] : '';
	$css_property = isset( $settings['css_property'] ) ? $settings['css_property'] : '';
	$map_style    = isset( $settings['map_style'] ) ? wp_json_encode( $settings['map_style'] ) : '';

	$output = '<p><select autocomplete="on"' . $multiselect . 'data-mapstyle="' . htmlspecialchars( $map_style, ENT_QUOTES, 'UTF-8' ) . '" name="' . $input_name . '" id="cp_' . $input_name . '" data-type="dropdown" class="form-control cp-input cp-select ' . $input_name . ' ' . $type . '" >';

	$value = explode( ',', $value );

	foreach ( $options as $key => $val ) {
		$output .= '<option class="cp_' . $key . '" value="' . $key . '"' . selected( in_array( $key, $value, true ), 1, false ) . '>' . htmlspecialchars( $val, ENT_QUOTES, 'UTF-8' ) . '</option>';
	}
	$output .= '</select></p>';
	return $output;
}
