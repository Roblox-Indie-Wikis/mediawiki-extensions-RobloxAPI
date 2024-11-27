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

use MediaWiki\Config\Config;
use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\parserFunction\ActivePlayersParserFunction;
use MediaWiki\Extension\RobloxAPI\parserFunction\DataSourceParserFunction;
use MediaWiki\Extension\RobloxAPI\parserFunction\GroupRankParserFunction;
use MediaWiki\Extension\RobloxAPI\parserFunction\PlaceVisitsParserFunction;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\MediaWikiServices;

class Hooks implements ParserFirstCallInitHook {

	private Config $config;
	private DataSourceProvider $dataSourceProvider;
	private array $parserFunctions;

	public function __construct() {
		$this->config =
			MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'RobloxAPI' );

		$this->dataSourceProvider = new DataSourceProvider( $this->config );

		$this->parserFunctions = [
			'roblox_grouprank' => new GroupRankParserFunction( $this->dataSourceProvider ),
			'roblox_activeplayers' => new ActivePlayersParserFunction( $this->dataSourceProvider ),
			'roblox_visits' => new PlaceVisitsParserFunction( $this->dataSourceProvider ),
		];
		$this->parserFunctions += $this->dataSourceProvider->createParserFunctions();
	}

	/**
	 * @inheritDoc
	 */
	public function onParserFirstCallInit( $parser ) {
		foreach ( $this->parserFunctions as $id => $function ) {
			// all data source parser functions are only enabled if the corresponding data source
			// is enabled, so we don't need to check the config for that
			$isEnabled =
				$function instanceof DataSourceParserFunction ||
				in_array( $id, $this->config->get( 'RobloxAPIEnabledParserFunctions' ) );
			if ( $isEnabled ) {
				$parser->setFunctionHook( $id, [ $function, 'exec' ] );
			}
		}
	}

}
