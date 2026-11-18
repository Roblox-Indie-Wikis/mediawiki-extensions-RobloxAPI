<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args\Types;

/**
 * Represents a Roblox username argument.
 */
class UsernameArgument extends RegexArgument {

	/** @inheritDoc */
	public function __construct() {
		parent::__construct(
			'username',
			/** @lang RegExp */ '/^(?=^[^_]+_?[^_]+$)\w{3,20}$/'
		);
	}

}
