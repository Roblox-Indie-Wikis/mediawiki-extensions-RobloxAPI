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

use LogicException;
use MediaWiki\Extension\RobloxAPI\Data\Source\AbstractDataSource;
use MediaWiki\Extension\RobloxAPI\Data\Source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\Data\Source\DependentDataSource;
use MediaWikiUnitTestCase;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\Data\Source\AbstractDataSource
 * @covers \MediaWiki\Extension\RobloxAPI\Data\Source\DependentDataSource
 * @group RobloxAPI
 */
class DataSourceTest extends MediaWikiUnitTestCase {

	public function testAbstractDefaults() {
		$abstractDataSource = $this->getMockForAbstractClass( AbstractDataSource::class, [ 'test' ] );
		$this->assertTrue( $abstractDataSource->shouldEscapeResult( '' ) );
		$this->assertFalse( $abstractDataSource->shouldRegisterLegacyParserFunction() );
	}

	public function testFailMethods() {
		$abstractDataSource = TestingAccessWrapper::newFromObject(
			$this->getMockForAbstractClass( AbstractDataSource::class, [ 'test' ] )
		);
		$this->assertStatusNotOK( $abstractDataSource->failNoData() );
		$this->assertStatusNotOK( $abstractDataSource->failUnexpectedDataStructure() );
		$this->assertStatusNotOK( $abstractDataSource->failInvalidData() );
	}

	public function testDisable() {
		$abstractDataSource = TestingAccessWrapper::newFromObject(
			$this->getMockForAbstractClass( AbstractDataSource::class, [ 'test' ] )
		);
		$this->assertTrue( $abstractDataSource->isEnabled() );
		$abstractDataSource->disable();
		$this->assertFalse( $abstractDataSource->isEnabled() );
	}

	public function testDependentDataSourceSuccess() {
		$mockDependency = $this->createMock( AbstractDataSource::class );
		$mockDependency->expects( $this->once() )
			->method( 'getFetcherSourceId' )
			->willReturn( 'testDependency' );
		$dataSourceProvider = $this->createMock( DataSourceProvider::class );
		$dataSourceProvider->expects( $this->once() )
			->method( 'getDataSource' )
			->with( 'testDependency' )
			->willReturn( $mockDependency );
		$dependentDataSource = $this->getMockForAbstractClass(
			DependentDataSource::class,
			[ $dataSourceProvider, 'test', 'testDependency' ]
		 );
		$this->assertSame( 'testDependency', $dependentDataSource->getFetcherSourceId() );

		$mockDependency->expects( $this->exactly( 2 ) )
			->method( 'isEnabled' )
			->willReturnOnConsecutiveCalls( true, false );

		$this->assertTrue( $dependentDataSource->isEnabled() );
		$this->assertFalse( $dependentDataSource->isEnabled() );
	}

	public function testDependentDataSourceFailure() {
		$dataSourceProvider = $this->createMock( DataSourceProvider::class );
		$dataSourceProvider->expects( $this->once() )
			->method( 'getDataSource' )
			->with( 'testDependency' )
			->willReturn( null );
		$this->expectException( LogicException::class );
		$this->getMockForAbstractClass(
			DependentDataSource::class,
			[ $dataSourceProvider, 'test', 'testDependency' ]
		);
	}

}
