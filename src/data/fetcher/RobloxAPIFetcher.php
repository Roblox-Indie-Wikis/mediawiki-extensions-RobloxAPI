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

namespace MediaWiki\Extension\RobloxAPI\data\fetcher;

use Closure;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\data\cache\DataSourceCache;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIConstants;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Json\FormatJson;
use MediaWiki\Logger\LoggerFactory;

/**
 * This class holds all logic for fetching data from Roblox API endpoints.
 * @since 1.6.0
 */
class RobloxAPIFetcher {

	public const CONSTRUCTOR_OPTIONS = [
		RobloxAPIConstants::ConfCachingExpiries,
		RobloxAPIConstants::ConfRequestUserAgent,
	];
	
	public function __construct(
		private readonly ServiceOptions $options,
		private readonly DataSourceCache $cache,
		private readonly HttpRequestFactory $httpRequestFactory
	) {
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
	}

	/**
	 * Fetches data from the given endpoint.
	 * @param string $dataSourceId The ID of the data source
	 * @param string $endpoint The endpoint to fetch data from.
	 * @param array<string> $requiredArgs
	 * @param array<string, string> $optionalArgs
	 * @param array<string> $headers Additional headers that should be added
	 * @param Closure( array<string, mixed>&, array<string>, array<string, string> ): void $processRequestOptions
	 * @return mixed The fetched data.
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	public function getDataFromEndpoint(
		string $dataSourceId,
		string $endpoint,
		array $requiredArgs,
		array $optionalArgs,
		array $headers,
		Closure $processRequestOptions
	): mixed {
		$cached_result = $this->cache->getResultForEndpoint( $endpoint, $requiredArgs, $optionalArgs );

		if ( $cached_result !== null ) {
			return $cached_result;
		}

		$options = [];

		$userAgent = $this->options->get( RobloxAPIConstants::ConfRequestUserAgent );
		if ( $userAgent !== null && $userAgent !== '' ) {
			$options['userAgent'] = $userAgent;
		}

		$processRequestOptions( $options, $requiredArgs, $optionalArgs );

		// @phan-suppress-next-line PhanParamTooFewInPHPDoc the $caller arg has a default so no need to supply it
		$request = $this->httpRequestFactory->create( $endpoint, $options );
		$request->setHeader( 'Accept', 'application/json' );

		foreach ( $headers as $header => $value ) {
			$request->setHeader( $header, $value );
		}

		$status = $request->execute();

		if ( !$status->isOK() ) {
			$logger = LoggerFactory::getInstance( 'RobloxAPI' );
			$errors = $status->getMessages( 'error' );
			$logger->warning( 'Failed to fetch data from Roblox API', [
				'endpoint' => $endpoint,
				'errors' => $errors,
				'status' => $status->getStatusValue(),
				'content' => $request->getContent(),
			] );
		}

		$json = $request->getContent();

		if ( !$status->isOK() || $json === null ) {
			throw new RobloxAPIException( 'robloxapi-error-request-failed' );
		}

		$data = FormatJson::decode( $json );

		if ( $data === null ) {
			throw new RobloxAPIException( 'robloxapi-error-decode-failure' );
		}

		$this->cache->registerCacheEntry(
			$endpoint,
			$data,
			$requiredArgs,
			$optionalArgs,
			$this->getCachingExpiry( $dataSourceId )
		);

		return $data;
	}

	/**
	 * Gets the caching expiry for a data source.
	 * If a specific value is not set, the default value (key '*') is used.
	 * @param string $id The ID of the data source
	 * @return int The caching expiry in seconds.
	 */
	protected function getCachingExpiry( string $id ): int {
		$cachingExpiries = $this->options->get( RobloxAPIConstants::ConfCachingExpiries );
		if ( !isset( $cachingExpiries[$id] ) ) {
			return $cachingExpiries['*'];
		}

		return $cachingExpiries[$id];
	}

}
