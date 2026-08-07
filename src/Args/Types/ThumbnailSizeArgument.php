<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args\Types;

/**
 * Represents a thumbnail size (width x height).
 */
class ThumbnailSizeArgument extends RegexArgument {

	// TODO ChoiceArgument instead?

	/** @inheritDoc */
	public function __construct() {
		parent::__construct(
			'thumbnail-size',
			/** @lang RegExp */ '/^\d{1,3}x\d{1,3}$/'
		);
	}

}
