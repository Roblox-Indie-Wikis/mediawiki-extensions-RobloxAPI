<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source\Implementation;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Args\Types\UsernameArgument;
use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\Data\Source\FetcherDataSource;
use MediaWiki\Json\FormatJson;
use StatusValue;

/**
 * A data source for getting a user's ID from their username.
 */
class UserIdDataSource extends FetcherDataSource {

	/** @inheritDoc */
	public function __construct( RobloxAPIFetcher $fetcher ) {
		parent::__construct( 'userId', $fetcher );
	}

	/** @inheritDoc */
	public function getEndpoint( array $requiredArgs, array $optionalArgs ): string {
		return "https://users.roblox.com/v1/usernames/users";
	}

	/** @inheritDoc */
	public function processData( mixed $data, array $requiredArgs, array $optionalArgs ): StatusValue {
		$entries = $data->data;
		if ( $entries === null || count( $entries ) === 0 ) {
			return $this->failInvalidData();
		}

		$entry = $entries[0];
		if ( $entry === null ) {
			return $this->failNoData();
		}

		if ( !property_exists( $entry, 'id' ) ) {
			return $this->failUnexpectedDataStructure();
		}

		return StatusValue::newGood( $entry->id );
	}

	/** @inheritDoc */
	public function processRequestOptions( array &$options, array $requiredArgs, array $optionalArgs ): void {
		$options['method'] = 'POST';
		$options['postData'] = FormatJson::encode( [ 'usernames' => [ $requiredArgs[0] ] ] );
	}

	/** @inheritDoc */
	protected function getAdditionalHeaders( array $requiredArgs, array $optionalArgs ): array {
		return [
			'Content-Type' => 'application/json',
		];
	}

	/** @inheritDoc */
	public function getArgumentSpecification(): ArgumentSpecification {
		return ArgumentSpecification::for( new UsernameArgument() );
	}

	// special case:
	// for legacy reasons, this data source does not return the full json.
	// instead, it returns the id directly.
	// this is because the id is the same as the one of the legacy parser function.

	/** @inheritDoc */
	public function shouldRegisterLegacyParserFunction(): bool {
		return true;
	}

}
