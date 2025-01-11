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

use MediaWiki\Config\Config;
use MediaWiki\Extension\RobloxAPI\data\args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\parserFunction\DataSourceParserFunction;
use MediaWiki\Extension\RobloxAPI\parserFunction\RobloxApiParserFunction;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;

/**
 * Handles the registration of data sources and stores them.
 */
class DataSourceProvider {

	public Config $config;
	/**
	 * @var array The currently enabled data sources.
	 */
	public array $dataSources = [];
	/**
	 * @var array|int the amount of time for each data source after which the cache
	 * expires
	 */
	public array $cachingExpiries;

	public function __construct( Config $config ) {
		$this->config = $config;

		$this->cachingExpiries = $this->config->get( 'RobloxAPICachingExpiries' );

		$this->registerDataSource( new GameDataSource( $config ) );
		$this->registerDataSource( new UserIdDataSource( $config ) );

		$this->registerDataSource( new SimpleFetcherDataSource( 'groupRoles', $config,
			new ArgumentSpecification( [ 'UserID' ] ), static function ( $args ) {
				return "https://groups.roblox.com/v1/users/$args[0]/groups/roles";
			}, static function ( $data ) {
				return $data->data;
			}, true ) );
		$this->registerDataSource( new SimpleFetcherDataSource( 'groupData', $config,
			new ArgumentSpecification( [ 'GroupID' ] ), static function ( $args ) {
				return "https://groups.roblox.com/v1/groups/$args[0]";
			}, null, true ) );
		$this->registerDataSource( new SimpleFetcherDataSource( 'userAvatarThumbnail', $config,
			new ArgumentSpecification( [ 'UserID', 'ThumbnailSize' ] ), static function ( $args ) {
				// TODO allow configuring more options
				return "https://thumbnails.roblox.com/v1/users/avatar?userIds={$args[0]}&size={$args[1]}&format=Png" .
					"&isCircular=false";
			}, static function ( $data ) {
				return $data->data;
			}, true ) );
		$this->registerDataSource( new SimpleFetcherDataSource( 'badgeInfo', $config,
			new ArgumentSpecification( [ 'BadgeID' ] ), static function ( $args ) {
				return "https://badges.roblox.com/v1/badges/$args[0]";
			}, null, true ) );
		$this->registerDataSource( new SimpleFetcherDataSource( 'userInfo', $config,
			new ArgumentSpecification( [ 'UserID' ] ), static function ( $args ) {
				return "https://users.roblox.com/v1/users/$args[0]";
			}, null, true ) );
		$this->registerDataSource( new SimpleFetcherDataSource( 'assetDetails', $config, new ArgumentSpecification( [
			'AssetID',
		] ), static function ( $args ) {
			return "https://economy.roblox.com/v2/assets/$args[0]/details";
		}, null, true ) );
	}

	/**
	 * Checks the config on whether a data source is enabled.
	 * @param string $id
	 * @return bool
	 */
	protected function isEnabled( string $id ): bool {
		$enabledDataSources = $this->config->get( 'RobloxAPIEnabledDatasources' );

		return in_array( $id, $enabledDataSources );
	}

	/**
	 * Gets the caching expiry for a data source.
	 * If a specific value is not set, the default value (key '*') is used.
	 * @param string $id
	 * @return int The caching expiry in seconds.
	 */
	protected function getCachingExpiry( string $id ): int {
		if ( !isset( $this->cachingExpiries[$id] ) ) {
			return $this->cachingExpiries['*'];
		}

		return $this->cachingExpiries[$id];
	}

	/**
	 * Registers a data source if it is enabled.
	 * @param IDataSource $dataSource
	 * @return void
	 */
	public function registerDataSource( IDataSource $dataSource ): void {
		$id = $dataSource->id;
		if ( $this->isEnabled( $id ) ) {
			$this->dataSources[$dataSource->id] = $dataSource;
			$dataSource->setCacheExpiry( $this->getCachingExpiry( $dataSource->id ) );
		}
	}

	/**
	 * Gets a data source by its ID.
	 * @param string $id
	 * @return IDataSource|null
	 */
	public function getDataSource( string $id, bool $ignoreCase = false ): ?IDataSource {
		if ( array_key_exists( $id, $this->dataSources ) ) {
			return $this->dataSources[$id];
		}

		if ( $ignoreCase ) {
			foreach ( $this->dataSources as $dataSource ) {
				if ( strcasecmp( $dataSource->id, $id ) === 0 ) {
					return $dataSource;
				}
			}
		}

		return null;
	}

	/**
	 * @param string $id
	 * @return IDataSource
	 * @throws RobloxAPIException
	 */
	public function getDataSourceOrThrow( string $id ): IDataSource {
		$source = $this->getDataSource( $id );

		if ( !$source ) {
			throw new RobloxAPIException( 'robloxapi-error-datasource-not-found', $id );
		}

		return $source;
	}

	/**
	 * Creates parser functions for all enabled data sources.
	 * @return RobloxApiParserFunction[]
	 */
	public function createLegacyParserFunctions(): array {
		$functions = [];

		/** @var IDataSource $dataSource */
		foreach ( $this->dataSources as $dataSource ) {
			// register parser function only if needed for legacy reasons
			if ( !$dataSource->shouldRegisterLegacyParserFunction() || !( $dataSource instanceof FetcherDataSource ) ) {
				continue;
			}

			$id = "roblox_" . ucfirst( $dataSource->id );
			$function = $this->createParserFunction( $dataSource );
			$functions[$id] = $function;
		}

		return $functions;
	}

	/**
	 * Creates a parser function for the given data source.
	 * @param FetcherDataSource $dataSource
	 * @return RobloxApiParserFunction
	 */
	private function createParserFunction( FetcherDataSource $dataSource ): RobloxApiParserFunction {
		return new DataSourceParserFunction( $this, $dataSource );
	}

}
