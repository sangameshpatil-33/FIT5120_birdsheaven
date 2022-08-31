<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

$text_attributes = array(
	'id'      => 'cp_border_par',
	'type'    => 'border',
	'scripts' => 'border.js',
	'styles'  => 'border.css',
);

echo wp_json_encode( $text_attributes );
