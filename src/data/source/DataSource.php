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

use FormatJson;
use MediaWiki\Config\Config;
use MediaWiki\Extension\RobloxAPI\data\cache\DataSourceCache;
use MediaWiki\Extension\RobloxAPI\data\cache\EmptyCache;
use MediaWiki\Extension\RobloxAPI\data\cache\SimpleExpiringCache;
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
	 * @var Config The extension configuration.
	 */
	protected Config $config;
	/**
	 * @var array|string The expected argument types.
	 */
	protected array $expectedArgs;

	/**
	 * Constructs a new data source.
	 * @param string $id The ID of this data source.
	 * @param DataSourceCache $cache The cache of this data source.
	 * @param Config $config The extension configuration.
	 * @param array $expectedArgs The expected argument types.
	 */
	public function __construct(
		string $id, DataSourceCache $cache, Config $config, array $expectedArgs
	) {
		$this->id = $id;
		$this->cache = $cache;
		$this->config = $config;
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
		RobloxAPIUtil::safeDestructure( $args, count( $this->expectedArgs ) );
		// validate the args
		RobloxAPIUtil::assertValidArgs( $this->expectedArgs, $args );
		RobloxAPIUtil::assertArgsAllowed( $this->config, $this->expectedArgs, $args );

		$endpoint = $this->getEndpoint( $args );
		$data = $this->getDataFromEndpoint( $endpoint );

		$processedData = $this->processData( $data, $args );

		if ( !$processedData ) {
			throw new RobloxAPIException( 'robloxapi-error-invalid-data' );
		}

		return $processedData;
	}

	/**
	 * Fetches data from the given endpoint.
	 * @param string $endpoint The endpoint to fetch data from.
	 * @return mixed The fetched data.
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	public function getDataFromEndpoint( string $endpoint ) {
		$cached_result = $this->cache->getResultForEndpoint( $endpoint );

		if ( $cached_result !== null ) {
			return $cached_result;
		}

		$json = file_get_contents( $endpoint );

		if ( $json === false ) {
			// TODO try to fetch from cache
			throw new RobloxAPIException( 'robloxapi-error-request-failed' );
		}

		$data = FormatJson::decode( $json );

		if ( $data === null ) {
			throw new RobloxAPIException( 'robloxapi-error-decode-failure' );
		}

		$this->cache->registerCacheEntry( $endpoint, $data );

		return $data;
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

	protected static function createSimpleCache(): DataSourceCache {
		if ( defined( 'MW_PHPUNIT_TEST' ) ) {
			// we're in a unit test environment, don't create cache
			return new EmptyCache();
		}

		return new SimpleExpiringCache();
	}

}
