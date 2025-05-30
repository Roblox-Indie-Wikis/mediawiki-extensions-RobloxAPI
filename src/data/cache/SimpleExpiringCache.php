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

use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;
use MediaWiki\MediaWikiServices;
use WANObjectCache;

/**
 * A simple cache that expires after a set amount of seconds.
 */
class SimpleExpiringCache extends DataSourceCache {
	/** ToDo: replace this alias with the actual class once support for 1.42 is dropped */
	private WANObjectCache $cache;

	public function __construct() {
		$this->cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
	}

	/**
	 * @inheritDoc
	 */
	public function getResultForEndpoint( string $endpoint, array $args, array $optionalArgs ): mixed {
		$value = $this->cache->get( $this->getCacheKey( $endpoint, $args, $optionalArgs ) );
		if ( $value === false ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function registerCacheEntry( string $endpoint, $value, array $args, array $optionalArgs ): void {
		$this->cache->set( $this->getCacheKey( $endpoint, $args, $optionalArgs ), $value, $this->expiry );
	}

	/**
	 * Generates a cache key for the given endpoint and arguments.
	 * @param string $endpoint
	 * @param array $args
	 * @param array $optionalArgs
	 * @return string
	 */
	protected function getCacheKey( string $endpoint, array $args, array $optionalArgs ): string {
		$cacheAffectingOptionalArgs = RobloxAPIUtil::getCacheAffectingArgs( $optionalArgs );

		$argsJson = json_encode( $args );
		$optionalArgsJson = json_encode( $cacheAffectingOptionalArgs );

		// ToDo consider using cache->makeKey() here

		return '__roblox__' . $endpoint . '__' . md5( $argsJson ) . '__' . md5( $optionalArgsJson );
	}

}
