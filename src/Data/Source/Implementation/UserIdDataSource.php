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

namespace MediaWiki\Extension\RobloxAPI\Data\Source\Implementation;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Args\Types\UsernameArgument;
use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\Data\Source\FetcherDataSource;
use MediaWiki\Json\FormatJson;
use MediaWiki\Parser\Parser;
use StatusValue;

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
	public function processData( mixed $data, array $requiredArgs, array $optionalArgs ): StatusValue {
		$entries = $data->data;
		if ( $entries === null || count( $entries ) === 0 ) {
			return $this->failInvalidData();
		}

		return StatusValue::newGood( $entries[0] );
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
	public function exec( Parser $parser, array $requiredArgs, array $optionalArgs = [] ): StatusValue {
		$dataStatus = $this->fetch( $requiredArgs, $optionalArgs );

		if ( !$dataStatus->isGood() ) {
			return $dataStatus;
		}
		$data = $dataStatus->getValue();
		// TODO do we really need to handle this?
		if ( !$data ) {
			return $this->failNoData();
		}

		if ( !property_exists( $data, 'id' ) ) {
			return $this->failUnexpectedDataStructure();
		}

		return StatusValue::newGood( $data->id );
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return ArgumentSpecification::for( new UsernameArgument() );
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
