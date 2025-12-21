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
use MediaWiki\Extension\RobloxAPI\Args\Types\AbstractArgument;
use MediaWikiIntegrationTestCase;
use StatusValue;
use Wikimedia\Message\ScalarParam;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Args\Types\AbstractArgument
 * @group RobloxAPI
 */
class AbstractArgumentTest extends MediaWikiIntegrationTestCase {

	private function createAbstractArgument(): TestingAccessWrapper {
		return TestingAccessWrapper::newFromObject( new class extends AbstractArgument {
			public function __construct() {
				parent::__construct( 'testargument' );
			}

			public function validate( ArgumentParserContext $ctx, string $value ): StatusValue {
				return StatusValue::newGood();
			}
		} );
	}

	public function testGetTranslationKey() {
		$argType = $this->createAbstractArgument();

		$this->assertSame(
			'robloxapi-arg-type-testargument',
			$argType->getTranslationKey()
		);
	}

	public function testGetKey() {
		$argType = $this->createAbstractArgument();

		$this->assertSame(
			'testargument',
			$argType->getKey()
		);
	}

	public function testInvalidValue() {
		$argType = $this->createAbstractArgument();
		$status = $argType->invalidValue( 'badvalue' );
		/** @var $status StatusValue */
		$this->assertStatusError( 'robloxapi-error-invalid-generic-argument', $status );
		$params = $status->getMessages()[0]->getParams();
		/** @var $params ScalarParam[] */
		$this->assertSame(
			'<text>badvalue</text>',
			$params[0]->dump()
		);
		$this->assertSame(
			'<text><message key="robloxapi-arg-type-testargument"></message></text>',
			$params[1]->dump()
		);
	}

	public function testEmptyInvalidValue() {
		$argType = $this->createAbstractArgument();
		$status = $argType->invalidValue( '' );
		/** @var $status StatusValue */
		$this->assertStatusError( 'robloxapi-error-invalid-generic-argument', $status );
		$params = $status->getMessages()[0]->getParams();
		/** @var $params ScalarParam[] */
		$this->assertSame(
			'<text><message key="robloxapi-arg-empty-value-placeholder"></message></text>',
			$params[0]->dump()
		);
		$this->assertSame(
			'<text><message key="robloxapi-arg-type-testargument"></message></text>',
			$params[1]->dump()
		);
	}

}
