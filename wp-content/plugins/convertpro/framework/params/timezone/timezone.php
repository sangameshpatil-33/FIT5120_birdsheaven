<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

$text_attributes = array(
	'id'      => 'cp_timezone_par',
	'type'    => 'timezone',
	'scripts' => '',
	'styles'  => '',
);

echo wp_json_encode( $text_attributes );
