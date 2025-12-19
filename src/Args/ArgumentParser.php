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

namespace MediaWiki\Extension\RobloxAPI\Args;

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWiki\Language\Language;
use StatusValue;
use Wikimedia\Message\MessageValue;

class ArgumentParser {

	public const CONSTRUCTOR_OPTIONS = [
		RobloxAPIConstants::ConfAllowedArguments,
	];

	public function __construct(
		private readonly ServiceOptions $options,
		private readonly Language $contentLanguage,
	) {
		$this->options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
	}

	/**
	 * @param ArgumentSpecification $specification The argument specification to use.
	 * @param string[] $args The raw argument strings.
	 * @return StatusValue<ArgumentParserResult> The parsed arguments or an error status.
	 */
	public function parse(
		ArgumentSpecification $specification,
		array $args,
	): StatusValue {
		$ctx = $this->newContext();

		$status = $this->extractRequiredArgs( $ctx, $specification, $args );
		if ( !$status->isGood() ) {
			return $status;
		}
		$requiredArgs = $status->value;

		$status = $this->extractOptionalArgs( $ctx, $specification, $args );
		if ( !$status->isGood() ) {
			return $status;
		}
		$optionalArgs = $status->value;

		return StatusValue::newGood( new ArgumentParserResult(
			$requiredArgs,
			$optionalArgs
		) );
	}

	/**
	 * @return StatusValue<string[]> The extracted required arguments or an error status.
	 */
	private function extractRequiredArgs(
		ArgumentParserContext $ctx,
		ArgumentSpecification $specification,
		array &$args,
	): StatusValue {
		$result = [];

		foreach ( $specification->requiredArgs as $type ) {
			if ( count( $args ) === 0 ) {
				return StatusValue::newFatal(
					'robloxapi-error-missing-argument',
					MessageValue::new( $type->getTranslationKey() )
				);
			}

			$value = array_shift( $args );
			$status = $type->validate( $ctx, $value );
			// TODO implement ConfAllowedArguments! or deprecate/remove in 2.0.0?
			if ( !$status->isGood() ) {
				return $status;
			}

			$result[] = $status->value;
		}

		return StatusValue::newGood( $result );
	}

	/**
	 * @return StatusValue<array<string, string>> The extracted optional arguments or an error status.
	 */
	private function extractOptionalArgs(
		ArgumentParserContext $ctx,
		ArgumentSpecification $specification,
		array &$args,
	): StatusValue {
		$result = [];
		$first = true;

		foreach ( $args as $value ) {
			$parts = explode( '=', $value, 2 );

			if ( count( $parts ) === 1 ) {
				if ( $first ) {
					return StatusValue::newFatal( 'robloxapi-error-too-many-required-args', $parts[0] );
				}
				return StatusValue::newFatal( 'robloxapi-error-missing-optional-argument-value', $parts[0] );
			}

			$key = strtolower( $parts[0] );
			$value = $parts[1];

			if ( !array_key_exists( $key, $specification->optionalArgs ) ) {
				return StatusValue::newFatal( 'robloxapi-error-unknown-optional-argument', $key );
			}

			$type = $specification->optionalArgs[$key];
			$status = $type->validate( $ctx, $value );
			// TODO implement ConfAllowedArguments! or deprecate/remove in 2.0.0?
			if ( !$status->isGood() ) {
				return $status;
			}

			$result[$key] = $status->value;
			$first = false;
		}

		return StatusValue::newGood( $result );
	}

	/**
	 * @return ArgumentParserContext A new argument parser context.
	 */
	private function newContext(): ArgumentParserContext {
		return new ArgumentParserContext(
			$this->contentLanguage,
		);
	}

}
