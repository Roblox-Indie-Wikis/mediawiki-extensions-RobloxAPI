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

use BagOStuff;
use MediaWiki\MediaWikiServices;

/**
 * A simple cache that expires after a set amount of seconds.
 */
class SimpleExpiringCache extends DataSourceCache {
	private BagOStuff $cache;

	public function __construct() {
		$this->cache = MediaWikiServices::getInstance()->getLocalServerObjectCache();
	}

	/**
	 * @inheritDoc
	 */
	public function getResultForEndpoint( string $endpoint, array $args ) {
		$value = $this->cache->get( $this->getCacheKey( $endpoint, $args ) );
		if ( $value === false ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function registerCacheEntry( string $endpoint, $value, array $args ): void {
		$this->cache->set( $this->getCacheKey( $endpoint, $args ), $value, $this->expiry );
	}

	/**
	 * Generates a cache key for the given endpoint and arguments.
	 * @param string $endpoint
	 * @param array $args
	 * @return string
	 */
	protected function getCacheKey( string $endpoint, array $args ): string {
		return md5( json_encode( $args ) ) . '__roblox__' . $endpoint;
	}

}
