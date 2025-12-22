<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source;

use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Parser\Parser;
use StatusValue;

/**
 * Represents an endpoint of the roblox api.
 */
abstract class FetcherDataSource extends AbstractDataSource {

	/**
	 * Constructs a new data source.
	 * @param string $id The ID of this data source.
	 * @param RobloxAPIFetcher $fetcher An instance of the fetcher service.
	 */
	public function __construct(
		string $id,
		private readonly RobloxAPIFetcher $fetcher
	) {
		parent::__construct( $id );
	}

	/**
	 * Fetches data
	 * @param array<string> $requiredArgs
	 * @param array<string, mixed> $optionalArgs
	 * @return StatusValue<mixed> The fetched data.
	 */
	public function fetch( array $requiredArgs, array $optionalArgs = [] ): StatusValue {
		$endpoint = $this->getEndpoint( $requiredArgs, $optionalArgs );
		$headers = $this->getAdditionalHeaders( $requiredArgs, $optionalArgs );

		$dataStatus = $this->fetcher->getDataFromEndpoint(
			$this->id,
			$endpoint,
			$requiredArgs,
			$optionalArgs,
			$headers,
			$this->processRequestOptions( ... )
		);
		if ( !$dataStatus->isOK() ) {
			return $dataStatus;
		}
		$data = $dataStatus->getValue();

		$processedDataStatus = $this->processData( $data, $requiredArgs, $optionalArgs );
		if ( !$processedDataStatus->isOK() ) {
			return $processedDataStatus;
		}

		$processedData = $processedDataStatus->getValue();
		if ( $processedData === null ) {
			return $this->failInvalidData();
		}

		return StatusValue::newGood( $processedData );
	}

	/**
	 * Returns the endpoint of this data source for the given arguments.
	 * @param array<string> $requiredArgs
	 * @param array<string, mixed> $optionalArgs
	 * @return string The endpoint of this data source.
	 */
	abstract public function getEndpoint( array $requiredArgs, array $optionalArgs ): string;

	/**
	 * Processes the data before returning it.
	 * @param mixed $data The data to process.
	 * @param array<string> $requiredArgs
	 * @param array<string, mixed> $optionalArgs
	 * @return StatusValue<mixed> The processed data.
	 */
	public function processData( mixed $data, array $requiredArgs, array $optionalArgs ): StatusValue {
		return StatusValue::newGood( $data );
	}

	/**
	 * Processes the request options before making the request. This allows modifying the request options.
	 * @param array<string, mixed> &$options The options to process.
	 * @param string[] $requiredArgs
	 * @param array<string, mixed> $optionalArgs
	 */
	public function processRequestOptions( array &$options, array $requiredArgs, array $optionalArgs ): void {
		// do nothing by default
	}

	/**
	 * Allows specifying additional headers for the request.
	 * @param array<string> $requiredArgs
	 * @param array<string, mixed> $optionalArgs
	 * @return array<string, string> The additional headers.
	 */
	protected function getAdditionalHeaders( array $requiredArgs, array $optionalArgs ): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function exec( Parser $parser, array $requiredArgs, array $optionalArgs = [] ): StatusValue {
		return $this->fetch( $requiredArgs, $optionalArgs );
	}

	public function getFetcherSourceId(): string {
		return $this->getId();
	}

}
