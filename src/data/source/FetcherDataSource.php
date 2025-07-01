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

use MediaWiki\Config\Config;
use MediaWiki\Extension\RobloxAPI\data\cache\DataSourceCache;
use MediaWiki\Extension\RobloxAPI\data\cache\EmptyCache;
use MediaWiki\Extension\RobloxAPI\data\cache\SimpleExpiringCache;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Json\FormatJson;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;

/**
 * Represents an endpoint of the roblox api.
 */
abstract class FetcherDataSource implements IDataSource {

	/**
	 * @var string The ID of this data source.
	 */
	public string $id;
	/**
	 * @var DataSourceCache The cache of this data source.
	 */
	protected DataSourceCache $cache;
	/**
	 * @var Config The extension configuration.
	 */
	protected Config $config;
	/**
	 * @var ?HttpRequestFactory The HTTP request factory. Can be overridden for testing.
	 */
	private ?HttpRequestFactory $httpRequestFactory;

	/**
	 * Constructs a new data source.
	 * @param string $id The ID of this data source.
	 * @param DataSourceCache $cache The cache of this data source.
	 * @param Config $config The extension configuration.
	 */
	public function __construct(
		string $id, DataSourceCache $cache, Config $config
	) {
		$this->id = $id;
		$this->cache = $cache;
		$this->config = $config;
	}

	public function setCacheExpiry( int $seconds ): void {
		$this->cache->setExpiry( $seconds );
	}

	/**
	 * Fetches data
	 * @param array<string> $requiredArgs
	 * @param array<string, string> $optionalArgs
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	public function fetch( array $requiredArgs, array $optionalArgs = [] ): mixed {
		$endpoint = $this->getEndpoint( $requiredArgs, $optionalArgs );
		$data = $this->getDataFromEndpoint( $endpoint, $requiredArgs, $optionalArgs );

		$processedData = $this->processData( $data, $requiredArgs, $optionalArgs );

		if ( $processedData === null ) {
			throw new RobloxAPIException( 'robloxapi-error-invalid-data' );
		}

		return $processedData;
	}

	/**
	 * Fetches data from the given endpoint.
	 * @param string $endpoint The endpoint to fetch data from.
	 * @param array<string> $requiredArgs
	 * @param array<string, string> $optionalArgs
	 * @return mixed The fetched data.
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	public function getDataFromEndpoint( string $endpoint, array $requiredArgs, array $optionalArgs ): mixed {
		$cached_result = $this->cache->getResultForEndpoint( $endpoint, $requiredArgs, $optionalArgs );

		if ( $cached_result !== null ) {
			return $cached_result;
		}

		$options = [];

		$userAgent = $this->config->get( 'RobloxAPIRequestUserAgent' );
		if ( $userAgent !== null && $userAgent !== '' ) {
			$options['userAgent'] = $userAgent;
		}

		$this->processRequestOptions( $options, $requiredArgs, $optionalArgs );

		$this->httpRequestFactory ??= MediaWikiServices::getInstance()->getHttpRequestFactory();
		// @phan-suppress-next-line PhanParamTooFewInPHPDoc the $caller arg has a default so no need to supply it
		$request = $this->httpRequestFactory->create( $endpoint, $options );
		$request->setHeader( 'Accept', 'application/json' );

		$headers = $this->getAdditionalHeaders( $requiredArgs, $optionalArgs );
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

		$this->cache->registerCacheEntry( $endpoint, $data, $requiredArgs, $optionalArgs );

		return $data;
	}

	/**
	 * Returns the endpoint of this data source for the given arguments.
	 * @param array<string> $requiredArgs
	 * @param array<string, string> $optionalArgs
	 * @return string The endpoint of this data source.
	 */
	abstract public function getEndpoint( array $requiredArgs, array $optionalArgs ): string;

	/**
	 * Processes the data before returning it.
	 * @param mixed $data The data to process.
	 * @param array<string> $requiredArgs
	 * @param array<string, string> $optionalArgs
	 * @return mixed The processed data.
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	public function processData( mixed $data, array $requiredArgs, array $optionalArgs ): mixed {
		return $data;
	}

	/**
	 * Processes the request options before making the request. This allows modifying the request options.
	 * @param array<string, mixed> &$options The options to process.
	 * @param string[] $requiredArgs
	 * @param array<string, string> $optionalArgs
	 */
	public function processRequestOptions( array &$options, array $requiredArgs, array $optionalArgs ): void {
		// do nothing by default
	}

	/**
	 * Creates a simple expiring cache. If we're in a unit test environment, an empty cache is created.
	 * @return DataSourceCache The created cache.
	 */
	protected static function createSimpleCache(): DataSourceCache {
		global $wgRobloxAPIDisableCache;
		if ( defined( 'MW_PHPUNIT_TEST' ) || $wgRobloxAPIDisableCache ) {
			// we're either in a unit test environment or the cache is disabled
			return new EmptyCache();
		}

		return new SimpleExpiringCache();
	}

	/**
	 * Allows specifying additional headers for the request.
	 * @param array<string> $requiredArgs
	 * @param array<string, string> $optionalArgs
	 * @return array<string, string> The additional headers.
	 */
	protected function getAdditionalHeaders( array $requiredArgs, array $optionalArgs ): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRegisterLegacyParserFunction(): bool {
		return false;
	}

	/**
	 * Sets the HTTP request factory.
	 * @param HttpRequestFactory $httpRequestFactory The HTTP request factory.
	 */
	public function setHttpRequestFactory( HttpRequestFactory $httpRequestFactory ): void {
		$this->httpRequestFactory = $httpRequestFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldEscapeResult( mixed $result ): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	public function exec(
		DataSourceProvider $dataSourceProvider, Parser $parser, array $requiredArgs, array $optionalArgs = []
	): mixed {
		return $this->fetch( $requiredArgs, $optionalArgs );
	}

}
