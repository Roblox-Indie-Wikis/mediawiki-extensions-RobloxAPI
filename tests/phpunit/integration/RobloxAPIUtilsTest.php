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

use FormatJson;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIException;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use StatusValue;
use Wikimedia\Message\MessageValue;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils
 * @group RobloxAPI
 */
class RobloxAPIUtilsTest extends MediaWikiIntegrationTestCase {

	protected function getUtils(): RobloxAPIUtils {
		return $this->getServiceContainer()->getService( 'RobloxAPI.Utils' );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::createJsonResult
	 */
	public function testCreateJsonResult(): void {
		$utils = $this->getUtils();
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
		$jsonObject = FormatJson::decode( $jsonString );
		self::assertEquals( 'abaddriverlol',
			$utils->createJsonResult( $jsonObject, [ 'json_key' => [ 'requestedUsername' ] ] ) );
		self::assertEquals( '{"requestedUsername":"abaddriverlol","hasVerifiedBadge":false,"id":4182456156,' .
			'"name":"abaddriverlol","displayName":"abaddriverlol"}',
			$utils->createJsonResult( $jsonObject, [] ) );

		// test non-existent key
		self::assertEquals( 'null', $utils->createJsonResult( $jsonObject, [ 'json_key' => [ 'doesnotexist' ] ] ) );

		// test invalid key path
		self::assertEquals( 'null',
			$utils->createJsonResult( $jsonObject, [ 'json_key' => [ 'doesnotexist' ] ] ) );

		// test keys pointing to non-objects
		self::assertEquals( 'null',
			$utils->createJsonResult( $jsonObject, [ 'json_key' => [ 'requestedUsername', 'id' ] ] ) );

		// test array index
		$jsonString = /** @lang JSON */
			<<<EOD
				{
					"someData": [
						"someValue"
					]
				}
		EOD;
		$jsonObject = FormatJson::decode( $jsonString );
		self::assertEquals( 'someValue',
			$utils->createJsonResult( $jsonObject, [ 'json_key' => [ 'someData', 0 ] ] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::getJsonKey
	 */
	public function testGetJsonKey(): void {
		$utils = $this->getUtils();
		$jsonString = /** @lang JSON */
			<<<EOD
				{
					"someData": {
						"someNestedData": "someValue"
					},
					"someArray": [
						"firstValue",
						"secondValue"
					]
				}
		EOD;
		$jsonObject = FormatJson::decode( $jsonString );
		self::assertEquals( 'someValue', $utils->getJsonKey( $jsonObject, [ 'someData', 'someNestedData' ] ) );

		self::assertNull( $utils->getJsonKey( 'string', [ 'key' ] ) );
		self::assertNull( $utils->getJsonKey( $jsonObject, [ 'someArray', 3 ] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::verifyIsRobloxCdnUrl
	 */
	public function testVerifyIsRobloxCdnUrl(): void {
		$utils = $this->getUtils();

		self::assertTrue( $utils->verifyIsRobloxCdnUrl( 'https://tr.rbxcd' .
			'n.com/30DAY-Avatar-7B1E1A9240F5DE0598D6FD97DBC8859F-Png/140/140/Avatar/Png/noFilter.png' ) );
		self::assertTrue( $utils->verifyIsRobloxCdnUrl( 'https://tr.rbxcdn.co' .
			'm/30DAY-Avatar-7B1E1A9240F5DE0598D6FD97DBC8859F-Png/140/140/Avatar/Png/noFilter.webp' ) );

		self::assertFalse( $utils->verifyIsRobloxCdnUrl( 'https://roblox.com/1234/' ) );
		self::assertFalse( $utils->verifyIsRobloxCdnUrl( 'https://t0.rbxcdn.com///https://google.com/' ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::transformValueForError
	 */
	public function testTransformValueForError(): void {
		$utils = $this->getUtils();

		self::assertEquals( 'simpleString', $utils->transformValueForError( 'simpleString' ) );
		self::assertEquals( new MessageValue( 'robloxapi-arg-empty-value-placeholder' ),
			$utils->transformValueForError( '' ) );
		self::assertEquals( '&#32; ', $utils->transformValueForError( '  ' ) );
		self::assertEquals( '&#60;test&#62;', $utils->transformValueForError( '<test>' ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::shouldReturnJson
	 */
	public function testShouldReturnJson(): void {
		$utils = $this->getUtils();

		self::assertTrue( $utils->shouldReturnJson( (object)[ 'key' => 'value' ] ) );
		self::assertTrue( $utils->shouldReturnJson( [ 'key' => 'value' ] ) );
		self::assertFalse( $utils->shouldReturnJson( 'just a string' ) );
		self::assertFalse( $utils->shouldReturnJson( 12345 ) );
		self::assertFalse( $utils->shouldReturnJson( null ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils::formatStatusValue
	 */
	public function testFormatStatusValue(): void {
		$utils = $this->getUtils();
		$parser = $this->getServiceContainer()->getParserFactory()->create();
		$lang = $this->getServiceContainer()->getLanguageFactory()->getLanguage( 'en' );
		$parserOptions = ParserOptions::newFromAnon();
		$parserOptions->setTargetLanguage( $lang );
		$parser->setOptions( $parserOptions );
		$parser->setPage( Title::newFromText( 'RobloxAPITest' ) );

		$statusFatal = StatusValue::newFatal(
			'robloxapi-error-invalid-generic-argument',
			'<invalid&value>',
			new MessageValue( 'robloxapi-arg-type-username' )
		);
		$formatted = $utils->formatStatusValue( $statusFatal, $parser );
		// phpcs:ignore Generic.Files.LineLength
		self::assertStringContainsString( '<span class="cdx-message__icon"></span><div class="cdx-message__content">Invalid value <code><invalid&value></code> for argument of type \'\'Username\'\'!</div></div>', $formatted );
		self::assertStringContainsString( 'mw-robloxapi-error', $formatted );

		$utils->overrideOptions( [
			RobloxAPIConstants::ConfShowPlainErrors => true,
		] );
		$formatted = $utils->formatStatusValue( $statusFatal, $parser );
		self::assertEquals( 'Invalid value <code><invalid&value></code> for argument of type \'\'Username\'\'!',
			$formatted );

		$statusGood = StatusValue::newGood();
		$this->expectException( \LogicException::class );
		$utils->formatStatusValue( $statusGood, $parser );
	}

}
