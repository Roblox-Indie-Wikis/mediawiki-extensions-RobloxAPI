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

use MediaWiki\Extension\RobloxAPI\data\args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtil;
use MediaWiki\Parser\Parser;

abstract class ThumbnailUrlDataSource extends DependentDataSource {

	/**
	 * @inheritDoc
	 */
	public function __construct( DataSourceProvider $dataSourceProvider, string $id, string $dependencyId ) {
		parent::__construct( $dataSourceProvider, $id, $dependencyId );
	}

	/**
	 * @inheritDoc
	 * @return string URL of the thumbnail
	 */
	public function exec(
		DataSourceProvider $dataSourceProvider, Parser $parser, array $requiredArgs, array $optionalArgs = []
	): string {
		$data = $this->dataSource->exec( $dataSourceProvider, $parser, $requiredArgs, $optionalArgs );

		if ( !$data ) {
			$this->failNoData();
		}

		if ( count( $data ) === 0 ) {
			$this->failInvalidData();
		}

		$url = $data[0]->imageUrl;

		if ( !$url ) {
			$this->failInvalidData();
		}

		$format = $optionalArgs['format'] ?? 'Png';
		$lowerFormat = strtolower( $format );

		$url = "$url.$lowerFormat";

		if ( !RobloxAPIUtil::verifyIsRobloxCdnUrl( $url ) ) {
			$this->failInvalidData();
		}

		return $url;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldEscapeResult( mixed $result ): bool {
		// The url should not be escaped here in order to be embedded correctly using $wgEnableImageWhitelist.
		// If the URL was escaped here, it would be URL-encoded and not recognized by MediaWiki as an image URL.
		return !RobloxAPIUtil::verifyIsRobloxCdnUrl( $result );
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return new ArgumentSpecification( [
			'UserID',
			'ThumbnailSize',
		], [
			'is_circular' => 'Boolean',
			'format' => 'ThumbnailFormat',
		], );
	}

}
