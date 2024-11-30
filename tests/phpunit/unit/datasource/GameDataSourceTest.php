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

namespace phpunit\unit\datasource;

use MediaWiki\Config\Config;
use MediaWiki\Extension\RobloxAPI\data\source\GameDataSource;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\data\source\GameDataSource
 * @group RobloxAPI
 */
class GameDataSourceTest extends \MediaWikiUnitTestCase {

	private GameDataSource $subject;

	protected function setUp(): void {
		$this->subject = new GameDataSource( $this->createMock( Config::class ) );
	}

	public function testGetEndpoint() {
		self::assertEquals( 'https://games.roblox.com/v1/games?universeIds=12345', $this->subject->getEndpoint( [
			12345, 54321
		] ) );
	}

	public function testProcessData() {
		$data = (object)[
			'data' => [
				(object)[
					'rootPlaceId' => 12345,
				],
			],
		];
		self::assertEquals( $data->data[0], $this->subject->processData( $data, [ 12345, 12345 ] ) );

		// test invalid data
		$this->expectException( \MediaWiki\Extension\RobloxAPI\util\RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-data' );
		$this->subject->processData( (object)[ 'data' => null ], [ 12345, 12345 ] );
	}

}
