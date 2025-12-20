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
use Wikimedia\Message\MessageValue;

/**
 * Represents an argument that must be one of a set of choices.
 */
class ChoiceArgument extends AbstractArgument {

	/**
	 * @param string[] $choices The valid choices for this argument.
	 * @param string $errorMessage The error message to use if the argument is invalid.
	 * @inheritDoc
	 */
	public function __construct(
		string $key,
		private array $choices,
		private readonly string $errorMessage = 'robloxapi-error-invalid-choice-argument',
		private readonly bool $caseSensitive = true,
	) {
		parent::__construct( $key );
		if ( !$caseSensitive ) {
			$this->choices = array_map( 'strtolower', $choices );
		}
	}

	/** @inheritDoc */
	public function validate( ArgumentParserContext $ctx, string $value ): StatusValue {
		if ( !$this->caseSensitive ) {
			$value = strtolower( $value );
		}
		if ( in_array( $value, $this->choices, false ) ) {
			return StatusValue::newGood( $value );
		} else {
			return StatusValue::newFatal(
				$this->errorMessage,
				wfEscapeWikiText( $value ),
				$ctx->contentLanguage->commaList( array_map( 'wfEscapeWikiText', $this->choices ) ),
				new MessageValue( $this->getTranslationKey() ),
			);
		}
	}

}
