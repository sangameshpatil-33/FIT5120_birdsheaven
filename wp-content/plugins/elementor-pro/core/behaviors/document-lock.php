<?php
namespace ElementorPro\Core\Behaviors;

use Elementor\Core\Base\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Document_Lock extends Feature_Lock {

	public function __construct( Document $document ) {
		parent::__construct( [
			'type' => $document::get_type(),
		] );
	}
}
