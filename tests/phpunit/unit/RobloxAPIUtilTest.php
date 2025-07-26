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

use HashConfig;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;
use MediaWiki\Utils\UrlUtils;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil
 * @group RobloxAPI
 */
class RobloxAPIUtilTest extends \MediaWikiUnitTestCase {

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::isValidId
	 */
	public function testIsValidId(): void {
		self::assertFalse( RobloxAPIUtil::isValidId( null ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "" ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "a" ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "2412a4214" ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "309713598a" ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "4848492840912840912840921842019481" ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "-1234" ) );

		self::assertTrue( RobloxAPIUtil::isValidId( "1" ) );
		self::assertTrue( RobloxAPIUtil::isValidId( "4182456156" ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::areValidIds
	 */
	public function testAreValidIds(): void {
		self::assertFalse( RobloxAPIUtil::areValidIds( [ null ] ) );
		self::assertFalse( RobloxAPIUtil::areValidIds( [ "a" ] ) );
		self::assertFalse( RobloxAPIUtil::areValidIds( [ "123", "b" ] ) );

		self::assertTrue( RobloxAPIUtil::areValidIds( [] ) );
		self::assertTrue( RobloxAPIUtil::areValidIds( [ "12345" ] ) );
		self::assertTrue( RobloxAPIUtil::areValidIds( [ "23598", "12345" ] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::assertValidIds
	 */
	public function testAssertValidIds(): void {
		$this->expectException( RobloxAPIException::class );
		RobloxAPIUtil::assertValidIds( "abc" );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::assertArgAllowed
	 */
	public function testAssertArgsAllowed(): void {
		$config = new HashConfig( [
			'RobloxAPIAllowedArguments' => [
				'UserID' => [ '123454321' ],
				'GroupID' => [],
			],
		] );

		RobloxAPIUtil::assertArgAllowed( $config, 'UserID', '123454321' );
		RobloxAPIUtil::assertArgAllowed( $config, 'GroupID', '14981124' );
		RobloxAPIUtil::assertArgAllowed( $config, 'GroupID', '512512312' );
		RobloxAPIUtil::assertArgAllowed( $config, 'GroupID', '901480124' );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-arg-not-allowed' );
		RobloxAPIUtil::assertArgAllowed( $config, 'UserID', '54321' );
		RobloxAPIUtil::assertArgAllowed( $config, 'UserID', '12345' );
		RobloxAPIUtil::assertArgAllowed( $config, 'GroupID', '54321' );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::assertValidArg
	 */
	public function testAssertValidArgs(): void {
		RobloxAPIUtil::assertValidArg( 'UserID', '123454321' );
		RobloxAPIUtil::assertValidArg( 'ThumbnailSize', '140x140' );
		RobloxAPIUtil::assertValidArg( 'Username', 'builderman_123' );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-thumbnail-size' );
		RobloxAPIUtil::assertValidArg( 'ThumbnailSize', '12345' );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-username' );
		RobloxAPIUtil::assertValidArg( 'Username', '__invalidusername' );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::createJsonResult
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
			RobloxAPIUtil::createJsonResult( $jsonObject, [ 'json_key' => 'requestedUsername' ] ) );
		self::assertEquals( '{"requestedUsername":"abaddriverlol","hasVerifiedBadge":false,"id":4182456156,' .
			'"name":"abaddriverlol","displayName":"abaddriverlol"}',
			RobloxAPIUtil::createJsonResult( $jsonObject, [] ) );

		// test non-existent key
		self::assertEquals( 'null', RobloxAPIUtil::createJsonResult( $jsonObject, [ 'json_key' => 'doesnotexist' ] ) );

		// test invalid key path
		self::assertEquals( 'null',
			RobloxAPIUtil::createJsonResult( $jsonObject, [ 'json_key' => 'doesnotexist->->' ] ) );

		// test keys pointing to non-objects
		self::assertEquals( 'null',
			RobloxAPIUtil::createJsonResult( $jsonObject, [ 'json_key' => 'requestedUsername->id' ] ) );

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
			RobloxAPIUtil::createJsonResult( $jsonObject, [ 'json_key' => 'someData->0' ] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::getJsonKey
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
		self::assertEquals( 'someValue', RobloxAPIUtil::getJsonKey( $jsonObject, 'someData->someNestedData' ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::verifyIsRobloxCdnUrl
	 */
	public function testVerifyIsRobloxCdnUrl(): void {
		$urlUtils = new UrlUtils();

		self::assertTrue( RobloxAPIUtil::verifyIsRobloxCdnUrl( 'https://tr.rbxcd' .
			'n.com/30DAY-Avatar-7B1E1A9240F5DE0598D6FD97DBC8859F-Png/140/140/Avatar/Png/noFilter.png', $urlUtils ) );
		self::assertTrue( RobloxAPIUtil::verifyIsRobloxCdnUrl( 'https://tr.rbxcdn.co' .
			'm/30DAY-Avatar-7B1E1A9240F5DE0598D6FD97DBC8859F-Png/140/140/Avatar/Png/noFilter.webp', $urlUtils ) );

		self::assertFalse( RobloxAPIUtil::verifyIsRobloxCdnUrl( 'https://roblox.com/1234/', $urlUtils ) );
		self::assertFalse( RobloxAPIUtil::verifyIsRobloxCdnUrl( 'https://t0.rbxcdn.com///https://google.com/',
			$urlUtils ) );
	}

}
