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
use MediaWiki\Extension\RobloxAPI\data\source\implementation\AssetThumbnailDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\AssetThumbnailUrlDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\GameDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\GameIconDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\GameIconUrlDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\GroupMembersDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\GroupRankDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\PlaceActivePlayersDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\PlaceVisitsDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\UserAvatarThumbnailDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\UserAvatarThumbnailUrlDataSource;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\UserIdDataSource;
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
		$this->registerDataSource( new UserAvatarThumbnailDataSource( $config ) );
		$this->registerDataSource( new AssetThumbnailDataSource( $config ) );
		$this->registerDataSource( new GameIconDataSource( $config ) );

		$this->registerDataSource( new SimpleFetcherDataSource( 'groupRoles', $config,
			( new ArgumentSpecification( [ 'UserID' ] ) )->withJsonArgs(), static function ( $args ) {
				return "https://groups.roblox.com/v1/users/$args[0]/groups/roles";
			}, static function ( $data ) {
				return $data->data;
			}, true ) );
		$this->registerDataSource( new SimpleFetcherDataSource( 'groupData', $config,
			( new ArgumentSpecification( [ 'GroupID' ] ) )->withJsonArgs(), static function ( $args ) {
				return "https://groups.roblox.com/v1/groups/$args[0]";
			}, null, true ) );
		$this->registerDataSource( new SimpleFetcherDataSource( 'groupRolesList', $config,
			( new ArgumentSpecification( [ 'GroupID' ] ) )->withJsonArgs(), static function ( $args ) {
				return "https://groups.roblox.com/v1/groups/$args[0]/roles";
			}, null, false ) );
		$this->registerDataSource( new SimpleFetcherDataSource( 'badgeInfo', $config,
			( new ArgumentSpecification( [ 'BadgeID' ] ) )->withJsonArgs(), static function ( $args ) {
				return "https://badges.roblox.com/v1/badges/$args[0]";
			}, null, true ) );
		$this->registerDataSource( new SimpleFetcherDataSource( 'userInfo', $config,
			( new ArgumentSpecification( [ 'UserID' ] ) )->withJsonArgs(), static function ( $args ) {
				return "https://users.roblox.com/v1/users/$args[0]";
			}, null, true ) );
		$this->registerDataSource( new SimpleFetcherDataSource( 'assetDetails', $config, ( new ArgumentSpecification( [
			'AssetID',
		] ) )->withJsonArgs(), static function ( $args ) {
			return "https://economy.roblox.com/v2/assets/$args[0]/details";
		}, null, true ) );

		// dependent data sources will throw an exception if the required data source is not enabled
		$this->tryRegisterDataSource( function () {
			return new GroupRankDataSource( $this );
		} );
		$this->tryRegisterDataSource( function () {
			return new PlaceActivePlayersDataSource( $this );
		} );
		$this->tryRegisterDataSource( function () {
			return new PlaceVisitsDataSource( $this );
		} );
		$this->tryRegisterDataSource( function () {
			return new GroupMembersDataSource( $this );
		} );
		$this->tryRegisterDataSource( function () {
			return new UserAvatarThumbnailUrlDataSource( $this );
		} );
		$this->tryRegisterDataSource( function () {
			return new AssetThumbnailUrlDataSource( $this );
		} );
		$this->tryRegisterDataSource( function () {
			return new GameIconUrlDataSource( $this );
		} );
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
		$id = $dataSource->getId();
		if ( $this->isEnabled( $id ) ) {
			$this->dataSources[$id] = $dataSource;
			if ( $dataSource instanceof FetcherDataSource ) {
				$dataSource->setCacheExpiry( $this->getCachingExpiry( $id ) );
			}
		}
	}

	/**
	 * Tries to register a data source, but ignores any exceptions.
	 * @param callable(): IDataSource $dataSourceFactory
	 * @return void
	 */
	public function tryRegisterDataSource( callable $dataSourceFactory ): void {
		try {
			$dataSource = $dataSourceFactory();
			$this->registerDataSource( $dataSource );
		} catch ( RobloxAPIException $e ) {
			wfDebugLog( 'RobloxAPI', "Failed to register data source: {$e->getMessage()}" );
		}
	}

	/**
	 * Gets a data source by its ID.
	 * @param string $id
	 * @param bool $ignoreCase
	 * @return IDataSource|null
	 */
	public function getDataSource( string $id, bool $ignoreCase = false ): ?IDataSource {
		if ( array_key_exists( $id, $this->dataSources ) ) {
			return $this->dataSources[$id];
		}

		if ( $ignoreCase ) {
			foreach ( $this->dataSources as $dataSource ) {
				/* @var IDataSource $dataSource */
				if ( strcasecmp( $dataSource->getId(), $id ) === 0 ) {
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
			if ( !$dataSource->shouldRegisterLegacyParserFunction() ) {
				continue;
			}

			$id = "roblox_" . ucfirst( $dataSource->getId() );
			$function = $this->createParserFunction( $dataSource );
			$functions[$id] = $function;
		}

		return $functions;
	}

	/**
	 * Creates a parser function for the given data source.
	 * @param IDataSource $dataSource
	 * @return RobloxApiParserFunction
	 */
	private function createParserFunction( IDataSource $dataSource ): RobloxApiParserFunction {
		return new DataSourceParserFunction( $this, $dataSource );
	}

}
