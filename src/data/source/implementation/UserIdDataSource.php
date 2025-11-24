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

namespace MediaWiki\Extension\RobloxAPI\data\source\implementation;

use MediaWiki\Extension\RobloxAPI\data\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\data\source\FetcherDataSource;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIException;
use MediaWiki\Json\FormatJson;
use MediaWiki\Parser\Parser;

/**
 * A data source for getting a user's ID from their username.
 */
class UserIdDataSource extends FetcherDataSource {

	/**
	 * @inheritDoc
	 */
	public function __construct( RobloxAPIFetcher $fetcher ) {
		parent::__construct( 'userId', $fetcher );
	}

	/**
	 * @inheritDoc
	 */
	public function getEndpoint( array $requiredArgs, array $optionalArgs ): string {
		return "https://users.roblox.com/v1/usernames/users";
	}

	/**
	 * @inheritDoc
	 */
	public function processData( mixed $data, array $requiredArgs, array $optionalArgs ): mixed {
		$entries = $data->data;
		if ( $entries === null || count( $entries ) === 0 ) {
			throw new RobloxAPIException( 'robloxapi-error-invalid-data' );
		}

		return $entries[0];
	}

	/**
	 * @inheritDoc
	 */
	public function processRequestOptions( array &$options, array $requiredArgs, array $optionalArgs ): void {
		$options['method'] = 'POST';
		$options['postData'] = FormatJson::encode( [ 'usernames' => [ $requiredArgs[0] ] ] );
	}

	/**
	 * @inheritDoc
	 */
	protected function getAdditionalHeaders( array $requiredArgs, array $optionalArgs ): array {
		return [
			'Content-Type' => 'application/json',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function exec(
		DataSourceProvider $dataSourceProvider, Parser $parser, array $requiredArgs, array $optionalArgs = []
	): mixed {
		$data = $this->fetch( $requiredArgs, $optionalArgs );

		if ( !$data ) {
			throw new RobloxAPIException( 'robloxapi-error-datasource-returned-no-data' );
		}

		if ( !property_exists( $data, 'id' ) ) {
			throw new RobloxAPIException( 'robloxapi-error-unexpected-data-structure' );
		}

		return $data->id;
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return new ArgumentSpecification( [ 'Username' ] );
	}

	// special case:
	// for legacy reasons, this data source does not return the full json.
	// instead, it returns the id directly.
	// this is because the id is the same as the one of the legacy parser function.

	/**
	 * @inheritDoc
	 */
	public function shouldRegisterLegacyParserFunction(): bool {
		return true;
	}

}
