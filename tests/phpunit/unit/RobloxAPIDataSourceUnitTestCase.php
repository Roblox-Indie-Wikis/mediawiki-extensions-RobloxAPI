<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Tests\Unit;

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\Data\Cache\DataSourceCache;
use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\Tests\Integration\HttpRequestFactoryTestTrait;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWikiUnitTestCase;
use Wikimedia\ObjectCache\WANObjectCache;

/**
 * Base class for Roblox API unit tests.
 * // TODO convert to trait
 */
abstract class RobloxAPIDataSourceUnitTestCase extends MediaWikiUnitTestCase {
	use HttpRequestFactoryTestTrait;

	private function createMockCache(): DataSourceCache {
		return new DataSourceCache(
			self::createServiceOptions( [
				RobloxAPIConstants::ConfDisableCache => true,
				RobloxAPIConstants::ConfCacheSplittingOptionalArguments => [],
			] ),
			$this->createMock( WANObjectCache::class )
		);
	}

	protected function createMockFetcher( ?string $returnedContent, int $status = 200 ): RobloxAPIFetcher {
		$serviceOptions = self::createServiceOptions( [
			RobloxAPIConstants::ConfCachingExpiries => [ '*' => 600 ],
			RobloxAPIConstants::ConfRequestUserAgent => null
		] );
		[ $httpRequestFactory ] = $this->createMockHttpRequestFactory( $returnedContent, $status );

		return new RobloxAPIFetcher( $serviceOptions, $this->createMockCache(), $httpRequestFactory );
	}

	private static function createServiceOptions( array $options ): ServiceOptions {
		return new ServiceOptions( array_keys( $options ), $options );
	}

}
