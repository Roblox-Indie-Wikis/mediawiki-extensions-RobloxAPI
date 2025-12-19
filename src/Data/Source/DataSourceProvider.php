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

namespace MediaWiki\Extension\RobloxAPI\Data\Source;

use Closure;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Args\Types\IdArgument;
use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\AssetThumbnailDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\AssetThumbnailUrlDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\GameDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\GameIconDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\GameIconUrlDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\GroupMembersDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\GroupRankDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\PlaceActivePlayersDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\PlaceVisitsDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\UserAvatarThumbnailDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\UserAvatarThumbnailUrlDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\UserIdDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\UserPlaceVisitsDataSource;
use MediaWiki\Extension\RobloxAPI\ParserFunction\DataSourceParserFunction;
use MediaWiki\Extension\RobloxAPI\ParserFunction\RobloxApiParserFunction;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils;
use StatusValue;

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
	public function __construct(
		public ServiceOptions $options,
		private readonly RobloxAPIFetcher $fetcher,
		private readonly RobloxAPIUtils $utils,
	) {
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );

		$this->registerDataSources(
			new GameDataSource( $fetcher ),
			new UserIdDataSource( $fetcher ),
			new UserAvatarThumbnailDataSource( $fetcher ),
			new AssetThumbnailDataSource( $fetcher ),
			new GameIconDataSource( $fetcher ),
		);

		$this->registerSimpleFetcherDataSource(
			'groupRoles',
			new ArgumentSpecification( [ IdArgument::user() ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://groups.roblox.com/v1/users/$args[0]/groups/roles";
			}, static function ( mixed $data, array $requiredArgs, array $optionalArgs ): mixed {
				return $data->data;
			},
			true
		);
		$this->registerSimpleFetcherDataSource(
			'groupData',
			new ArgumentSpecification( [ IdArgument::group() ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://groups.roblox.com/v1/groups/$args[0]";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'groupRolesList',
			new ArgumentSpecification( [ IdArgument::group() ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://groups.roblox.com/v1/groups/$args[0]/roles";
			}
		);
		$this->registerSimpleFetcherDataSource(
			'badgeInfo',
			new ArgumentSpecification( [ IdArgument::badge() ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://badges.roblox.com/v1/badges/$args[0]";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'userInfo',
			new ArgumentSpecification( [ IdArgument::user() ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://users.roblox.com/v1/users/$args[0]";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'assetDetails',
			new ArgumentSpecification( [ IdArgument::asset() ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://economy.roblox.com/v2/assets/$args[0]/details";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'gameNameDescription',
			new ArgumentSpecification( [ IdArgument::universe() ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://gameinternationalization.roblox.com/v1/name-description/games/$args[0]";
			}
		);
		$this->registerSimpleFetcherDataSource(
			'universeInfo',
			new ArgumentSpecification( [ IdArgument::universe() ], [], true ),
			static function ( array $args, array $optionalArgs ): string {
				return "https://develop.roblox.com/v1/universes/$args[0]";
			}
		);
		$this->registerSimpleFetcherDataSource(
			'userGames',
			new ArgumentSpecification(
				[ IdArgument::user() ],
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
				[ IdArgument::universe() ],
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
		$this->registerSimpleFetcherDataSource(
			'groupRoleMembers',
			new ArgumentSpecification(
				[ IdArgument::group(), IdArgument::role() ],
				[ 'limit' => 'GroupRoleMembersLimit', 'sort_order' => 'SortOrder' ],
			),
			static function ( array $args, array $optionalArgs ): string {
				$limit = $optionalArgs['limit'] ?? 50;
				$sortOrder = $optionalArgs['sort_order'] ?? 'Asc';

				return "https://groups.roblox.com/v1/groups/$args[0]/roles/$args[1]/users" .
					"?limit=$limit&sortOrder=$sortOrder";
			},
			static function ( mixed $data, array $requiredArgs, array $optionalArgs ): mixed {
				// TODO this discards cursor data, which should be implemented at some point (maybe via lua)
				return $data->data;
			}
		);

		// dependent data sources
		$this->registerDataSources(
			new GroupRankDataSource( $this ),
			new PlaceActivePlayersDataSource( $this ),
			new PlaceVisitsDataSource( $this ),
			new GroupMembersDataSource( $this ),
			new UserAvatarThumbnailUrlDataSource( $this, $this->utils ),
			new AssetThumbnailUrlDataSource( $this, $this->utils ),
			new GameIconUrlDataSource( $this, $this->utils ),
			new UserPlaceVisitsDataSource( $this ),
		);
	}

	/**
	 * Registers a data source if it is enabled.
	 */
	public function registerDataSource( IDataSource $dataSource ): void {
		$enabledDataSources = $this->options->get( RobloxAPIConstants::ConfEnabledDataSources );

		$id = $dataSource->getId();
		if ( !in_array( $id, $enabledDataSources, true ) ) {
			$dataSource->disable();
		}
		$this->dataSources[$id] = $dataSource;
	}

	/**
	 * Registers data sources if they're enabled.
	 */
	public function registerDataSources( IDataSource ...$dataSources ): void {
		foreach ( $dataSources as $dataSource ) {
			$this->registerDataSource( $dataSource );
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
	 * @return StatusValue<IDataSource>
	 */
	public function tryGetDataSource( string $id, bool $ignoreCase = false ): StatusValue {
		$source = $this->getDataSource( $id, $ignoreCase );

		if ( !$source ) {
			return StatusValue::newFatal( 'robloxapi-error-datasource-not-found', $id );
		}

		return StatusValue::newGood( $source );
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
		return new DataSourceParserFunction( $this->utils, $dataSource );
	}

}
