<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

$text_attributes = array(
	'id'      => 'cp_radio_image_par',
	'type'    => 'radio_image',
	'scripts' => 'radio_image.js',
	'styles'  => 'radio_image.css',
);
echo wp_json_encode( $text_attributes );
