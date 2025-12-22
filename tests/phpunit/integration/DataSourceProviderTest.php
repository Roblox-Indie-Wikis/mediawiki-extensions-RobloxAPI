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

namespace MediaWiki\Extension\RobloxAPI\Tests\Integration;

use MediaWiki\Extension\RobloxAPI\Data\Source\DataSourceProvider;
use MediaWikiIntegrationTestCase;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Data\Source\DataSourceProvider
 * @group RobloxAPI
 */
class DataSourceProviderTest extends MediaWikiIntegrationTestCase {

	private function getDataSourceProvider(): DataSourceProvider {
		return $this->getServiceContainer()->getService( 'RobloxAPI.DataSourceProvider' );
	}

	public function testTryGetDataSource() {
		$provider = $this->getDataSourceProvider();

		$this->assertStatusError(
			'robloxapi-error-datasource-not-found',
			$provider->tryGetDataSource( 'nonexistent' )
		);

		$this->assertStatusGood( $provider->tryGetDataSource( 'userId' ) );
		$this->assertStatusError(
			'robloxapi-error-datasource-not-found',
			$provider->tryGetDataSource( 'userid' )
		);
		$this->assertStatusGood( $provider->tryGetDataSource( 'userid', true ) );
	}

}
