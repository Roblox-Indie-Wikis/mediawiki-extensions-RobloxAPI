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

namespace MediaWiki\Extension\RobloxAPI\data\source;

use MediaWiki\Extension\RobloxAPI\data\cache\DataSourceCache;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;

/**
 * A data source represents an endpoint of the roblox api.
 */
abstract class DataSource {

	/**
	 * @var string The ID of this data source.
	 */
	public string $id;
	/**
	 * @var DataSourceCache The cache of this data source.
	 */
	protected DataSourceCache $cache;

	public function __construct( string $id, DataSourceCache $cache ) {
		$this->id = $id;
		$this->cache = $cache;
	}

	public function setCacheExpiry( int $seconds ): void {
		$this->cache->setExpiry( $seconds );
	}

	/**
	 * Fetches data
	 * @param mixed ...$args
	 * @return mixed
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	abstract public function fetch( ...$args );

}
