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

namespace MediaWiki\Extension\RobloxAPI\Tests\Unit;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Args\Types\BooleanArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\JsonKeyArgument;
use MediaWikiUnitTestCase;

/**
 * @group RobloxAPI
 * @covers \MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification
 */
class ArgumentSpecificationTest extends MediaWikiUnitTestCase {

	public function testConstruct() {
		$requiredArgs = [ new BooleanArgument() ];
		$optionalArgs = [ 'test' => new BooleanArgument() ];
		$argSpec = new ArgumentSpecification( $requiredArgs, $optionalArgs );
		$this->assertSame( $requiredArgs, $argSpec->getRequiredArgs() );
		$this->assertSame( $optionalArgs, $argSpec->getOptionalArgs() );
	}

	public function testConstructWithJsonArgs() {
		$requiredArgs = [ new BooleanArgument() ];
		$optionalArgs = [ 'test' => new BooleanArgument() ];
		$argSpec = new ArgumentSpecification( $requiredArgs, $optionalArgs, true );
		$this->assertSame( $requiredArgs, $argSpec->getRequiredArgs() );
		$this->assertInstanceOf( JsonKeyArgument::class, $argSpec->getOptionalArgs()['json_key'] );
		$this->assertInstanceOf( BooleanArgument::class, $argSpec->getOptionalArgs()['pretty'] );
	}

	public function testWithMethods() {
		$argSpec = new ArgumentSpecification( [], [] );
		$this->assertSame( $argSpec, $argSpec->withJsonArgs() );
		$this->assertArrayHasKey( 'json_key', $argSpec->getOptionalArgs() );
		$this->assertCount( 0, $argSpec->getRequiredArgs() );
		$this->assertCount( 2, $argSpec->getOptionalArgs() );

		$argSpec->withRequiredArg( new BooleanArgument() );
		$this->assertCount( 1, $argSpec->getRequiredArgs() );
		$argSpec->withOptionalArg( 'test', new BooleanArgument() );
		$this->assertCount( 3, $argSpec->getOptionalArgs() );
	}

}
