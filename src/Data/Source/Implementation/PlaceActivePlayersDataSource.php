<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source\Implementation;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Args\Types\IdArgument;
use MediaWiki\Extension\RobloxAPI\Data\Source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\Data\Source\DependentDataSource;
use MediaWiki\Parser\Parser;
use StatusValue;

class PlaceActivePlayersDataSource extends DependentDataSource {

	/**
	 * @inheritDoc
	 */
	public function __construct( DataSourceProvider $dataSourceProvider ) {
		parent::__construct( $dataSourceProvider, 'activePlayers', 'gameData' );
	}

	/**
	 * @inheritDoc
	 */
	public function exec( Parser $parser, array $requiredArgs, array $optionalArgs = [] ): StatusValue {
		$gameDataStatus = $this->dataSource->exec( $parser, $requiredArgs );

		if ( !$gameDataStatus->isGood() ) {
			return $gameDataStatus;
		}
		$gameData = $gameDataStatus->getValue();
		if ( $gameData === null ) {
			return $this->failNoData();
		}

		return StatusValue::newGood( $gameData->playing );
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return ArgumentSpecification::for( IdArgument::universe(), IdArgument::place() );
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRegisterLegacyParserFunction(): bool {
		return true;
	}

}
