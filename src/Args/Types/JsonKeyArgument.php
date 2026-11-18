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
 * Represents an argument that is a JSON key.
 * @extends AbstractArgument<array<string|int>>
 */
class JsonKeyArgument extends AbstractArgument {

	/** @inheritDoc */
	public function __construct() {
		parent::__construct( 'json-key' );
	}

	/**
	 * @return StatusValue<array<string|int>>
	 * @inheritDoc
	 */
	public function validate( ArgumentParserContext $ctx, string $value ): StatusValue {
		$values = explode( '->', $value );

		$result = [];
		foreach ( $values as $val ) {
			if ( $val === '' ) {
				return $this->invalidValue( $value );
			}
			if ( ctype_digit( $val ) ) {
				$result[] = (int)$val;
			} else {
				$result[] = $val;
			}
		}

		return StatusValue::newGood( $result );
	}

}
