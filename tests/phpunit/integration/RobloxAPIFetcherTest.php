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

use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWikiIntegrationTestCase;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher
 * @group RobloxAPI
 */
class RobloxAPIFetcherTest extends MediaWikiIntegrationTestCase {
	use HttpRequestFactoryTestTrait;

	private function getFetcher(): TestingAccessWrapper {
		return TestingAccessWrapper::newFromObject(
			$this->getServiceContainer()->getService( 'RobloxAPI.RobloxAPIFetcher' )
		);
	}

	public function testSuccessfulDataFetch() {
		$sampleData = [ 'key' => 'value', 'number' => 42 ];
		$jsonData = json_encode( $sampleData );

		[ $httpRequestFactory ] = $this->createMockHttpRequestFactory( $jsonData );
		$this->setService(
			'HttpRequestFactory',
			$httpRequestFactory
		);
		$this->resetServices();

		$fetcher = $this->getFetcher();

		$result = $fetcher->getDataFromEndpoint( 'testSource', 'some/endpoint', [], [], [], static fn () => [] );

		$this->assertStatusValue( (object)$sampleData, $result );
	}

	public function testCachingBehavior() {
		$sampleData = [ 'cachedKey' => 'cachedValue' ];
		$jsonData = json_encode( $sampleData );

		[ $httpRequestFactory ] = $this->createMockHttpRequestFactory( $jsonData );
		$this->setService(
			'HttpRequestFactory',
			$httpRequestFactory
		);
		$this->resetServices();

		$fetcher = $this->getFetcher();

		// First fetch should hit the HTTP request
		$result1 = $fetcher->getDataFromEndpoint( 'testSource', 'some/endpoint', [], [], [], static fn () => [] );
		$this->assertStatusValue( (object)$sampleData, $result1 );

		// Second fetch should use the cache and not hit the HTTP request again
		$result2 = $fetcher->getDataFromEndpoint( 'testSource', 'some/endpoint', [], [], [], static fn () => [] );
		$this->assertStatusValue( (object)$sampleData, $result2 );
	}

	public function testHeaders() {
		$expectedUserAgent = 'RobloxAPI Test Agent/1.0';

		$this->overrideConfigValue(
			RobloxAPIConstants::ConfRequestUserAgent,
			$expectedUserAgent
		);

		[ $httpRequestFactory, $request ] = $this->createMockHttpRequestFactory( json_encode( [] ), );
		$this->setService(
			'HttpRequestFactory',
			$httpRequestFactory
		);
		$this->resetServices();

		$fetcher = $this->getFetcher();
		$fetcher->getDataFromEndpoint( 'testSource', 'some/endpoint', [], [], [
			'Custom-Header' => 'test123'
		], static fn () => [] );

		$request = TestingAccessWrapper::newFromObject( $request );
		$headers = $request->reqHeaders;
		$this->assertEquals( 'test123', $headers['Custom-Header'] );
		$this->assertEquals( $expectedUserAgent, $headers['User-Agent'] );
		$this->assertEquals( 'application/json', $headers['Accept'] );
	}

	public function testInvalidJsonHandling() {
		[ $httpRequestFactory ] = $this->createMockHttpRequestFactory( 'invalid json' );
		$this->setService( 'HttpRequestFactory', $httpRequestFactory );
		$this->resetServices();

		$fetcher = $this->getFetcher();

		$this->assertStatusError( 'robloxapi-error-decode-failure',
			$fetcher->getDataFromEndpoint( 'testSource', 'some/endpoint', [], [], [], static fn () => [] ) );
	}

	public function test429ResponseHandling() {
		[ $httpRequestFactory ] = $this->createMockHttpRequestFactory( null, 429 );
		$this->setService(
			'HttpRequestFactory',
			$httpRequestFactory
		);
		$this->resetServices();

		$fetcher = $this->getFetcher();

		$this->assertStatusError(
			'robloxapi-error-request-rate-limited',
			$fetcher->getDataFromEndpoint( 'testSource', 'some/endpoint', [], [], [], static fn () => [] )
		);
	}

	public function testBadStatusHandling() {
		[ $httpRequestFactory ] = $this->createMockHttpRequestFactory( null, 500, false );
		$this->setService(
			'HttpRequestFactory',
			$httpRequestFactory
		);
		$this->resetServices();

		$fetcher = $this->getFetcher();

		$this->assertStatusError(
			'robloxapi-error-request-failed',
			$fetcher->getDataFromEndpoint( 'testSource', 'some/endpoint', [], [], [], static fn () => [] )
		);
	}

	public function testRateLimitedDataSources() {
		$fetcher = $this->getFetcher();
		$fetcher->rateLimitedDataSources = [ 'sourceA', 'sourceB' ];

		$this->assertStatusError(
			'robloxapi-error-request-cancelled-rate-limits',
			$fetcher->getDataFromEndpoint( 'sourceA', 'some/endpoint', [], [], [], static fn () => [] )
		);
	}

	public function testGetCachingExpiry() {
		$this->overrideConfigValue(
			RobloxAPIConstants::ConfCachingExpiries,
			[
				'testSource' => 3600,
				'*' => 600
			]
		);
		$this->resetServices();
		$fetcher = $this->getFetcher();

		$this->assertEquals( 3600, $fetcher->getCachingExpiry( 'testSource' ) );
		$this->assertEquals( 600, $fetcher->getCachingExpiry( 'unknownSource' ) );
	}

}
