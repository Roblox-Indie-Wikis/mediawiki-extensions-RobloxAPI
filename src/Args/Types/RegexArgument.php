<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args\Types;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentParserContext;
use StatusValue;

/**
 * Represents an argument that must match a regular expression.
 * @extends AbstractArgument<string>
 */
class RegexArgument extends AbstractArgument {

	/** @inheritDoc */
	public function __construct(
		string $key,
		private readonly string $pattern,
		private readonly string $errorMessage = 'robloxapi-error-invalid-generic-argument'
	) {
		parent::__construct( $key );
	}

	/**
	 * @return StatusValue<string>
	 * @inheritDoc
	 */
	public function validate( ArgumentParserContext $ctx, string $value ): StatusValue {
		if ( preg_match( $this->pattern, $value ) ) {
			return StatusValue::newGood( $value );
		} else {
			return $this->invalidValue( $value, $this->errorMessage );
		}
	}

}
