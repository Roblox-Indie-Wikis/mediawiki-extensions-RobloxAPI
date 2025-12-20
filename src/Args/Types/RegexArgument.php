<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args\Types;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentParserContext;
use StatusValue;

/**
 * Represents an argument that must match a regular expression.
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

	/** @inheritDoc */
	public function validate( ArgumentParserContext $ctx, string $value ): StatusValue {
		if ( preg_match( $this->pattern, $value ) ) {
			return StatusValue::newGood( $value );
		} else {
			return $this->invalidValue( $value, $this->errorMessage );
		}
	}

}
