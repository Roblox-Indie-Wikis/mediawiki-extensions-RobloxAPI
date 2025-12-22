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

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\Data\Cache\DataSourceCache;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWikiIntegrationTestCase;
use Wikimedia\ObjectCache\WANObjectCache;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Data\Cache\DataSourceCache
 * @group RobloxAPI
 */
class DataSourceCacheTest extends MediaWikiIntegrationTestCase {

	private function getDataSourceCache(): TestingAccessWrapper {
		return TestingAccessWrapper::newFromObject(
			$this->getServiceContainer()->getService( 'RobloxAPI.DataSourceCache' )
		);
	}

	public function testGetCacheKey() {
		$cache = $this->getDataSourceCache();

		$this->assertEquals(
			$cache->getCacheKey( 'https://test.roblox.com/', [ 'arg1', 'arg2' ], [ 'opt1' => 'value1' ] ),
			$cache->getCacheKey( 'https://test.roblox.com/', [ 'arg1', 'arg2' ], [ 'opt2' => 'value1' ] ),
			'Cache keys with the same cache-splitting parameters should be equal'
		);

		$this->assertNotEmpty(
			$cache->getCacheKey( 'https://test.roblox.com/', [], [] ),
			'Cache key should not be empty, even without parameters'
		);

		$this->assertNotEquals(
			$cache->getCacheKey( 'https://test.roblox.com/', [ 'arg1', 'arg3' ], [ 'is_circular' => 'value1' ] ),
			$cache->getCacheKey( 'https://test.roblox.com/', [ 'arg1', 'arg3' ], [ 'is_circular' => 'value2' ] ),
			'Cache keys with different cache-splitting args should not be equal'
		);

		$this->overrideConfigValue(
			RobloxAPIConstants::ConfCacheSplittingOptionalArguments,
			[
				'arg1',
				'arg2',
			]
		);
		$this->resetServices();
		$cache = $this->getDataSourceCache();

		$this->assertEquals(
			$cache->getCacheKey( 'https://test.roblox.com/', [ 'test' ], [ 'arg1' => '1', 'arg2' => '2' ] ),
			$cache->getCacheKey( 'https://test.roblox.com/', [ 'test' ], [ 'arg2' => '2', 'arg1' => '1' ] ),
			'Cache keys with the same arg-splitting optional args in different order should be equal'
		);
	}

	public function testDisabled() {
		$cache = TestingAccessWrapper::newFromObject( new DataSourceCache(
			new ServiceOptions( DataSourceCache::CONSTRUCTOR_OPTIONS, [
				RobloxAPIConstants::ConfCacheSplittingOptionalArguments => [],
				RobloxAPIConstants::ConfDisableCache => true,
			] ),
			$this->createNoOpMock( WANObjectCache::class )
		) );

		$endpoint = 'https://test.roblox.com/';
		$args = [ 'arg1' ];
		$optionalArgs = [];
		$cache->registerCacheEntry(
			$endpoint,
			'test value',
			$args,
			$optionalArgs,
			3600
		);

		$this->assertNull( $cache->getResultForEndpoint( $endpoint, $args, $optionalArgs ),
			'Cache should be disabled and return null' );
	}

	public function testRegisterAndGetCacheEntry() {
		$cache = $this->getDataSourceCache();
		$endpoint = 'https://test.roblox.com/';
		$value = 'test value';
		$args = [ 'arg1', 'arg2' ];
		$optionalArgs = [ 'opt1' => 'value1' ];
		$cache->registerCacheEntry(
			$endpoint,
			$value,
			$args,
			$optionalArgs,
			3600
		);

		$this->assertEquals(
			$value,
			$cache->getResultForEndpoint( $endpoint, $args, $optionalArgs ),
			'Cached value should match the registered value'
		);
		$this->assertEquals(
			$value,
			$cache->getResultForEndpoint( $endpoint, $args, [ 'opt1' => 'value2' ] ),
			'Cached value should match the registered value (optional args not affecting cache key)'
		);

		$this->assertNull( $cache->getResultForEndpoint(
			$endpoint . '2',
			$args,
			$optionalArgs
		), 'Cache miss should return null (endpoint)' );

		$this->assertNull( $cache->getResultForEndpoint(
			$endpoint,
			[ 'arg1', 'arg2', 'arg3' ],
			$optionalArgs
		), 'Cache miss should return null (required args)' );
	}

}
