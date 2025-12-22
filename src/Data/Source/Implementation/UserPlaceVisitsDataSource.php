<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source\Implementation;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Data\Source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\Data\Source\DependentDataSource;
use MediaWiki\Parser\Parser;
use StatusValue;

/**
 * A data source for getting the total amount of visits a user's places have.
 * For performance reasons, this is restricted to the first 50 games the API returns.
 */
class UserPlaceVisitsDataSource extends DependentDataSource {

	public function __construct( DataSourceProvider $dataSourceProvider ) {
		parent::__construct( $dataSourceProvider, 'userPlaceVisits', 'userGames' );
	}

	/**
	 * @inheritDoc
	 */
	public function exec( Parser $parser, array $requiredArgs, array $optionalArgs = [] ): StatusValue {
		$userGamesStatus = $this->dataSource->exec( $parser, $requiredArgs, $optionalArgs );

		if ( !$userGamesStatus->isGood() ) {
			return $userGamesStatus;
		}
		$userGames = $userGamesStatus->getValue();
		if ( $userGames === null ) {
			return $this->failNoData();
		}

		if ( !is_array( $userGames ) ) {
			return $this->failUnexpectedDataStructure();
		}

		$totalVisits = 0;
		foreach ( $userGames as $game ) {
			if ( !property_exists( $game, 'placeVisits' ) ) {
				return $this->failUnexpectedDataStructure();
			}
			$totalVisits += $game->placeVisits;
		}

		return StatusValue::newGood( $totalVisits );
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return $this->dataSource->getArgumentSpecification();
	}
}
