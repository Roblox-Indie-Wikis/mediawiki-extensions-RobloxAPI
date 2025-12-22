<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Tests\Integration;

use MediaWiki\Extension\RobloxAPI\Data\Source\IDataSource;
use MediaWiki\Extension\RobloxAPI\Hooks;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWikiIntegrationTestCase;
use Wikimedia\TestingAccessWrapper;

/**
 * @group RobloxAPI
 * @covers \MediaWiki\Extension\RobloxAPI\Hooks
 */
class HooksTest extends MediaWikiIntegrationTestCase {
	use ParserDependentTestTrait;

	private function createHooks(): Hooks {
		return new Hooks(
			$this->getServiceContainer()->getConfigFactory(),
			$this->getServiceContainer()->get( 'RobloxAPI.ArgumentParser' ),
			$this->getServiceContainer()->get( 'RobloxAPI.DataSourceProvider' ),
			$this->getServiceContainer()->get( 'RobloxAPI.Utils' ),
		);
	}

	public function testCanUseDisabledDataSource() {
		$hooks = TestingAccessWrapper::newFromObject( $this->createHooks() );

		$dataSourceMock = $this->createMock( IDataSource::class );
		$dataSourceMock->method( 'isEnabled' )->willReturn( false );

		$result = $hooks->canUseDataSource( $this->createParser(), $dataSourceMock );
		$this->assertStatusError( 'robloxapi-error-datasource-disabled', $result );
	}

	public function testCanUseUnlimitedDataSource() {
		$hooks = TestingAccessWrapper::newFromObject( $this->createHooks() );

		$dataSourceMock = $this->createMock( IDataSource::class );
		$dataSourceMock->method( 'isEnabled' )->willReturn( true );
		$dataSourceMock->method( 'getFetcherSourceId' )->willReturn( 'unlimitedsource' );

		$result = $hooks->canUseDataSource( $this->createParser(), $dataSourceMock );
		$this->assertStatusGood( $result );
	}

	public function testCanUseLimitedDataSource() {
		$this->overrideConfigValue(
			RobloxAPIConstants::ConfDataSourceUsageLimits,
			[ 'limitedsource' => 1 ]
		);
		$this->resetServices();
		$hooks = TestingAccessWrapper::newFromObject( $this->createHooks() );

		$dataSourceMock = $this->createMock( IDataSource::class );
		$dataSourceMock->method( 'isEnabled' )->willReturn( true );
		$dataSourceMock->method( 'getId' )->willReturn( 'limitedsource' );
		$dataSourceMock->method( 'getFetcherSourceId' )->willReturn( 'limitedsource' );

		$parser = $this->createParser();
		$result = $hooks->canUseDataSource( $parser, $dataSourceMock );
		$this->assertStatusGood( $result );
		$result = $hooks->canUseDataSource( $parser, $dataSourceMock );
		$this->assertStatusError( 'robloxapi-error-usage-limit', $result );
	}

	public function testCanUseDependentLimitedDataSource() {
		$this->overrideConfigValue(
			RobloxAPIConstants::ConfDataSourceUsageLimits,
			[ 'dependentsource-dependency' => 1 ]
		);
		$this->resetServices();
		$hooks = TestingAccessWrapper::newFromObject( $this->createHooks() );

		$dataSourceMock = $this->createMock( IDataSource::class );
		$dataSourceMock->method( 'isEnabled' )->willReturn( true );
		$dataSourceMock->method( 'getId' )->willReturn( 'dependentsource' );
		$dataSourceMock->method( 'getFetcherSourceId' )->willReturn( 'dependentsource-dependency' );

		$parser = $this->createParser();
		$result = $hooks->canUseDataSource( $parser, $dataSourceMock );
		$this->assertStatusGood( $result );
		$result = $hooks->canUseDataSource( $parser, $dataSourceMock );
		$this->assertStatusError( 'robloxapi-error-usage-limit-dependent', $result );
	}

}
