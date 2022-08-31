<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

$text_attributes = array(
	'id'      => 'cp_multiinput_par',
	'type'    => 'multiinput',
	'scripts' => '',
	'styles'  => '',
);

echo wp_json_encode( $text_attributes );
