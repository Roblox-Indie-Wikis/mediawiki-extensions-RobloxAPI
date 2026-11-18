<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args\Types;

/**
 * Represents a thumbnail file format.
 */
class ThumbnailFormatArgument extends ChoiceArgument {

	/** @inheritDoc */
	public function __construct() {
		parent::__construct(
			'thumbnail-format',
			[ 'Png', 'Webp' ],
			caseSensitive: false,
		);
	}

}
