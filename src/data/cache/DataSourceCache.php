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

use FormatJson;

/**
 * Defines a caching strategy for a data source.
 */
abstract class DataSourceCache {

	/**
	 * Tries to search for a value in the cache.
	 * @param string $endpoint
	 * @return mixed|null
	 */
	abstract protected function getResultForEndpoint( string $endpoint );

	/**
	 * Saves an entry to the cache.
	 * @param string $endpoint
	 * @param mixed $value
	 * @return void
	 */
	abstract protected function registerCacheEntry( string $endpoint, $value ): void;

	/**
	 * Fetches a JSON value from the given endpoint.
	 * @param string $endpoint
	 * @return false|mixed|string
	 */
	public function fetchJson( string $endpoint ) {
		$cached_result = $this->getResultForEndpoint( $endpoint );

		if ( $cached_result !== null ) {
			return $cached_result;
		}

		$json = file_get_contents( $endpoint );

		if ( $json === false ) {
			// TODO
			return FormatJson::encode( [
				'error' => 'request_failed',
			] );
		}

		$data = FormatJson::decode( $json );
		$this->registerCacheEntry( $endpoint, $data );

		return $data;
	}

}
