<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args\Types;

/**
 * Represents a boolean argument, either true or false.
 *
 * validate() intentionally returns a string rather than a boolean so it's easier to use it
 * as a query parameter in URLs.
 */
class BooleanArgument extends ChoiceArgument {

	/** @inheritDoc */
	public function __construct() {
		parent::__construct(
			'boolean',
			[ 'true', 'false' ],
			caseSensitive: false
		);
	}

}
