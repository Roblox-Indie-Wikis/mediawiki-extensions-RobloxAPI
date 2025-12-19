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

/**
 * Represents an argument that is a Roblox ID.
 */
class IdArgument extends RegexArgument {

	/** @inheritDoc */
	public function __construct( string $translationKey, string $errorMessage = 'robloxapi-error-invalid-id' ) {
		parent::__construct(
			$translationKey,
			/** @lang RegExp */ '/^\d{1,16}$/',
			$errorMessage
		);
	}

	public static function asset(): self {
		return new self( 'robloxapi-arg-type-asset-id' );
	}

	public static function badge(): self {
		return new self( 'robloxapi-arg-type-badge-id' );
	}

	public static function group(): self {
		return new self( 'robloxapi-arg-type-group-id' );
	}

	public static function place(): self {
		return new self( 'robloxapi-arg-type-place-id' );
	}

	public static function role(): self {
		return new self( 'robloxapi-arg-type-role-id' );
	}

	public static function universe(): self {
		return new self( 'robloxapi-arg-type-universe-id' );
	}

	public static function user(): self {
		return new self( 'robloxapi-arg-type-user-id' );
	}

}
