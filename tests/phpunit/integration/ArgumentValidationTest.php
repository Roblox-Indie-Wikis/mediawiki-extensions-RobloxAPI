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

use MediaWiki\Extension\RobloxAPI\Args\ArgumentParserContext;
use MediaWiki\Extension\RobloxAPI\Args\Types\BooleanArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\ChoiceArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\IArgument;
use MediaWiki\Language\Language;
use MediaWikiIntegrationTestCase;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Args\Types\BooleanArgument
 * @covers \MediaWiki\Extension\RobloxAPI\Args\Types\ChoiceArgument
 *
 * @group RobloxAPI
 */
class ArgumentValidationTest extends MediaWikiIntegrationTestCase {

	/**
	 * @dataProvider provideArgumentValidationSuccessTests
	 */
	public function testArgumentValidationSuccess(
		IArgument $argument,
		string $value,
		mixed $expected,
	) {
		$status = $argument->validate( $this->createContext(), $value );
		$this->assertStatusGood( $status );
		$this->assertStatusValue( $expected, $status );
	}

	/**
	 * @dataProvider provideArgumentValidationFailureTests
	 */
	public function testArgumentValidationFailure(
		IArgument $argument,
		string $value,
		string $expectedErrorMessage,
	) {
		$status = $argument->validate( $this->createContext(), $value );
		$this->assertStatusError( $expectedErrorMessage, $status );
	}

	// phpcs:disable Generic.Files.LineLength
	public static function provideArgumentValidationSuccessTests(): array {
		return [
			[ new BooleanArgument(), 'true', 'true' ],
			[ new BooleanArgument(), 'TrUe', 'true' ],
			[ new BooleanArgument(), 'false', 'false' ],
			[ new BooleanArgument(), 'FalSe', 'false' ],

			[ new ChoiceArgument( 'test', [ 'val1', 'val2' ] ), 'val1', 'val1' ],
			[ new ChoiceArgument( 'test', [ 'val1', 'val2' ] ), 'val2', 'val2' ],
			[ new ChoiceArgument( 'test', [ 'val1', 'val2' ], caseSensitive: false ), 'VAL1', 'val1' ],
			[ new ChoiceArgument( 'test', [ 'VAL1', 'VAL2' ], caseSensitive: false ), 'val1', 'VAL1' ],
		];
	}

	public static function provideArgumentValidationFailureTests(): array {
		return [
			[ new BooleanArgument(), 'test', 'robloxapi-error-invalid-choice-argument' ],
			[ new BooleanArgument(), '', 'robloxapi-error-invalid-choice-argument' ],

			[ new ChoiceArgument( 'test', [ 'val1', 'val2' ] ), 'VAL1', 'robloxapi-error-invalid-choice-argument' ],
			[ new ChoiceArgument( 'test', [ 'val1', 'val2' ] ), 'val3', 'robloxapi-error-invalid-choice-argument' ],
			[ new ChoiceArgument( 'test', [ 'val1', 'val2' ], caseSensitive: false ), 'val3', 'robloxapi-error-invalid-choice-argument' ],
			[ new ChoiceArgument( 'test', [ 'val1', 'val2' ], errorMessage: 'test-custom-error' ), 'val3', 'test-custom-error' ],
		];
	}

	// phpcs:enable Generic.Files.LineLength

	private function createContext(): ArgumentParserContext {
		return new ArgumentParserContext(
			$this->createMock( Language::class )
		);
	}

}
