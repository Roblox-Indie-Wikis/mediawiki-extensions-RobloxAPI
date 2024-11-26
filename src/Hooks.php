<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI;

use MediaWiki\Hook\ParserFirstCallInitHook;

class Hooks implements ParserFirstCallInitHook {

	public function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook( 'grouprank', [ $this, 'grouprank' ] );
	}

	public function grouprank( $parser, $groupId = '', $userId = '' ) {
		if ( !$this->isValidId( $groupId ) || !$this->isValidId( $userId ) ) {
			return 'Invalid parameters!';
		}

		$json = file_get_contents( "https://groups.roblox.com/v1/users/$userId/groups/roles" );
		$data = json_decode( $json );

		$groups = $data->data;

		if ( !$groups ) {
			return 'Failed to parse groups!';
		}

		foreach ( $groups as $group ) {
			if ($group->group->id === (int)$groupId) {
				return $group->role->name;
			}
		}

		return 'Failed to find group!';
	}

	private function isValidId( string $string ): bool {
		return preg_match( '/^\d{1,16}$/', $string );
	}

}
