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
	public function shouldRegisterLegacyParserFunction(): bool {
		return false;
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
	public function exec( Parser $parser, array $requiredArgs, array $optionalArgs = [] ): StatusValue {
		return $this->fetch( $requiredArgs, $optionalArgs );
	}

	public function getFetcherSourceId(): string {
		return $this->getId();
	}

}
