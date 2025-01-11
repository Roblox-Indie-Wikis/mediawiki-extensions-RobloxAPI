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

use MediaWiki\Config\Config;
use MediaWiki\Extension\RobloxAPI\data\args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use Parser;

/**
 * A data source for the roblox games API.
 */
class GameDataSource extends FetcherDataSource {

	public function __construct( Config $config ) {
		parent::__construct( 'gameData', self::createSimpleCache(), $config );
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
	public function processData( $data, array $requiredArgs, array $optionalArgs ) {
		$entries = $data->data;

		if ( !$entries ) {
			throw new RobloxAPIException( 'robloxapi-error-invalid-data' );
		}

		foreach ( $entries as $entry ) {
			if ( $entry->rootPlaceId !== (int)$requiredArgs[1] ) {
				continue;
			}

			return $entry;
		}

		return null;
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
		return ( new ArgumentSpecification( [
			'UniverseID',
			'PlaceID',
		] ) )->withJsonArgs();
	}

	/**
	 * @inheritDoc
	 */
	public function exec(
		DataSourceProvider $dataSourceProvider, Parser $parser, array $requiredArgs, array $optionalArgs = []
	): string {
		return $this->fetch( ...$requiredArgs );
	}

}
