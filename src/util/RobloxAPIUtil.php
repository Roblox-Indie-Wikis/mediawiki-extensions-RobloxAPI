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

namespace MediaWiki\Extension\RobloxAPI\util;

use MediaWiki\Config\Config;

/**
 * Provides utilities for working with the Roblox API.
 */
class RobloxAPIUtil {

	/**
	 * Checks whether a numeric ID is valid.
	 * @param string|null $string
	 * @return bool
	 */
	public static function isValidId( ?string $string ): bool {
		if ( $string === null ) {
			// TODO handle this somewhere else
			return false;
		}

		return preg_match( '/^\d{1,16}$/', $string );
	}

	/**
	 * Checks whether multiple numeric IDs are valid.
	 * @param array $strings
	 * @return bool
	 */
	public static function areValidIds( array $strings ): bool {
		foreach ( $strings as $string ) {
			if ( !self::isValidId( $string ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param mixed ...$strings
	 * @return void
	 * @throws RobloxAPIException if any of the IDs are invalid
	 */
	public static function assertValidIds( ...$strings ): void {
		// required because RobloxAPIException uses wfEscapeWikiText
		// TODO find a better way to handle this
		global $wgEnableMagicLinks;
		$wgEnableMagicLinks = [];

		foreach ( $strings as $string ) {
			if ( !self::isValidId( $string ) ) {
				throw new RobloxAPIException( 'robloxapi-error-invalid-id', $string );
			}
		}
	}

	/**
	 * Validates the number of args and returns them so they can be destructured safely
	 * @param array $args An array of args
	 * @param int $amount The amount of args expected
	 * @throws RobloxAPIException if the args are invalid
	 */
	public static function safeDestructure( array $args, int $amount ): array {
		if ( count( $args ) !== $amount ) {
			throw new RobloxAPIException( 'robloxapi-error-invalid-args-count' );
		}

		return $args;
	}

	/**
	 * Asserts that the given args are allowed
	 * @param Config $config The config object
	 * @param array $expectedArgs The expected arg types
	 * @param array $args The actual args
	 * @throws RobloxAPIException if the args are invalid
	 */
	public static function assertArgsAllowed(
		Config $config, array $expectedArgs, array $args
	) {
		foreach ( $args as $index => $arg ) {
			$expectedType = $expectedArgs[$index];
			$configKey = "RobloxAPIAllowed{$expectedType}s";
			$allowedValues = $config->get( $configKey );
			if ( empty( $allowedValues ) ) {
				// all values are allowed
				continue;
			}
			if ( !in_array( $arg, $allowedValues ) ) {
				throw new RobloxAPIException( 'robloxapi-error-arg-not-allowed', $arg, $expectedType );
			}
		}
	}

}
