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

namespace MediaWiki\Extension\RobloxAPI\Tests\Integration;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentParserContext;
use MediaWiki\Extension\RobloxAPI\Args\ArgumentParserResult;
use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Args\Types\BooleanArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\IdArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\UsernameArgument;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils;
use MediaWiki\Language\Language;
use MediaWikiIntegrationTestCase;
use StatusValue;
use Wikimedia\Message\MessageValue;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Args\ArgumentParser
 * @group RobloxAPI
 */
class ArgumentParserTest extends MediaWikiIntegrationTestCase {
	use ParserDependentTestTrait;

	private function getArgumentParser(): TestingAccessWrapper {
		return TestingAccessWrapper::newFromObject(
			$this->getServiceContainer()->getService( 'RobloxAPI.ArgumentParser' )
		);
	}

	/**
	 * @dataProvider provideValidParseTests
	 */
	public function testParseValidArgs(
		ArgumentSpecification $specification,
		array $args,
		array $expectedRequired,
		array $expectedOptional
	) {
		$parser = $this->getArgumentParser();
		$parsingResult = $parser->parse(
			$specification,
			$args
		);
		$this->assertStatusGood( $parsingResult );
		$this->assertEquals( $expectedRequired, $parsingResult->getValue()->getRequiredArgs() );
		$this->assertEquals( $expectedOptional, $parsingResult->getValue()->getOptionalArgs() );
	}

	public static function provideValidParseTests(): array {
		return [
			[
				ArgumentSpecification::for( new UsernameArgument() )
					->withOptionalArg( 'pretty', new BooleanArgument() ),
				[
					'validUsername',
					'pretty=true'
				],
				[
					'validUsername'
				],
				[
					'pretty' => 'true'
				]
			],
			[
				ArgumentSpecification::for( new UsernameArgument(), IdArgument::group() )
					->withOptionalArg( 'pretty', new BooleanArgument() )
					->withJsonArgs(),
				[
					'validUsername',
					'12345',
					'pretty=false',
					'json_key=value->2'
				],
				[
					'validUsername',
					'12345'
				],
				[
					'pretty' => 'false',
					'json_key' => [ 'value', 2 ]
				]
			],
			[
				ArgumentSpecification::for(),
				[],
				[],
				[]
			]
		];
	}

	/**
	 * @dataProvider provideInvalidParseTests
	 */
	public function testParseInvalidArgs(
		ArgumentSpecification $specification,
		array $args,
		string $expectedError
	) {
		$parser = $this->getArgumentParser();
		$parsingResult = $parser->parse(
			$specification,
			$args
		);
		$this->assertStatusError( $expectedError, $parsingResult );
	}

	public static function provideInvalidParseTests(): array {
		return [
			[
				ArgumentSpecification::for( new UsernameArgument() ),
				[],
				'robloxapi-error-missing-argument'
			],
			[
				ArgumentSpecification::for( new UsernameArgument() )
					->withOptionalArg( 'pretty', new BooleanArgument() ),
				[
					'validUsername',
					'pretty=notaboolean'
				],
				'robloxapi-error-invalid-choice-argument'
			],
			[
				ArgumentSpecification::for( new UsernameArgument() )
					->withOptionalArg( 'pretty', new BooleanArgument() ),
				[
					'validUsername',
					'extraarg'
				],
				'robloxapi-error-too-many-required-args'
			],
			[
				ArgumentSpecification::for( new UsernameArgument(), IdArgument::group() )
					->withOptionalArg( 'pretty', new BooleanArgument() )
					->withJsonArgs(),
				[
					'validUsername',
					'notaninteger',
					'pretty=false',
					'json_key=value->2'
				],
				'robloxapi-error-invalid-generic-argument'
			],
		];
	}

	/**
	 * @dataProvider provideValidRequiredArgsTests
	 */
	public function testExtractValidRequiredArgs(
		ArgumentSpecification $specification,
		array $args,
		array $expected
	) {
		$clonedArgs = $args;
		$parser = $this->getArgumentParser();
		$ctx = $parser->newContext();
		$extractionResult = $parser->object->extractRequiredArgs(
			$ctx,
			$specification,
			$clonedArgs
		);
		$this->assertStatusGood( $extractionResult );
		$this->assertEquals( $expected, $extractionResult->getValue() );
	}

	public static function provideValidRequiredArgsTests(): array {
		return [
			[
				ArgumentSpecification::for( new UsernameArgument() ),
				[
					'validUsername'
				],
				[
					'validUsername'
				]
			],
			[
				ArgumentSpecification::for(
					new UsernameArgument(),
					IdArgument::group()
				)->withJsonArgs(),
				[
					'validUsername',
					'12345'
				],
				[
					'validUsername',
					'12345'
				]
			],
		];
	}

	/**
	 * @dataProvider provideInvalidRequiredArgsTests
	 */
	public function testExtractInvalidRequiredArgs(
		ArgumentSpecification $specification,
		array $args,
		string $expectedError
	) {
		$parser = $this->getArgumentParser();
		$ctx = $parser->newContext();
		$extractionResult = $parser->object->extractRequiredArgs(
			$ctx,
			$specification,
			$args
		);

		$this->assertStatusError( $expectedError, $extractionResult );
	}

	public static function provideInvalidRequiredArgsTests(): array {
		return [
			[
				ArgumentSpecification::for( new UsernameArgument() ),
				[],
				'robloxapi-error-missing-argument'
			],
			[
				ArgumentSpecification::for( new UsernameArgument() ),
				[
					'Invalid@Username!'
				],
				'robloxapi-error-invalid-generic-argument'
			],
			[
				ArgumentSpecification::for(
					new UsernameArgument(),
					IdArgument::group()
				)->withJsonArgs(),
				[
					'validUsername',
					'notaninteger'
				],
				'robloxapi-error-invalid-generic-argument'
			],
		];
	}

	/**
	 * @dataProvider provideValidOptionalArgsTests
	 */
	public function testExtractValidOptionalArgs(
		ArgumentSpecification $specification,
		array $args,
		array $expected
	) {
		$parser = $this->getArgumentParser();
		$ctx = $parser->newContext();
		$extractionResult = $parser->extractOptionalArgs(
			$ctx,
			$specification,
			$args
		);

		$this->assertStatusGood( $extractionResult );
		$this->assertEquals( $expected, $extractionResult->getValue() );
	}

	public static function provideValidOptionalArgsTests(): array {
		return [
			[
				ArgumentSpecification::for()->withOptionalArg( 'test', new BooleanArgument() ),
				[
					'test=true'
				],
				[
					'test' => 'true'
				]
			],
			[
				ArgumentSpecification::for( new UsernameArgument() )->withOptionalArg( 'test', new BooleanArgument() ),
				[
					'test=true'
				],
				[
					'test' => 'true'
				]
			],
			[
				ArgumentSpecification::for()->withOptionalArg( 'test', new BooleanArgument() )->withJsonArgs(),
				[
					'pretty=TrUE',
					'test=false',
					'json_key=value->2'
				],
				[
					'pretty' => 'true',
					'test' => 'false',
					'json_key' => [ 'value', 2 ]
				]
			],
			[
				ArgumentSpecification::for()->withOptionalArg( 'test', new BooleanArgument() )->withJsonArgs(),
				[],
				[]
			],
			[
				ArgumentSpecification::for(),
				[],
				[]
			]
		];
	}

	/**
	 * @dataProvider provideInvalidOptionalArgsTests
	 */
	public function testExtractInvalidOptionalArgs(
		ArgumentSpecification $specification,
		array $args,
		string $expectedError
	) {
		$parser = $this->getArgumentParser();
		$ctx = $parser->newContext();
		$extractionResult = $parser->extractOptionalArgs(
			$ctx,
			$specification,
			$args
		);

		$this->assertStatusError( $expectedError, $extractionResult );
	}

	public static function provideInvalidOptionalArgsTests(): array {
		return [
			[
				ArgumentSpecification::for()->withOptionalArg( 'test', new BooleanArgument() ),
				[
					'test=notaboolean'
				],
				'robloxapi-error-invalid-choice-argument'
			],
			[
				ArgumentSpecification::for()->withOptionalArg( 'test', new BooleanArgument() ),
				[
					'invalidarg=true'
				],
				'robloxapi-error-unknown-optional-argument'
			],
			[
				ArgumentSpecification::for()->withOptionalArg( 'test', new BooleanArgument() ),
				[
					'test'
				],
				'robloxapi-error-too-many-required-args'
			],
			[
				ArgumentSpecification::for()->withOptionalArg( 'test', new BooleanArgument() ),
				[
					'test=true',
					'extraarg'
				],
				'robloxapi-error-missing-optional-argument-value'
			],
		];
	}

	public function testValidateAllowedValue() {
		$this->overrideConfigValue(
			RobloxAPIConstants::ConfAllowedArguments,
			[
				'username' => [ 'allowedUsername' ]
			]
		);

		$this->resetServices();
		$parser = $this->getArgumentParser();
		$validationStatus = $parser->validate(
			new UsernameArgument(),
			$parser->newContext(),
			'allowedUsername'
		);
		$this->assertTrue( $validationStatus->isOK() );
	}

	public function testValidateDisallowedValue() {
		$this->overrideConfigValue(
			RobloxAPIConstants::ConfAllowedArguments,
			[
				'username' => [ 'allowedUsername' ]
			]
		);

		$this->resetServices();
		$parser = $this->getArgumentParser();
		$validationStatus = $parser->validate(
			new UsernameArgument(),
			$parser->newContext(),
			'disallowedUsername'
		);
		$this->assertStatusError( 'robloxapi-error-arg-not-allowed', $validationStatus );
	}

	public function testValidateInvalidValue() {
		$parser = $this->getArgumentParser();
		$validationStatus = $parser->validate(
			new UsernameArgument(),
			$parser->newContext(),
			'Invalid@Username!'
		);
		$this->assertStatusError( 'robloxapi-error-invalid-generic-argument', $validationStatus );
	}

	public function testValidateError() {
		$parser = $this->getArgumentParser();
		$utils = $this->getServiceContainer()->getService( 'RobloxAPI.Utils' );
		/** @var $utils RobloxAPIUtils */
		$error = $utils->formatStatusValue(
			StatusValue::newFatal(
				'robloxapi-error-invalid-generic-argument',
				'badvalue',
				new MessageValue( 'robloxapi-arg-type-username' )
			),
			$this->createParser()
		);

		$validationStatus = $parser->validate( new UsernameArgument(), $parser->newContext(), $error );
		$this->assertStatusError( 'robloxapi-error-passed-error-value', $validationStatus );
	}

	public function testGetAllowedArguments() {
		$this->overrideConfigValue(
			RobloxAPIConstants::ConfAllowedArguments,
			[
				'username' => [ 'allowedUsername' ]
			]
		);

		$this->resetServices();
		$parser = $this->getArgumentParser();
		$allowedUsernameArgs = $parser->getAllowedArguments(
			new UsernameArgument()
		);
		$this->assertCount( 1, $allowedUsernameArgs );
		$this->assertEquals( 'allowedUsername', $allowedUsernameArgs[0] );

		$allowedGroupIdArgs = $parser->getAllowedArguments( IdArgument::group() );
		$this->assertCount( 0, $allowedGroupIdArgs );
	}

	public function testNewContext() {
		$parser = $this->getArgumentParser();
		$context = $parser->newContext();

		$this->assertNotNull( $context );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Args\ArgumentParserContext
	 */
	public function testArgumentParserContext() {
		$contentLanguage = $this->createMock( Language::class );
		$context = new ArgumentParserContext( $contentLanguage );
		$this->assertSame( $contentLanguage, $context->getContentLanguage() );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\Args\ArgumentParserResult
	 */
	public function testArgumentParserResult() {
		$requiredArgs = [ 'arg1', 'arg2' ];
		$optionalArgs = [ 'opt1' => 'value1', 'opt2' => 'value2' ];
		$result = new ArgumentParserResult( $requiredArgs, $optionalArgs );
		$this->assertEquals( $requiredArgs, $result->getRequiredArgs() );
		$this->assertEquals( $optionalArgs, $result->getOptionalArgs() );
	}

}
