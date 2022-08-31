<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

// Add new input type "number".
if ( function_exists( 'cp_add_input_type' ) ) {
	cp_add_input_type( 'numberfield', 'cp_v2_numberfield_settings_field' );
	add_action( 'admin_enqueue_scripts', 'cp_v2_numberfield_admin_scripts' );
}

/**
 * Function Name: cp_v2_numberfield_admin_scripts.
 * Function Description: cp_v2_numberfield_admin_scripts.
 *
 * @param string $hook string parameter.
 */
function cp_v2_numberfield_admin_scripts( $hook ) {
	$dev_mode = get_option( 'cp_dev_mode' );

	if ( '1' === $dev_mode ) {
		wp_enqueue_script( 'cp-numberfield', plugins_url( 'numberfield.js', __FILE__ ), array(), '1.0.0', true );
	}
}

/**
 * Function Name: cp_v2_numberfield_settings_field.
 * Function Description: Function to handle new input type.
 *
 * @param string $name string parameter.
 * @param string $settings string parameter.
 * @param string $value string parameter.
 */
function cp_v2_numberfield_settings_field( $name, $settings, $value ) {
	$input_name = $name;
	$value      = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
	$type       = isset( $settings['type'] ) ? $settings['type'] : '';
	$class      = isset( $settings['class'] ) ? $settings['class'] : '';
	$suffix     = isset( $settings['suffix'] ) ? $settings['suffix'] : '';
	$map_style  = isset( $settings['map_style'] ) ? wp_json_encode( $settings['map_style'] ) : '';
	$suffixes   = explode( ',', $suffix );

	ob_start();

	?>

	<div class="cp-numberfield-container cp-param-inner" data-id="cp_<?php echo esc_attr( $input_name ); ?>" data-units="<?php echo esc_attr( $suffix ); ?>">
		<div class="cp-fields-param-units number-field-param-units">
			<?php foreach ( $suffixes as $suffix ) { ?>	
				<span class="cp-units cp-unit-<?php echo esc_attr( $suffix ); ?>"><?php echo esc_attr( strtoupper( $suffix ) ); ?></span>
			<?php } ?>
		</div>
		<input type="hidden" data-type="<?php echo esc_attr( $type ); ?>" id="cp_<?php echo esc_attr( $input_name ); ?>" data-mapstyle="<?php echo esc_attr( htmlspecialchars( $map_style, ENT_QUOTES, 'UTF-8' ) ); ?>" class="form-control cp-input cp-<?php echo esc_attr( $type ) . ' ' . esc_attr( $input_name ) . ' ' . esc_attr( $type ) . ' ' . esc_attr( $class ); ?>" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $input_name ); ?>" />
		<input type="number" class="cp-number-param-temp" value="<?php echo esc_attr( $value ); ?>" /></div>

	<?php

	$output = ob_get_clean();

	return $output;
}
