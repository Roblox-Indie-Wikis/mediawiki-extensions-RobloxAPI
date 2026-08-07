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
 * Represents an argument type.
 * @template T
 */
interface IArgument {

	/**
	 * @param ArgumentParserContext $ctx The context for argument parsing.
	 * @param string $value The value to validate.
	 * @return StatusValue<T> The status of the validation: A good status with the argument value if valid,
	 * else an error status.
	 */
	public function validate( ArgumentParserContext $ctx, string $value ): StatusValue;

	/**
	 * @return string The key for the argument type.
	 */
	public function getKey(): string;

	/**
	 * @return string The translation key for the argument type.
	 */
	public function getTranslationKey(): string;

}
