<?php
/**
 * @license GPL-2.0-or-later
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
