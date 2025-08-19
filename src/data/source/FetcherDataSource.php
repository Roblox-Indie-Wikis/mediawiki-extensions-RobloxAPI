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

use MediaWiki\Extension\RobloxAPI\data\fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Parser\Parser;

/**
 * Represents an endpoint of the roblox api.
 */
abstract class FetcherDataSource implements IDataSource {

	/**
	 * Constructs a new data source.
	 * @param string $id The ID of this data source.
	 * @param RobloxAPIFetcher $fetcher An instance of the fetcher service.
	 */
	public function __construct(
		public readonly string $id,
		private readonly RobloxAPIFetcher $fetcher
	) {
	}

	/**
	 * Fetches data
	 * @param array<string> $requiredArgs
	 * @param array<string, string> $optionalArgs
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	public function fetch( array $requiredArgs, array $optionalArgs = [] ): mixed {
		$endpoint = $this->getEndpoint( $requiredArgs, $optionalArgs );
		$headers = $this->getAdditionalHeaders( $requiredArgs, $optionalArgs );

		$data = $this->fetcher->getDataFromEndpoint(
			$this->id,
			$endpoint,
			$requiredArgs,
			$optionalArgs,
			$headers,
			$this->processRequestOptions( ... )
		);

		$processedData = $this->processData( $data, $requiredArgs, $optionalArgs );

		if ( $processedData === null ) {
			throw new RobloxAPIException( 'robloxapi-error-invalid-data' );
		}

		return $processedData;
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

	public function getFetcherSourceId(): string {
		return $this->getId();
	}

}
