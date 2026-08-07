<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args\Types;

/**
 * Represents an argument that is a Roblox ID.
 */
class IdArgument extends RegexArgument {

	/** @inheritDoc */
	public function __construct( string $key ) {
		parent::__construct(
			$key,
			/** @lang RegExp */ '/^\d{1,16}$/',
		);
	}

	public static function asset(): self {
		return new self( 'asset-id' );
	}

	public static function badge(): self {
		return new self( 'badge-id' );
	}

	public static function group(): self {
		return new self( 'group-id' );
	}

	public static function place(): self {
		return new self( 'place-id' );
	}

	public static function role(): self {
		return new self( 'role-id' );
	}

	public static function universe(): self {
		return new self( 'universe-id' );
	}

	public static function user(): self {
		return new self( 'user-id' );
	}

}
