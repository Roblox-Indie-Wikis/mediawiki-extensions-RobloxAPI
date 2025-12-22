<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source;

use Closure;
use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use StatusValue;

/**
 * A simple data source that does not process the data.
 */
class SimpleFetcherDataSource extends FetcherDataSource {

	/**
	 * @inheritDoc
	 * @param Closure( string[], array<string, string> ): string $createEndpoint The function to create the endpoint.
	 * @param null|Closure( mixed, string[], array<string, string> ): (StatusValue<mixed>|mixed|null) $processDataFn
	 * The function to process the data.
	 * @param bool $registerParserFunction Whether to register a legacy parser function.
	 */
	public function __construct(
		string $id,
		RobloxAPIFetcher $fetcher,
		protected ArgumentSpecification $argumentSpecification,
		protected Closure $createEndpoint,
		protected ?Closure $processDataFn = null,
		protected bool $registerParserFunction = false
	) {
		parent::__construct( $id, $fetcher );
	}

	/**
	 * @inheritDoc
	 */
	public function getEndpoint( array $requiredArgs, array $optionalArgs ): string {
		return call_user_func( $this->createEndpoint, $requiredArgs, $optionalArgs );
	}

	/**
	 * @inheritDoc
	 */
	public function processData( mixed $data, array $requiredArgs, array $optionalArgs ): StatusValue {
		if ( $this->processDataFn ) {
			$processedData = call_user_func( $this->processDataFn, $data, $requiredArgs, $optionalArgs );
			if ( !$processedData instanceof StatusValue ) {
				$processedData = StatusValue::newGood( $processedData );
			}
			return $processedData;
		}

		return StatusValue::newGood( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRegisterLegacyParserFunction(): bool {
		return $this->registerParserFunction;
	}

	public function getArgumentSpecification(): ArgumentSpecification {
		return $this->argumentSpecification;
	}

}
