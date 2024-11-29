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
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;

/**
 * A data source for the roblox groups api (v1).
 */
class GroupDataSource extends DataSource {

	public function __construct() {
		parent::__construct( 'groupData', new SimpleExpiringCache() );
	}

	/**
	 * @inheritDoc
	 */
	public function fetch( ...$args ) {
		[ $groupId ] = RobloxAPIUtil::safeDestructure( $args, 1 );

		RobloxAPIUtil::assertValidIds( $groupId );

		$endpoint = "https://groups.roblox.com/v1/groups/$groupId";

		return $this->cache->fetchJson( $endpoint );
	}

}
