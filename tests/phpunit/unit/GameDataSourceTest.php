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

use MediaWiki\Config\Config;
use MediaWiki\Extension\RobloxAPI\data\source\implementation\GameDataSource;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\data\source\implementation\GameDataSource
 * @group RobloxAPI
 */
class GameDataSourceTest extends RobloxAPIDataSourceUnitTestCase {

	private GameDataSource $subject;

	protected function setUp(): void {
		$this->subject = new GameDataSource( $this->createMock( Config::class ) );
	}

	public function testGetEndpoint() {
		self::assertEquals( 'https://games.roblox.com/v1/games?universeIds=12345', $this->subject->getEndpoint( [
			12345,
			54321,
		], [] ) );
	}

	public function testProcessData() {
		$data = (object)[
			'data' => [
				(object)[
					'rootPlaceId' => 12345,
				],
			],
		];
		self::assertEquals( $data->data[0], $this->subject->processData( $data, [ 12345, 12345 ], [] ) );

		// test invalid data
		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-data' );
		$this->subject->processData( (object)[ 'data' => null ], [ 12345, 12345 ], [] );
	}

	public function testFetch() {
		$result = /** @lang JSON */
			<<<EOD
		{
			"data": [
				{
					"id": 6483209208,
					"rootPlaceId": 132813250731469,
					"name": "The Hybrid Cafe",
					"description": "[shortened]",
					"sourceName": "[\\ud83d\\udc7d] The Hybrid Cafe \\u2615",
					"sourceDescription": "[shortened]",
					"creator": {
						"id": 5353743,
						"name": "ArenaDev",
						"type": "Group",
						"isRNVAccount": false,
						"hasVerifiedBadge": true
					},
					"price": null,
					"allowedGearGenres": [
						"All"
					],
					"allowedGearCategories": [],
					"isGenreEnforced": false,
					"copyingAllowed": false,
					"playing": 982,
					"visits": 5410307,
					"maxPlayers": 20,
					"created": "2024-08-29T02:54:27.323Z",
					"updated": "2025-02-01T14:01:05.03Z",
					"studioAccessToApisAllowed": false,
					"createVipServersAllowed": false,
					"universeAvatarType": "MorphToR6",
					"genre": "All",
					"genre_l1": "Adventure",
					"genre_l2": "Story",
					"isAllGenre": true,
					"isFavoritedByUser": false,
					"favoritedCount": 71987
				}
			]
		}
		EOD;

		$dataSource = new GameDataSource( $this->createMock( Config::class ) );
		$dataSource->setHttpRequestFactory( $this->createMockHttpRequestFactory( $result ) );

		$data = $dataSource->fetch( [ '6483209208', '132813250731469' ] );

		self::assertEquals( 6483209208, $data->id );
		self::assertEquals( 132813250731469, $data->rootPlaceId );
		self::assertEquals( 'The Hybrid Cafe', $data->name );
	}

	public function testFetchEmptyResult() {
		$result = /** @lang JSON */
			<<<EOD
		{
			"data": []
		}
		EOD;

		$dataSource = new GameDataSource( $this->createMock( Config::class ) );
		$dataSource->setHttpRequestFactory( $this->createMockHttpRequestFactory( $result ) );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-data' );
		$dataSource->fetch( [ '4252370517', '12018816388' ] );
	}

	public function testFailedRequest() {
		$dataSource = new GameDataSource( $this->createMock( Config::class ) );
		$dataSource->setHttpRequestFactory( $this->createMockHttpRequestFactory( null, 429 ) );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-request-failed' );
		$dataSource->fetch( [ '4252370517', '12018816388' ] );
	}

}
