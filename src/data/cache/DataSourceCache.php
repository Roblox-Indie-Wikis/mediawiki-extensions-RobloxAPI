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

namespace MediaWiki\Extension\RobloxAPI\data\cache;

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtil;
use Wikimedia\ObjectCache\WANObjectCache;

/**
 * Caches data returned by the Roblox API.
 */
class DataSourceCache {

	public const CONSTRUCTOR_OPTIONS = [
		RobloxAPIConstants::ConfDisableCache,
	];

	/** @var bool Whether the cache is disabled */
	private bool $disabled;

	public function __construct(
		ServiceOptions $options,
		private readonly WANObjectCache $cache
	) {
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$this->disabled = $options->get( RobloxAPIConstants::ConfDisableCache );
	}

	/**
	 * Tries to search for a value in the cache.
	 * @param string $endpoint
	 * @param string[] $args
	 * @param array<string, string> $optionalArgs
	 */
	public function getResultForEndpoint( string $endpoint, array $args, array $optionalArgs ): mixed {
		if ( $this->disabled ) {
			return null;
		}

		$value = $this->cache->get( $this->getCacheKey( $endpoint, $args, $optionalArgs ) );
		if ( $value === false ) {
			return null;
		}

		return $value;
	}

	/**
	 * Saves an entry to the cache.
	 * @param string $endpoint
	 * @param mixed $value
	 * @param string[] $args
	 * @param array<string, string> $optionalArgs
	 * @param int $expiry The expiry in seconds
	 */
	public function registerCacheEntry(
		string $endpoint,
		mixed $value,
		array $args,
		array $optionalArgs,
		int $expiry
	): void {
		if ( $this->disabled ) {
			return;
		}

		$this->cache->set( $this->getCacheKey( $endpoint, $args, $optionalArgs ), $value, $expiry );
	}

	/**
	 * Generates a cache key for the given endpoint and arguments.
	 * @param string $endpoint
	 * @param string[] $args
	 * @param array<string, string> $optionalArgs
	 */
	protected function getCacheKey( string $endpoint, array $args, array $optionalArgs ): string {
		$cacheAffectingOptionalArgs = RobloxAPIUtil::getCacheAffectingArgs( $optionalArgs );

		$argsJson = json_encode( $args );
		$optionalArgsJson = json_encode( $cacheAffectingOptionalArgs );

		// ToDo consider using cache->makeKey() here

		return '__roblox__' . $endpoint . '__' . md5( $argsJson ) . '__' . md5( $optionalArgsJson );
	}

}
