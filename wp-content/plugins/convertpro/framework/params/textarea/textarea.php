<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

$text_attributes = array(
	'id'      => 'cp_textarea_par',
	'type'    => 'textarea',
	'scripts' => '',
	'styles'  => '',
);

echo wp_json_encode( $text_attributes );
