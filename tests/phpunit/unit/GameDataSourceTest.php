<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Tests\Unit;

use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\GameDataSource;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Data\Source\Implementation\GameDataSource
 * @group RobloxAPI
 */
class GameDataSourceTest extends RobloxAPIDataSourceUnitTestCase {

	private GameDataSource $subject;

	protected function setUp(): void {
		$this->subject = new GameDataSource( $this->createMock( RobloxAPIFetcher::class ) );
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
		self::assertEquals( $data->data[0], $this->subject->processData( $data, [ 12345, 12345 ], [] )->getValue() );

		$status = $this->subject->processData( (object)[ 'data' => null ], [ 12345, 12345 ], [] );
		$this->assertStatusError( 'robloxapi-error-invalid-data', $status );
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

		$dataSource = new GameDataSource( $this->createMockFetcher( $result ) );

		$data = $dataSource->fetch( [ '6483209208', '132813250731469' ] )->getValue();

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

		$dataSource = new GameDataSource( $this->createMockFetcher( $result ) );

		$status = $dataSource->fetch( [ '4252370517', '12018816388' ] );
		$this->assertStatusError( 'robloxapi-error-invalid-data', $status );
	}

	public function testFailedRequest() {
		$dataSource = new GameDataSource( $this->createMockFetcher( null, 429 ) );

		$status = $dataSource->fetch( [ '4252370517', '12018816388' ] );
		$this->assertStatusError( 'robloxapi-error-request-rate-limited', $status );
	}

}
