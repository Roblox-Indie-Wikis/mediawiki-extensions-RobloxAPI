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

/**
 * A data source for fetching data about a specific asset.
 */
class AssetDetailsSource extends DataSource {

	public function __construct( Config $config ) {
		parent::__construct( 'assetDetails', self::createSimpleCache(), $config, [
			'AssetID',
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function getEndpoint( $args ): string {
		return "https://economy.roblox.com/v2/assets/{$args[0]}/details";
	}

}
