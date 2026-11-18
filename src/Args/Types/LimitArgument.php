<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args\Types;

/**
 * Argument type for limiting results.
 */
class LimitArgument extends ChoiceArgument {

	public function __construct( string ...$choices ) {
		parent::__construct(
			'limit',
			$choices
		);
	}

}
