<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

// Add new input type "pages".
if ( function_exists( 'cp_add_input_type' ) ) {
	cp_add_input_type( 'pages', 'cp_v2_pages_settings_field' );
}

/**
 * Function Name: cp_v2_pages_settings_field.
 * Function Description: Function to handle new input type.
 *
 * @param string $name string parameter.
 * @param string $settings string parameter.
 * @param string $value string parameter.
 */
function cp_v2_pages_settings_field( $name, $settings, $value ) {
	$input_name = $name;
	$type       = isset( $settings['type'] ) ? $settings['type'] : '';
	$class      = isset( $settings['class'] ) ? $settings['class'] : '';
	ob_start();
	?>
<select name="<?php echo esc_attr( $input_name ); ?>" id="cp_<?php echo esc_attr( $input_name ); ?>" class="select2-pages-dropdown form-control cp-input <?php echo esc_attr( 'cp-' . $type . ' ' . $input_name . ' ' . $type . ' ' . $class ); ?>" multiple="multiple" style="width:260px;"> 
	<optgroup label="<?php echo esc_attr( __( 'Pages', 'convertpro' ) ); ?>">
	<?php
	$pages   = get_pages();
	$val_arr = explode( ',', $value );
	foreach ( $pages as $page ) {
		$selected = ( in_array( $page->ID, $val_arr, true ) ) ? 1 : '';
		?>
		<option value ="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $selected, 1 ); ?> > <?php echo esc_html( $page->post_title ); ?> </option>
		<?php
	}
	?>
	</optgroup>
	<optgroup label="<?php echo esc_attr( __( 'Posts', 'convertpro' ) ); ?>">
	<?php
	$args    = array(
		'posts_per_page' => -1,
	);
	$myposts = get_posts( $args );
	foreach ( $myposts as $post ) {
		$selected = ( in_array( $post->ID, $val_arr, true ) ) ? 1 : '';
		?>
		<option value ="<?php echo esc_attr( $post->ID ); ?>" <?php selected( $selected, 1 ); ?> > <?php echo esc_html( $post->post_title ); ?> </option>
		<?php
	}
	?>
	</optgroup>
</select>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('select.select2-pages-dropdown').cpselect2({
		placeholder: "<?php esc_html_e( 'Select pages / posts', 'convertpro' ); ?>",
	});
});
</script>
	<?php
	return ob_get_clean();
}
