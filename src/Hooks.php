<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI;

use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\data\source\IDataSource;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIConstants;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\ParserTestGlobalsHook;
use MediaWiki\Parser\Parser;

class Hooks implements ParserFirstCallInitHook, ParserTestGlobalsHook {

	private Config $config;
	/**
	 * @var array<string, int>
	 */
	private array $usageLimits;

	public function __construct(
		ConfigFactory $configFactory,
		private readonly DataSourceProvider $dataSourceProvider
	) {
		$this->config = $configFactory->makeConfig( 'RobloxAPI' );

		$this->usageLimits = $this->config->get( RobloxAPIConstants::ConfDataSourceUsageLimits );
	}

	/**
	 * @inheritDoc
	 */
	public function onParserFirstCallInit( $parser ): void {
		$parser->setFunctionHook( 'robloxapi', function ( Parser $parser, mixed ...$args ): array|bool|string {
			try {
				return $this->handleParserFunctionCall( $parser, $args );
			} catch ( RobloxAPIException $exception ) {
				$parser->addTrackingCategory( 'robloxapi-category-error' );
				return RobloxAPIUtil::formatException( $exception, $parser, $this->config );
			}
		} );

		if ( $this->config->get( RobloxAPIConstants::ConfRegisterLegacyParserFunctions ) ) {
			$legacyParserFunctions = $this->dataSourceProvider->createLegacyParserFunctions();

			foreach ( $legacyParserFunctions as $id => $function ) {
				// all data source parser functions are only enabled if the corresponding data source
				// is enabled, so we don't need to check the config for that
				$parser->setFunctionHook(
					$id,
					function ( Parser $parser, mixed ...$args ) use ( $function ): array|bool|string {
						$parser->addTrackingCategory( 'robloxapi-category-deprecated-parser-function' );
						if ( $this->config->get( RobloxAPIConstants::ConfParserFunctionsExpensive ) &&
							!$parser->incrementExpensiveFunctionCount() ) {
							return false;
						}
						$this->checkCanUseDataSource( $parser, $function->getDataSource() );

						try {
							$result = $function->exec( $this->dataSourceProvider, $parser, ...$args );

							$shouldEscape = $function->shouldEscapeResult( $result );

							if ( RobloxAPIUtil::shouldReturnJson( $result ) ) {
								$result = RobloxAPIUtil::createJsonResult( $result, [] );
								// always escape json, there is no need for it to be parsed
								$shouldEscape = true;
							}

							return [
								$result,
								'nowiki' => $shouldEscape,
							];
						} catch ( RobloxAPIException $exception ) {
							$parser->addTrackingCategory( 'robloxapi-category-error' );
							return RobloxAPIUtil::formatException( $exception, $parser, $this->config );
						}
					}
				);
			}
		}
	}

	/**
	 * Handles a call to the #robloxAPI parser function.
	 * @param Parser $parser
	 * @param string[] $args
	 * @throws RobloxAPIException
	 */
	private function handleParserFunctionCall( Parser $parser, array $args ): array|bool {
		if ( $this->config->get( RobloxAPIConstants::ConfParserFunctionsExpensive ) &&
			!$parser->incrementExpensiveFunctionCount() ) {
			return false;
		}

		if ( count( $args ) === 0 ) {
			throw new RobloxAPIException( 'robloxapi-error-no-arguments' );
		}
		$dataSourceId = $args[0];
		$dataSource = $this->dataSourceProvider->getDataSource( $dataSourceId, true );

		if ( !$dataSource ) {
			throw new RobloxAPIException( 'robloxapi-error-datasource-not-found', $dataSourceId );
		}

		$this->checkCanUseDataSource( $parser, $dataSource );

		$otherArgs = array_slice( $args, 1 );

		$argumentSpecification = $dataSource->getArgumentSpecification();

		[ $requiredArgs, $optionalArgs ] =
			RobloxAPIUtil::parseArguments( $argumentSpecification, $otherArgs, $this->config );

		$result = $dataSource->exec( $this->dataSourceProvider, $parser, $requiredArgs, $optionalArgs );
		$shouldEscape = $dataSource->shouldEscapeResult( $result );

		if ( RobloxAPIUtil::shouldReturnJson( $result ) ) {
			$result = RobloxAPIUtil::createJsonResult( $result, $optionalArgs );
			// always escape json, there is no need for it to be parsed
			$shouldEscape = true;
		}

		return [
			$result,
			'nowiki' => $shouldEscape,
		];
	}

	/**
	 * @throws RobloxAPIException if the usage limit of the data source is exceeded
	 */
	private function checkCanUseDataSource( Parser $parser, IDataSource $dataSource ): void {
		$dataSourceId = $dataSource->getFetcherSourceId();
		if ( !array_key_exists( $dataSourceId, $this->usageLimits ) ) {
			// no limit
			return;
		}

		$output = $parser->getOutput();
		$extensionData = $output->getExtensionData( RobloxAPIConstants::ExtensionDataKey ) ?? [];

		if ( !array_key_exists( $dataSourceId, $extensionData ) ) {
			$extensionData[$dataSourceId] = 0;
		}

		$used = $extensionData[$dataSourceId] + 1;
		$limit = $this->usageLimits[ $dataSourceId ];

		$extensionData[$dataSourceId] = $used;
		$parser->getOutput()->setExtensionData( RobloxAPIConstants::ExtensionDataKey, $extensionData );

		if ( $used > $limit ) {
			if ( $dataSource->getFetcherSourceId() !== $dataSource->getId() ) {
				throw new RobloxAPIException( 'robloxapi-error-usage-limit-dependent', $dataSourceId, $limit,
					$dataSource->getId() );
			} else {
				throw new RobloxAPIException( 'robloxapi-error-usage-limit', $dataSourceId, $limit );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onParserTestGlobals( &$globals ): void {
		$globals += [
			'wgRobloxAPIAllowedArguments' => [ 'UserID' => [ 54321 ] ],
			// show errors as plain text to make parser tests not depend on changes in Html:errorBox
			'wgRobloxAPIShowPlainErrors' => true,
		];
	}
}
