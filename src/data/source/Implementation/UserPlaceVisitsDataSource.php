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

namespace MediaWiki\Extension\RobloxAPI\data\source\Implementation;

use MediaWiki\Extension\RobloxAPI\data\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\data\source\DependentDataSource;
use MediaWiki\Parser\Parser;

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
	public function exec(
		DataSourceProvider $dataSourceProvider, Parser $parser, array $requiredArgs, array $optionalArgs = []
	): mixed {
		$userGames = $this->dataSource->exec( $dataSourceProvider, $parser, $requiredArgs, $optionalArgs );

		if ( $userGames === null ) {
			$this->failNoData();
		}

		if ( !is_array( $userGames ) ) {
			$this->failUnexpectedDataStructure();
		}

		$totalVisits = 0;
		foreach ( $userGames as $game ) {
			if ( !property_exists( $game, 'placeVisits' ) ) {
				$this->failUnexpectedDataStructure();
			}
			$totalVisits += $game->placeVisits;
		}

		return $totalVisits;
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return $this->dataSource->getArgumentSpecification();
	}
}
