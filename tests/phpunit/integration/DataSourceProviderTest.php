<?php
/**
 * @license GPL-2.0-or-later
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
