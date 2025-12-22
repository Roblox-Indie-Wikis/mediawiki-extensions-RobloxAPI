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

namespace MediaWiki\Extension\RobloxAPI\Tests\Unit;

use GuzzleHttpRequest;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\Data\Cache\DataSourceCache;
use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\Tests\Integration\HttpRequestFactoryTestTrait;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Status\Status;
use MediaWikiUnitTestCase;
use StatusValue;
use Wikimedia\ObjectCache\WANObjectCache;

/**
 * Base class for Roblox API unit tests.
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
