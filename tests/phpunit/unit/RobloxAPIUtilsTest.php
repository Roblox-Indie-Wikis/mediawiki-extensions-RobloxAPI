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

use MediaWiki\Config\HashConfig;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIException;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils;
use MediaWiki\Utils\UrlUtils;
use MediaWikiUnitTestCase;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils
 * @group RobloxAPI
 */
class RobloxAPIUtilsTest extends MediaWikiUnitTestCase {

	protected function createUtils(): RobloxAPIUtils {
		return new RobloxAPIUtils();
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::isValidId
	 */
	public function testIsValidId(): void {
		$utils = $this->createUtils();
		self::assertFalse( $utils->isValidId( null ) );
		self::assertFalse( $utils->isValidId( "" ) );
		self::assertFalse( $utils->isValidId( "a" ) );
		self::assertFalse( $utils->isValidId( "2412a4214" ) );
		self::assertFalse( $utils->isValidId( "309713598a" ) );
		self::assertFalse( $utils->isValidId( "4848492840912840912840921842019481" ) );
		self::assertFalse( $utils->isValidId( "-1234" ) );

		self::assertTrue( $utils->isValidId( "1" ) );
		self::assertTrue( $utils->isValidId( "4182456156" ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::assertValidIds
	 */
	public function testAssertValidIds(): void {
		$utils = $this->createUtils();
		$this->expectException( RobloxAPIException::class );
		$utils->assertValidIds( "abc" );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::assertArgAllowed
	 */
	public function testAssertArgsAllowed(): void {
		$config = new HashConfig( [
			'RobloxAPIAllowedArguments' => [
				'UserID' => [ '123454321' ],
				'GroupID' => [],
			],
		] );
		$utils = $this->createUtils();

		$utils->assertArgAllowed( $config, 'UserID', '123454321' );
		$utils->assertArgAllowed( $config, 'GroupID', '14981124' );
		$utils->assertArgAllowed( $config, 'GroupID', '512512312' );
		$utils->assertArgAllowed( $config, 'GroupID', '901480124' );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-arg-not-allowed' );
		$utils->assertArgAllowed( $config, 'UserID', '54321' );
		$utils->assertArgAllowed( $config, 'UserID', '12345' );
		$utils->assertArgAllowed( $config, 'GroupID', '54321' );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::assertValidArg
	 */
	public function testAssertValidArgs(): void {
		$utils = $this->createUtils();
		$utils->assertValidArg( 'UserID', '123454321' );
		$utils->assertValidArg( 'ThumbnailSize', '140x140' );
		$utils->assertValidArg( 'Username', 'builderman_123' );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-thumbnail-size' );
		$utils->assertValidArg( 'ThumbnailSize', '12345' );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-username' );
		$utils->assertValidArg( 'Username', '__invalidusername' );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::createJsonResult
	 */
	public function testCreateJsonResult(): void {
		$utils = $this->createUtils();
		$jsonString = /** @lang JSON */
			<<<EOD
				{
					"requestedUsername": "abaddriverlol",
					"hasVerifiedBadge": false,
					"id": 4182456156,
					"name": "abaddriverlol",
					"displayName": "abaddriverlol"
				}
		EOD;
		$jsonObject = \FormatJson::decode( $jsonString );
		self::assertEquals( 'abaddriverlol',
			$utils->createJsonResult( $jsonObject, [ 'json_key' => 'requestedUsername' ] ) );
		self::assertEquals( '{"requestedUsername":"abaddriverlol","hasVerifiedBadge":false,"id":4182456156,' .
			'"name":"abaddriverlol","displayName":"abaddriverlol"}',
			$utils->createJsonResult( $jsonObject, [] ) );

		// test non-existent key
		self::assertEquals( 'null', $utils->createJsonResult( $jsonObject, [ 'json_key' => 'doesnotexist' ] ) );

		// test invalid key path
		self::assertEquals( 'null',
			$utils->createJsonResult( $jsonObject, [ 'json_key' => 'doesnotexist->->' ] ) );

		// test keys pointing to non-objects
		self::assertEquals( 'null',
			$utils->createJsonResult( $jsonObject, [ 'json_key' => 'requestedUsername->id' ] ) );

		// test array index
		$jsonString = /** @lang JSON */
			<<<EOD
				{
					"someData": [
						"someValue"
					]
				}
		EOD;
		$jsonObject = \FormatJson::decode( $jsonString );
		self::assertEquals( 'someValue',
			$utils->createJsonResult( $jsonObject, [ 'json_key' => 'someData->0' ] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::getJsonKey
	 */
	public function testGetJsonKey(): void {
		$utils = $this->createUtils();
		$jsonString = /** @lang JSON */
			<<<EOD
				{
					"someData": {
						"someNestedData": "someValue"
					}
				}
		EOD;
		$jsonObject = \FormatJson::decode( $jsonString );
		self::assertEquals( 'someValue', $utils->getJsonKey( $jsonObject, 'someData->someNestedData' ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::verifyIsRobloxCdnUrl
	 */
	public function testVerifyIsRobloxCdnUrl(): void {
		$utils = $this->createUtils();

		self::assertTrue( $utils->verifyIsRobloxCdnUrl( 'https://tr.rbxcd' .
			'n.com/30DAY-Avatar-7B1E1A9240F5DE0598D6FD97DBC8859F-Png/140/140/Avatar/Png/noFilter.png' ) );
		self::assertTrue( $utils->verifyIsRobloxCdnUrl( 'https://tr.rbxcdn.co' .
			'm/30DAY-Avatar-7B1E1A9240F5DE0598D6FD97DBC8859F-Png/140/140/Avatar/Png/noFilter.webp' ) );

		self::assertFalse( $utils->verifyIsRobloxCdnUrl( 'https://roblox.com/1234/' ) );
		self::assertFalse( $utils->verifyIsRobloxCdnUrl( 'https://t0.rbxcdn.com///https://google.com/') );
	}

}
