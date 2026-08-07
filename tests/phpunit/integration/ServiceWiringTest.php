<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Tests\Integration;

use MediaWikiIntegrationTestCase;

/**
 * @group RobloxAPI
 * @covers \MediaWiki\Extension\RobloxAPI\Args\ArgumentParser
 * @covers \MediaWiki\Extension\RobloxAPI\Data\Cache\DataSourceCache
 * @covers \MediaWiki\Extension\RobloxAPI\Data\Source\DataSourceProvider
 * @covers \MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher
 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils
 */
class ServiceWiringTest extends MediaWikiIntegrationTestCase {

	public function testServices() {
		// we manually loop over the services so the coverage is 100%
		// this wouldn't work with a data provider
		$services = require __DIR__ . '/../../../src/ServiceWiring.php';
		foreach ( array_keys( $services ) as $service ) {
			$this->getServiceContainer()->get( $service );
			$this->addToAssertionCount( 1 );
		}
	}

}
