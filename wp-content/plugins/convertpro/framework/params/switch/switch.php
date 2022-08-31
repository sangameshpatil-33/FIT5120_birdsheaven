<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

$text_attributes = array(
	'id'      => 'cp_switch_par',
	'type'    => 'switch',
	'scripts' => 'switch.js',
	'styles'  => '',
);

echo wp_json_encode( $text_attributes );
