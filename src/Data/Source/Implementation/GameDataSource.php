<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source\Implementation;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Args\Types\IdArgument;
use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\Data\Source\FetcherDataSource;
use StatusValue;

/**
 * A data source for the roblox games API.
 */
class GameDataSource extends FetcherDataSource {

	public function __construct( RobloxAPIFetcher $fetcher ) {
		parent::__construct( 'gameData', $fetcher );
	}

	/**
	 * @inheritDoc
	 */
	public function getEndpoint( array $requiredArgs, array $optionalArgs ): string {
		return "https://games.roblox.com/v1/games?universeIds=$requiredArgs[0]";
	}

	/**
	 * @inheritDoc
	 */
	public function processData( mixed $data, array $requiredArgs, array $optionalArgs ): StatusValue {
		$entries = $data->data;

		if ( !$entries ) {
			return $this->failInvalidData();
		}

		foreach ( $entries as $entry ) {
			if ( !property_exists( $entry, 'rootPlaceId' ) ) {
				continue;
			}

			if ( $entry->rootPlaceId !== (int)$requiredArgs[1] ) {
				continue;
			}

			return StatusValue::newGood( $entry );
		}

		// TODO reconsider whether this should be an error (also consider in PlaceActivePlayersDataSource)
		return StatusValue::newGood( null );
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRegisterLegacyParserFunction(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return ArgumentSpecification::for( IdArgument::universe(), IdArgument::place() )
			->withJsonArgs();
	}

}
