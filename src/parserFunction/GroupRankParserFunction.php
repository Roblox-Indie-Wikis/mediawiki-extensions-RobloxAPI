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

namespace MediaWiki\Extension\RobloxAPI\parserFunction;

use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;

class GroupRankParserFunction extends RobloxApiParserFunction {

	public function __construct( DataSourceProvider $dataSourceProvider ) {
		parent::__construct( $dataSourceProvider );
	}

	/**
	 * Executes the parser function.
	 * @param \Parser $parser
	 * @param string $groupId
	 * @param string $userId
	 * @return string
	 */
	public function exec( $parser, $groupId = '', $userId = '' ) {
		$source = $this->dataSourceProvider->getDataSource( 'groupRoles' );

		if ( !$source ) {
			// TODO
			return 'Data source not found';
		}

		$groups = $source->fetch( $userId );

		if ( !$groups ) {
			// TODO
			return 'Failed to parse groups!';
		}

		if ( !is_array( $groups ) ) {
			return "Encountered error: $groups";
		}

		foreach ( $groups as $group ) {
			if ( $group->group->id === (int)$groupId ) {
				return $group->role->name;
			}
		}

		// TODO
		return 'Failed to find group!';
	}

}
