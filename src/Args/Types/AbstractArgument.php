<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args\Types;

use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils;
use StatusValue;
use Wikimedia\Message\MessageValue;

/**
 * Abstract base class for arguments.
 * @template T
 * @implements IArgument<T>
 */
abstract class AbstractArgument implements IArgument {

	/**
	 * @param string $key The key for this argument type.
	 * @suppress PhanGenericConstructorTypes No idea why, but it causes errors otherwise.
	 */
	protected function __construct(
		private readonly string $key,
	) {
	}

	/** @inheritDoc */
	public function getTranslationKey(): string {
		return "robloxapi-arg-type-{$this->getKey()}";
	}

	public function getKey(): string {
		return $this->key;
	}

	protected function invalidValue(
		string $value = '',
		string $errorMessage = 'robloxapi-error-invalid-generic-argument',
	): StatusValue {
		return StatusValue::newFatal(
			$errorMessage,
			RobloxAPIUtils::transformValueForError( $value ),
			new MessageValue( $this->getTranslationKey() )
		);
	}

}
