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

/**
 * A simple cache that expires after a set amount of seconds.
 */
class SimpleExpiringCache extends DataSourceCache {
	// TODO DOESNT WORK
	private int $expireAfterSeconds;
	private array $cache;

	public function __construct( int $expireAfterSeconds ) {
		$this->expireAfterSeconds = $expireAfterSeconds;
		$this->cache = [];
	}

	/**
	 * @inheritDoc
	 */
	protected function getResultForEndpoint( string $endpoint ) {
		$cachedValue = $this->cache[$endpoint] ?? null;

		if ( $cachedValue === null ) {
			return null;
		}

		$expiry = $cachedValue['expiry'];

		if ( $expiry < time() ) {
			// remove value from cache since it's expired
			$this->cache[$endpoint] = null;

			return null;
		}

		return $cachedValue['value'];
	}

	/**
	 * @inheritDoc
	 */
	protected function registerCacheEntry( string $endpoint, $value ): void {
		$this->cache[$endpoint] = [
			'value' => $value,
			'expiry' => time() + $this->expireAfterSeconds,
		];
	}
}
