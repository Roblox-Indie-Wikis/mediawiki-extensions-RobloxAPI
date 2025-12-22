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

class GroupRankDataSource extends DependentDataSource {

	/** @inheritDoc */
	public function __construct( DataSourceProvider $dataSourceProvider ) {
		parent::__construct( $dataSourceProvider, 'groupRank', 'groupRoles' );
	}

	/** @inheritDoc */
	public function exec( Parser $parser, array $requiredArgs, array $optionalArgs = [] ): StatusValue {
		$execStatus = $this->dataSource->exec( $parser, [ $requiredArgs[1] ] );
		if ( !$execStatus->isGood() ) {
			return $execStatus;
		}

		$groups = $execStatus->getValue();
		if ( !$groups ) {
			return $this->failNoData();
		}

		if ( !is_array( $groups ) ) {
			return $this->failUnexpectedDataStructure();
		}

		foreach ( $groups as $group ) {
			if ( $group->group->id === (int)$requiredArgs[0] ) {
				return $group->role->name;
			}
		}

		return StatusValue::newFatal( 'robloxapi-error-user-group-not-found' );
	}

	/** @inheritDoc */
	public function getArgumentSpecification(): ArgumentSpecification {
		return ArgumentSpecification::for( IdArgument::group(), IdArgument::user() );
	}

	/** @inheritDoc */
	public function shouldRegisterLegacyParserFunction(): bool {
		return true;
	}

}
