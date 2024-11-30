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
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;

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
	/**
	 * @var int The number of expected arguments.
	 */
	protected int $expectedArgs;

	/**
	 * Constructs a new data source.
	 * @param string $id The ID of this data source.
	 * @param DataSourceCache $cache The cache of this data source.
	 * @param int $expectedArgs The number of expected arguments.
	 */
	public function __construct(
		string $id, DataSourceCache $cache, int $expectedArgs
	) {
		$this->id = $id;
		$this->cache = $cache;
		$this->expectedArgs = $expectedArgs;
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
	public function fetch( ...$args ) {
		// assure that we have the correct number of arguments
		RobloxAPIUtil::safeDestructure( $args, $this->expectedArgs );
		// validate the ids
		RobloxAPIUtil::assertValidIds( ...$args );

		$endpoint = $this->getEndpoint( $args );
		$data = $this->cache->fetchJson( $endpoint );

		$processedData = $this->processData( $data, $args );

		if ( !$processedData ) {
			throw new RobloxAPIException( 'robloxapi-error-invalid-data' );
		}

		return $processedData;
	}

	/**
	 * Returns the endpoint of this data source for the given arguments.
	 * @param mixed $args The arguments to use.
	 * @return string The endpoint of this data source.
	 */
	abstract public function getEndpoint( $args ): string;

	/**
	 * Processes the data before returning it.
	 * @param mixed $data The data to process.
	 * @param mixed $args The arguments used to fetch the data.
	 * @return mixed The processed data.
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	public function processData( $data, $args ) {
		return $data;
	}

}
