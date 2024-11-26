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
	protected function getResultForEndpoint( string $endpoint ) {
		$value = $this->cache->get( $endpoint );
		if ( $value === false ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	protected function registerCacheEntry( string $endpoint, $value ): void {
		$this->cache->set( $endpoint, $value, $this->expiry );
	}
}
