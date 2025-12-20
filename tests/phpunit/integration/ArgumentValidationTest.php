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
use MediaWiki\Extension\RobloxAPI\Args\Types\IArgument;
use MediaWiki\Language\Language;
use MediaWikiIntegrationTestCase;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Args\Types\BooleanArgument
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

	public static function provideArgumentValidationSuccessTests(): array {
		return [
			[ new BooleanArgument(), 'true', 'true' ],
			[ new BooleanArgument(), 'TrUe', 'true' ],
			[ new BooleanArgument(), 'false', 'false' ],
			[ new BooleanArgument(), 'FalSe', 'false' ],
		];
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

	public static function provideArgumentValidationFailureTests(): array {
		return [
			[ new BooleanArgument(), 'test', 'robloxapi-error-invalid-choice-argument' ]
		];
	}

	private function createContext(): ArgumentParserContext {
		return new ArgumentParserContext(
			$this->createMock( Language::class )
		);
	}

}
