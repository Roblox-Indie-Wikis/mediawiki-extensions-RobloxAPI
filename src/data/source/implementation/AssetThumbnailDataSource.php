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

use MediaWiki\Config\Config;
use MediaWiki\Extension\RobloxAPI\data\args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\data\source\FetcherDataSource;

class AssetThumbnailDataSource extends FetcherDataSource {

	/**
	 * @inheritDoc
	 */
	public function __construct( Config $config ) {
		parent::__construct( 'assetThumbnail', self::createSimpleCache(), $config );
	}

	/**
	 * @inheritDoc
	 */
	public function getEndpoint( array $requiredArgs, array $optionalArgs ): string {
		$assetId = $requiredArgs[0];
		$size = $requiredArgs[1];
		$isCircular = $optionalArgs['is_circular'] ?? false;
		$format = $optionalArgs['format'] ?? 'Png';

		return "https://thumbnails.roblox.com/v1/assets" .
			"?assetIds=$assetId&size=$size&format=$format&isCircular=$isCircular";
	}

	/**
	 * @inheritDoc
	 */
	public function processData( $data, array $requiredArgs, array $optionalArgs ) {
		return $data->data;
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return ( new ArgumentSpecification( [
			'AssetID',
			'ThumbnailSize',
		], [
			'is_circular' => 'Boolean',
			'format' => 'ThumbnailFormat',
		], ) )->withJsonArgs();
	}

}
