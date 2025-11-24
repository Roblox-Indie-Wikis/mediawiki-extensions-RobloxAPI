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

namespace MediaWiki\Extension\RobloxAPI\data\source\implementation;

use MediaWiki\Extension\RobloxAPI\data\args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\data\source\DependentDataSource;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIException;
use MediaWiki\Parser\Parser;

class GroupRankDataSource extends DependentDataSource {

	/**
	 * @inheritDoc
	 */
	public function __construct( DataSourceProvider $dataSourceProvider ) {
		parent::__construct( $dataSourceProvider, 'groupRank', 'groupRoles' );
	}

	/**
	 * @inheritDoc
	 */
	public function exec(
		DataSourceProvider $dataSourceProvider, Parser $parser, array $requiredArgs, array $optionalArgs = []
	): mixed {
		$groups = $this->dataSource->exec( $dataSourceProvider, $parser, [ $requiredArgs[1] ] );

		if ( !$groups ) {
			$this->failNoData();
		}

		if ( !is_array( $groups ) ) {
			$this->failUnexpectedDataStructure();
		}

		foreach ( $groups as $group ) {
			if ( $group->group->id === (int)$requiredArgs[0] ) {
				return $group->role->name;
			}
		}

		throw new RobloxAPIException( 'robloxapi-error-user-group-not-found' );
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return new ArgumentSpecification( [ 'GroupID', 'UserID' ] );
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRegisterLegacyParserFunction(): bool {
		return true;
	}

}
