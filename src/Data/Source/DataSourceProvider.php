<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source;

use Closure;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Args\Types\IdArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\LimitArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\SortOrderArgument;
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
		private readonly ServiceOptions $options,
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
			ArgumentSpecification::for( IdArgument::user() )->withJsonArgs(),
			static function ( array $args, array $optionalArgs ): string {
				return "https://groups.roblox.com/v1/users/$args[0]/groups/roles";
			}, static function ( mixed $data, array $requiredArgs, array $optionalArgs ): mixed {
				return $data->data;
			},
			true
		);
		$this->registerSimpleFetcherDataSource(
			'groupData',
			ArgumentSpecification::for( IdArgument::group() )->withJsonArgs(),
			static function ( array $args, array $optionalArgs ): string {
				return "https://groups.roblox.com/v1/groups/$args[0]";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'groupRolesList',
			ArgumentSpecification::for( IdArgument::group() )->withJsonArgs(),
			static function ( array $args, array $optionalArgs ): string {
				return "https://groups.roblox.com/v1/groups/$args[0]/roles";
			}
		);
		$this->registerSimpleFetcherDataSource(
			'badgeInfo',
			ArgumentSpecification::for( IdArgument::badge() )->withJsonArgs(),
			static function ( array $args, array $optionalArgs ): string {
				return "https://badges.roblox.com/v1/badges/$args[0]";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'userInfo',
			ArgumentSpecification::for( IdArgument::user() )->withJsonArgs(),
			static function ( array $args, array $optionalArgs ): string {
				return "https://users.roblox.com/v1/users/$args[0]";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'assetDetails',
			ArgumentSpecification::for( IdArgument::asset() )->withJsonArgs(),
			static function ( array $args, array $optionalArgs ): string {
				return "https://economy.roblox.com/v2/assets/$args[0]/details";
			},
			null,
			true
		);
		$this->registerSimpleFetcherDataSource(
			'gameNameDescription',
			ArgumentSpecification::for( IdArgument::universe() )->withJsonArgs(),
			static function ( array $args, array $optionalArgs ): string {
				return "https://gameinternationalization.roblox.com/v1/name-description/games/$args[0]";
			}
		);
		$this->registerSimpleFetcherDataSource(
			'universeInfo',
			ArgumentSpecification::for( IdArgument::universe() )->withJsonArgs(),
			static function ( array $args, array $optionalArgs ): string {
				return "https://develop.roblox.com/v1/universes/$args[0]";
			}
		);
		$this->registerSimpleFetcherDataSource(
			'userGames',
			ArgumentSpecification::for( IdArgument::user() )
				->withOptionalArg( 'limit', new LimitArgument( '10', '25', '50' ) )
				->withOptionalArg( 'sort_order', new SortOrderArgument() )
				->withJsonArgs(),
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
			ArgumentSpecification::for( IdArgument::universe() )->withJsonArgs(),
			static function ( array $args, array $optionalArgs ): string {
				return "https://apis.roblox.com/virtual-events/v1/universes/$args[0]/virtual-events";
			},
			static function ( mixed $data, array $requiredArgs, array $optionalArgs ): mixed {
				return $data->data;
			}
		);
		$this->registerSimpleFetcherDataSource(
			'groupRoleMembers',
			ArgumentSpecification::for( IdArgument::group(), IdArgument::role() )
				->withOptionalArg( 'limit', new LimitArgument( '10', '25', '50', '100' ) )
				->withOptionalArg( 'sort_order', new SortOrderArgument() ),
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
	 * @param string $id The ID of the data source.
	 * @param ArgumentSpecification $argumentSpecification The argument specification.
	 * @param Closure( string[], array<string, string> ): string $createEndpoint The function to create the endpoint.
	 * @param null|Closure( mixed, string[], array<string, string> ): (StatusValue<mixed>|mixed|null) $processData
	 * The function to process the data.
	 * @param bool $registerParserFunction Whether to register a legacy parser function.
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
			return StatusValue::newFatal(
				'robloxapi-error-datasource-not-found',
				RobloxAPIUtils::transformValueForError( $id ),
			);
		}

		return StatusValue::newGood( $source );
	}

}
