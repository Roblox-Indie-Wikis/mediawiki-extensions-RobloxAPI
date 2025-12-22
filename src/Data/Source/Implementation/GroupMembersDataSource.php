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

class GroupMembersDataSource extends DependentDataSource {

	/**
	 * @inheritDoc
	 */
	public function __construct( DataSourceProvider $dataSourceProvider ) {
		parent::__construct( $dataSourceProvider, 'groupMembers', 'groupData' );
	}

	/**
	 * @inheritDoc
	 */
	public function exec( Parser $parser, array $requiredArgs, array $optionalArgs = []	): StatusValue {
		$groupDataStatus = $this->dataSource->exec( $parser, $requiredArgs );

		if ( !$groupDataStatus->isOK() ) {
			return $groupDataStatus;
		}
		$groupData = $groupDataStatus->getValue();

		if ( !$groupData ) {
			return $this->failNoData();
		}

		if ( !property_exists( $groupData, 'memberCount' ) ) {
			return $this->failUnexpectedDataStructure();
		}

		return StatusValue::newGood( $groupData->memberCount );
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return ArgumentSpecification::for( IdArgument::group() );
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRegisterLegacyParserFunction(): bool {
		return true;
	}

}
