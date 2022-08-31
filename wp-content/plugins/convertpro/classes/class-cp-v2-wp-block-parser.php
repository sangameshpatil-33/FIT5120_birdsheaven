<?php
/**
 * Convert Pro Block parser
 *
 * @package Convert Pro
 */

/**
 * Convert Pro Block Parser
 *
 * @since 1.7.0
 */
class CP_V2_WP_Block_Parser extends WP_Block_Parser {


	/**
	 * Parse block document.
	 *
	 * @param string $document block document.
	 */
	public function parse( $document ) {
		$result = parent::parse( $document );

		$current_index         = 0;
		$current_heading_index = 0;

		foreach ( $result as $index => $first_level_block ) {
			$result[ $index ]['firstLevelBlock'] = true;
			$inner_html                          = trim( $first_level_block['innerHTML'] );

			if ( ! empty( $inner_html ) ) {
				$result[ $index ]['firstLevelBlockIndex'] = $current_index++;

				if (
					strpos( $first_level_block['blockName'], 'heading' ) !== false
					||
					strpos( $first_level_block['blockName'], 'headline' ) !== false
					||
					in_array(
						substr( $inner_html, 0, 3 ),
						array(
							'<h1',
							'<h2',
							'<h3',
							'<h4',
							'<h5',
							'<h6',
						),
						true
					)
				) {
					$result[ $index ]['firstLevelHeadingIndex'] = $current_heading_index++;
				}
			}
		}

		return $result;
	}
}
