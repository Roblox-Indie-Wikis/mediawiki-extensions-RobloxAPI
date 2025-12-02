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

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::isValidId
	 */
	public function testIsValidId(): void {
		self::assertFalse( RobloxAPIUtils::isValidId( null ) );
		self::assertFalse( RobloxAPIUtils::isValidId( "" ) );
		self::assertFalse( RobloxAPIUtils::isValidId( "a" ) );
		self::assertFalse( RobloxAPIUtils::isValidId( "2412a4214" ) );
		self::assertFalse( RobloxAPIUtils::isValidId( "309713598a" ) );
		self::assertFalse( RobloxAPIUtils::isValidId( "4848492840912840912840921842019481" ) );
		self::assertFalse( RobloxAPIUtils::isValidId( "-1234" ) );

		self::assertTrue( RobloxAPIUtils::isValidId( "1" ) );
		self::assertTrue( RobloxAPIUtils::isValidId( "4182456156" ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::assertValidIds
	 */
	public function testAssertValidIds(): void {
		$this->expectException( RobloxAPIException::class );
		RobloxAPIUtils::assertValidIds( "abc" );
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

		RobloxAPIUtils::assertArgAllowed( $config, 'UserID', '123454321' );
		RobloxAPIUtils::assertArgAllowed( $config, 'GroupID', '14981124' );
		RobloxAPIUtils::assertArgAllowed( $config, 'GroupID', '512512312' );
		RobloxAPIUtils::assertArgAllowed( $config, 'GroupID', '901480124' );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-arg-not-allowed' );
		RobloxAPIUtils::assertArgAllowed( $config, 'UserID', '54321' );
		RobloxAPIUtils::assertArgAllowed( $config, 'UserID', '12345' );
		RobloxAPIUtils::assertArgAllowed( $config, 'GroupID', '54321' );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::assertValidArg
	 */
	public function testAssertValidArgs(): void {
		RobloxAPIUtils::assertValidArg( 'UserID', '123454321' );
		RobloxAPIUtils::assertValidArg( 'ThumbnailSize', '140x140' );
		RobloxAPIUtils::assertValidArg( 'Username', 'builderman_123' );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-thumbnail-size' );
		RobloxAPIUtils::assertValidArg( 'ThumbnailSize', '12345' );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-username' );
		RobloxAPIUtils::assertValidArg( 'Username', '__invalidusername' );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::createJsonResult
	 */
	public function testCreateJsonResult(): void {
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
			RobloxAPIUtils::createJsonResult( $jsonObject, [ 'json_key' => 'requestedUsername' ] ) );
		self::assertEquals( '{"requestedUsername":"abaddriverlol","hasVerifiedBadge":false,"id":4182456156,' .
			'"name":"abaddriverlol","displayName":"abaddriverlol"}',
			RobloxAPIUtils::createJsonResult( $jsonObject, [] ) );

		// test non-existent key
		self::assertEquals( 'null', RobloxAPIUtils::createJsonResult( $jsonObject, [ 'json_key' => 'doesnotexist' ] ) );

		// test invalid key path
		self::assertEquals( 'null',
			RobloxAPIUtils::createJsonResult( $jsonObject, [ 'json_key' => 'doesnotexist->->' ] ) );

		// test keys pointing to non-objects
		self::assertEquals( 'null',
			RobloxAPIUtils::createJsonResult( $jsonObject, [ 'json_key' => 'requestedUsername->id' ] ) );

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
			RobloxAPIUtils::createJsonResult( $jsonObject, [ 'json_key' => 'someData->0' ] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::getJsonKey
	 */
	public function testGetJsonKey(): void {
		$jsonString = /** @lang JSON */
			<<<EOD
				{
					"someData": {
						"someNestedData": "someValue"
					}
				}
		EOD;
		$jsonObject = \FormatJson::decode( $jsonString );
		self::assertEquals( 'someValue', RobloxAPIUtils::getJsonKey( $jsonObject, 'someData->someNestedData' ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::verifyIsRobloxCdnUrl
	 */
	public function testVerifyIsRobloxCdnUrl(): void {
		$urlUtils = new UrlUtils();

		self::assertTrue( RobloxAPIUtils::verifyIsRobloxCdnUrl( 'https://tr.rbxcd' .
			'n.com/30DAY-Avatar-7B1E1A9240F5DE0598D6FD97DBC8859F-Png/140/140/Avatar/Png/noFilter.png', $urlUtils ) );
		self::assertTrue( RobloxAPIUtils::verifyIsRobloxCdnUrl( 'https://tr.rbxcdn.co' .
			'm/30DAY-Avatar-7B1E1A9240F5DE0598D6FD97DBC8859F-Png/140/140/Avatar/Png/noFilter.webp', $urlUtils ) );

		self::assertFalse( RobloxAPIUtils::verifyIsRobloxCdnUrl( 'https://roblox.com/1234/', $urlUtils ) );
		self::assertFalse( RobloxAPIUtils::verifyIsRobloxCdnUrl( 'https://t0.rbxcdn.com///https://google.com/',
			$urlUtils ) );
	}

}
