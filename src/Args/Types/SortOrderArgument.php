<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args\Types;

/**
 * Represents a sort order argument, either Asc or Desc.
 */
class SortOrderArgument extends ChoiceArgument {

	/** @inheritDoc */
	public function __construct() {
		parent::__construct(
			'sort-order',
			[ 'Asc', 'Desc' ]
		);
	}

}
