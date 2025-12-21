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
