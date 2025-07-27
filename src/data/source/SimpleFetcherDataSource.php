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

use Closure;
use MediaWiki\Extension\RobloxAPI\data\args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\data\fetcher\RobloxAPIFetcher;

/**
 * A simple data source that does not process the data.
 */
class SimpleFetcherDataSource extends FetcherDataSource {

	/**
	 * @inheritDoc
	 * @param Closure( array, array ): string $createEndpoint The function to create the endpoint.
	 * @param Closure( mixed, array, array ): mixed|null $processData The function to process the data.
	 * @param bool $registerParserFunction Whether to register a legacy parser function.
	 */
	public function __construct(
		string $id,
		RobloxAPIFetcher $fetcher,
		protected ArgumentSpecification $argumentSpecification,
		protected Closure $createEndpoint,
		protected ?Closure $processData = null,
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
	public function processData( mixed $data, array $requiredArgs, array $optionalArgs ): mixed {
		if ( $this->processData ) {
			return call_user_func( $this->processData, $data, $requiredArgs, $optionalArgs );
		}

		return $data;
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
