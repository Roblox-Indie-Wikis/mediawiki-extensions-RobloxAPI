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

namespace MediaWiki\Extension\RobloxAPI\data\source;

use MediaWiki\Extension\RobloxAPI\data\cache\SimpleExpiringCache;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;

/**
 * A data source for the roblox games API.
 */
class GameDataSource extends DataSource {

	public function __construct() {
		parent::__construct( 'gameData', new SimpleExpiringCache() );
	}

	/**
	 * @inheritDoc
	 */
	public function fetch( ...$args ) {
		[ $universeId, $gameId ] = $args;

		RobloxAPIUtil::assertValidIds( [ $universeId, $gameId ] );

		$endpoint = "https://games.roblox.com/v1/games?universeIds=$universeId";
		$data = $this->cache->fetchJson( $endpoint );

		$entries = $data->data;

		if ( !$entries ) {
			throw new RobloxAPIException( 'robloxapi-error-invalid-data' );
		}

		foreach ( $entries as $entry ) {
			if ( $entry->rootPlaceId !== (int)$gameId ) {
				continue;
			}

			return $entry;
		}

		return null;
	}
}
