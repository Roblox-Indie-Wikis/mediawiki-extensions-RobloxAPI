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

use MediaWiki\Extension\RobloxAPI\data\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\data\source\ThumbnailDataSource;

class GameIconDataSource extends ThumbnailDataSource {

	/**
	 * @inheritDoc
	 */
	public function __construct( RobloxAPIFetcher $fetcher ) {
		parent::__construct( 'gameIcon', $fetcher, 'places/gameicons', 'placeIds' );
	}

	public function getEndpoint( array $requiredArgs, array $optionalArgs ): string {
		$returnPolicy = $optionalArgs['return_policy'] ?? 'PlaceHolder';

		return parent::getEndpoint( $requiredArgs, $optionalArgs ) . "&returnPolicy=$returnPolicy";
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		// jpeg is also supported in theory, not by the other thumbnail APIs though
		return ( new ArgumentSpecification( [
			'PlaceID',
			'ThumbnailSize',
		], [
			'is_circular' => 'Boolean',
			'format' => 'ThumbnailFormat',
			'return_policy' => 'ReturnPolicy',
		], ) )->withJsonArgs();
	}

}
