<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Tests\Unit;

use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\UserIdDataSource;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\UserIdDataSource
 * @group RobloxAPI
 */
class UserIdDataSourceTest extends RobloxAPIDataSourceUnitTestCase {

	private UserIdDataSource $subject;

	protected function setUp(): void {
		$this->subject = new UserIdDataSource( $this->createMock( RobloxAPIFetcher::class ) );
	}

	public function testProcessData() {
		$data = (object)[
			'data' => [
				(object)[
					'id' => 12345,
				],
			],
		];
		self::assertEquals( 12345, $this->subject->processData( $data, [ 'username' ], [] )->getValue() );

		// test invalid data
		$status = $this->subject->processData( (object)[ 'data' => null ], [ 'username' ], [] );
		$this->assertStatusError( 'robloxapi-error-invalid-data', $status );
	}

	public function testFetch() {
		$result = /** @lang JSON */
			<<<EOD
		{
			"data": [
				{
					"requestedUsername": "abaddriverlol",
					"hasVerifiedBadge": false,
					"id": 4182456156,
					"name": "abaddriverlol",
					"displayName": "abaddriverlol"
				}
			]
		}
		EOD;

		$dataSource = new UserIdDataSource( $this->createMockFetcher( $result ) );

		$data = $dataSource->fetch( [ 'abaddriverlol' ] )->getValue();

		self::assertEquals( 4182456156, $data );
	}

	public function testFetchEmptyResult() {
		$result = /** @lang JSON */
			<<<EOD
		{
			"data": []
		}
		EOD;

		$dataSource = new UserIdDataSource( $this->createMockFetcher( $result ) );

		$status = $dataSource->fetch( [ 'thisuserdoesntexist' ] );
		$this->assertStatusError( 'robloxapi-error-invalid-data', $status );
	}

	public function testFailedRequest() {
		$dataSource = new UserIdDataSource( $this->createMockFetcher( null, 429 ) );

		$status = $dataSource->fetch( [ 'thisrequestwillfail' ] );
		$this->assertStatusError( 'robloxapi-error-request-rate-limited', $status );
	}

	public function testProcessRequestOptions() {
		$dataSource = new UserIdDataSource( $this->createMock( RobloxAPIFetcher::class ) );
		$options = [];
		$args = [ 'example_user' ];
		$dataSource->processRequestOptions( $options, $args, [] );

		self::assertEquals( 'POST', $options['method'] );
		self::assertEquals( '{"usernames":["example_user"]}', $options['postData'] );
	}

}
