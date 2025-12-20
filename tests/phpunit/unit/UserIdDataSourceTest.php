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

namespace MediaWiki\Extension\RobloxAPI\Tests;

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
		$this->assertStatusError( 'robloxapi-error-request-failed', $status );
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
