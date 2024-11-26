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

namespace phpunit\unit;

use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil
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

}
