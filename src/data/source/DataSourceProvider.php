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

use Closure;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\data\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\data\Fetcher\RobloxAPIFetcher;
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
use MediaWiki\Extension\RobloxAPI\data\source\implementation\UserPlaceVisitsDataSource;
use MediaWiki\Extension\RobloxAPI\ParserFunction\DataSourceParserFunction;
use MediaWiki\Extension\RobloxAPI\ParserFunction\RobloxApiParserFunction;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIException;

/**
 * Handles the registration of data sources and stores them.
 */
class DataSourceProvider {

	public const CONSTRUCTOR_OPTIONS = [
		RobloxAPIConstants::ConfAllowedArguments,
		RobloxAPIConstants::ConfEnabledDataSources,
	];

	/**
	 * @var array<string, IDataSource> The currently enabled data sources.
	 */
	public array $dataSources = [];

	/** @noinspection PhpUnusedParameterInspection */
	public function __construct( public ServiceOptions $options, private readonly RobloxAPIFetcher $fetcher ) {
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );

		$this->registerDataSource( new GameDataSource( $fetcher ) );
		$this->registerDataSource( new UserIdDataSource( $fetcher ) );
		$this->registerDataSource( new UserAvatarThumbnailDataSource( $fetcher ) );
		$this->registerDataSource( new AssetThumbnailDataSource( $fetcher ) );
		$this->registerDataSource( new GameIconDataSource( $fetcher ) );

		$this->registerSimpleFetcherDataSource(
			'groupRoles',
			new ArgumentSpecification( [ 'UserID' ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://groups.roblox.com/v1/users/$args[0]/groups/roles";
			}, static function ( mixed $data, array $requiredArgs, array $optionalArgs ): mixed {
				return $data->data;
			},
			true
		);
		$this->registerSimpleFetcherDataSource(
			'groupData',
			new ArgumentSpecification( [ 'GroupID' ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://groups.roblox.com/v1/groups/$args[0]";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'groupRolesList',
			new ArgumentSpecification( [ 'GroupID' ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://groups.roblox.com/v1/groups/$args[0]/roles";
			}
		);
		$this->registerSimpleFetcherDataSource(
			'badgeInfo',
			new ArgumentSpecification( [ 'BadgeID' ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://badges.roblox.com/v1/badges/$args[0]";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'userInfo',
			new ArgumentSpecification( [ 'UserID' ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://users.roblox.com/v1/users/$args[0]";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'assetDetails',
			new ArgumentSpecification( [ 'AssetID' ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://economy.roblox.com/v2/assets/$args[0]/details";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'gameNameDescription',
			new ArgumentSpecification( [ 'UniverseID' ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://gameinternationalization.roblox.com/v1/name-description/games/$args[0]";
			}
		);
		$this->registerSimpleFetcherDataSource(
			'universeInfo',
			new ArgumentSpecification( [ 'UniverseID' ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://develop.roblox.com/v1/universes/$args[0]";
			}
		);
		$this->registerSimpleFetcherDataSource(
			'userGames',
			new ArgumentSpecification(
				[ 'UserID' ],
				[ 'limit' => 'UserGamesLimit', 'sort_order' => 'SortOrder' ],
				true
			),
			static function ( array $args, array $optionalArgs ): string {
				$limit = $optionalArgs['limit'] ?? 50;
				$sortOrder = $optionalArgs['sort_order'] ?? 'Asc';

				return "https://games.roblox.com/v2/users/$args[0]/games?limit=$limit&sortOrder=$sortOrder";
			},
			static function ( mixed $data, array $requiredArgs, array $optionalArgs ): mixed {
				return $data->data;
			}
		);
		$this->registerSimpleFetcherDataSource(
			'gameEvents',
			new ArgumentSpecification(
				[ 'UniverseID' ],
				[],
				true
			),
			static function ( array $args, array $optionalArgs ): string {
				return "https://apis.roblox.com/virtual-events/v1/universes/$args[0]/virtual-events";
			},
			static function ( mixed $data, array $requiredArgs, array $optionalArgs ): mixed {
				return $data->data;
			}
		);

		// dependent data sources will throw an exception if the required data source is not enabled
		$this->tryRegisterDataSources(
			GroupRankDataSource::class,
			PlaceActivePlayersDataSource::class,
			PlaceVisitsDataSource::class,
			GroupMembersDataSource::class,
			UserAvatarThumbnailUrlDataSource::class,
			AssetThumbnailUrlDataSource::class,
			GameIconUrlDataSource::class,
			UserPlaceVisitsDataSource::class,
		);
	}

	/**
	 * Checks the config on whether a data source is enabled.
	 */
	protected function isEnabled( string $id ): bool {
		$enabledDataSources = $this->options->get( RobloxAPIConstants::ConfEnabledDataSources );

		return in_array( $id, $enabledDataSources, true );
	}

	/**
	 * Registers a data source if it is enabled.
	 */
	public function registerDataSource( IDataSource $dataSource ): void {
		$id = $dataSource->getId();
		if ( $this->isEnabled( $id ) ) {
			$this->dataSources[$id] = $dataSource;
		}
	}

	/**
	 * Registers a new simple fetcher data source if it's enabled.
	 * @see SimpleFetcherDataSource::__construct
	 */
	public function registerSimpleFetcherDataSource(
		string $id,
		ArgumentSpecification $argumentSpecification,
		Closure $createEndpoint,
		?Closure $processData = null,
		bool $registerParserFunction = false
	): void {
		$this->registerDataSource( new SimpleFetcherDataSource(
			$id,
			$this->fetcher,
			$argumentSpecification,
			$createEndpoint,
			$processData,
			$registerParserFunction
		) );
	}

	/**
	 * Tries to register a data source, but ignores any exceptions.
	 * @param class-string $className
	 */
	public function tryRegisterDataSource( string $className ): void {
		try {
			$dataSource = new $className( $this );
			$this->registerDataSource( $dataSource );
		} catch ( RobloxAPIException $e ) {
			wfDebugLog( 'RobloxAPI', "Failed to register data source: {$e->getMessage()}" );
		}
	}

	/**
	 * Tries to register multiple data sources, but ignores any exceptions.
	 * @param class-string ...$classNames
	 */
	public function tryRegisterDataSources( string ...$classNames ): void {
		foreach ( $classNames as $className ) {
			$this->tryRegisterDataSource( $className );
		}
	}

	/**
	 * Gets a data source by its ID.
	 */
	public function getDataSource( string $id, bool $ignoreCase = false ): ?IDataSource {
		if ( array_key_exists( $id, $this->dataSources ) ) {
			return $this->dataSources[$id];
		}

		if ( $ignoreCase ) {
			foreach ( $this->dataSources as $dataSource ) {
				if ( strcasecmp( $dataSource->getId(), $id ) === 0 ) {
					return $dataSource;
				}
			}
		}

		return null;
	}

	/**
	 * @throws RobloxAPIException
	 */
	public function getDataSourceOrThrow( string $id, bool $ignoreCase = false ): IDataSource {
		$source = $this->getDataSource( $id, $ignoreCase );

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
	 */
	private function createParserFunction( IDataSource $dataSource ): RobloxApiParserFunction {
		return new DataSourceParserFunction( $this, $dataSource );
	}

}
