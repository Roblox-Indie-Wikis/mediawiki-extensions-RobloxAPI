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
use MediaWiki\Extension\RobloxAPI\Args\ArgumentParser;
use MediaWiki\Extension\RobloxAPI\Data\Source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\Data\Source\IDataSource;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIException;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\ParserTestGlobalsHook;
use MediaWiki\Parser\Parser;
use StatusValue;

class Hooks implements ParserFirstCallInitHook, ParserTestGlobalsHook {

	private Config $config;
	/**
	 * @var array<string, int>
	 */
	private array $usageLimits;

	public function __construct(
		ConfigFactory $configFactory,
		private readonly ArgumentParser $argumentParser,
		private readonly DataSourceProvider $dataSourceProvider,
		private readonly RobloxAPIUtils $utils,
	) {
		$this->config = $configFactory->makeConfig( 'RobloxAPI' );

		$this->usageLimits = $this->config->get( RobloxAPIConstants::ConfDataSourceUsageLimits );
	}

	/**
	 * @inheritDoc
	 */
	public function onParserFirstCallInit( $parser ): void {
		$parser->setFunctionHook( 'robloxapi', function ( Parser $parser, mixed ...$args ): array|bool|string {
			$status = $this->handleParserFunctionCall( $parser, $args );
			if ( $status->isGood() ) {
				return $status->value;
			} else {
				$parser->addTrackingCategory( 'robloxapi-category-error' );
				return $this->utils->formatStatusValue( $status, $parser );
			}
		} );

		if ( $this->config->get( RobloxAPIConstants::ConfRegisterLegacyParserFunctions ) ) {
			$legacyParserFunctions = [];
			foreach ( $this->dataSourceProvider->dataSources as $dataSource ) {
				// register parser function only if needed for legacy reasons
				if ( !$dataSource->shouldRegisterLegacyParserFunction() ) {
					continue;
				}

				$id = "roblox_" . ucfirst( $dataSource->getId() );

				// all data source parser functions are only enabled if the corresponding data source
				// is enabled, so we don't need to check the config for that
				$parser->setFunctionHook(
					$id,
					function ( Parser $parser, mixed ...$args ) use ( $dataSource ): array|bool|string {
						$parser->addTrackingCategory( 'robloxapi-category-deprecated-parser-function' );
						if ( $this->config->get( RobloxAPIConstants::ConfParserFunctionsExpensive ) &&
							!$parser->incrementExpensiveFunctionCount() ) {
							return false;
						}
						$canUse = $this->canUseDataSource( $parser, $dataSource );
						if ( !$canUse->isGood() ) {
							return $this->utils->formatStatusValue( $canUse, $parser );
						}

						try {
							$status = $this->argumentParser->parse( $dataSource->getArgumentSpecification(), $args );
							if ( !$status->isGood() ) {
								return $this->utils->formatStatusValue( $status, $parser );
							}
							$parseResult = $status->value;
							$result = $dataSource->exec(
								$parser,
								$parseResult->requiredArgs,
								$parseResult->optionalArgs
							);

							$shouldEscape = $dataSource->shouldEscapeResult( $result );

							if ( $this->utils->shouldReturnJson( $result ) ) {
								$result = $this->utils->createJsonResult( $result, [] );
								// always escape json, there is no need for it to be parsed
								$shouldEscape = true;
							}

							return [
								$result,
								'nowiki' => $shouldEscape,
							];
						} catch ( RobloxAPIException $exception ) {
							$parser->addTrackingCategory( 'robloxapi-category-error' );
							return $this->utils->formatStatusValue( $exception->toStatusValue(), $parser );
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
	 * @return StatusValue<array|bool>
	 */
	private function handleParserFunctionCall( Parser $parser, array $args ): StatusValue {
		if ( $this->config->get( RobloxAPIConstants::ConfParserFunctionsExpensive ) &&
			!$parser->incrementExpensiveFunctionCount() ) {
			// TODO suboptimal
			return StatusValue::newGood( false );
		}

		if ( count( $args ) === 0 ) {
			return StatusValue::newFatal( 'robloxapi-error-no-arguments' );
		}
		$dataSourceId = $args[0];
		$status = $this->dataSourceProvider->tryGetDataSource( $dataSourceId, true );
		if ( !$status->isGood() ) {
			return $status;
		}
		$dataSource = $status->value;

		$canUse = $this->canUseDataSource( $parser, $dataSource );
		if ( !$canUse->isGood() ) {
			return $canUse;
		}

		$otherArgs = array_slice( $args, 1 );

		$argumentSpecification = $dataSource->getArgumentSpecification();

		$status = $this->argumentParser->parse( $argumentSpecification, $otherArgs );
		if ( !$status->isGood() ) {
			return $status;
		}
		$parseResult = $status->value;

		// TODO use status
		try {
			$result = $dataSource->exec( $parser, $parseResult->requiredArgs, $parseResult->optionalArgs );
		} catch ( RobloxAPIException $exception ) {
			return $exception->toStatusValue();
		}
		$shouldEscape = $dataSource->shouldEscapeResult( $result );

		if ( $this->utils->shouldReturnJson( $result ) ) {
			$result = $this->utils->createJsonResult( $result, $parseResult->optionalArgs );
			// always escape json, there is no need for it to be parsed
			$shouldEscape = true;
		}

		return StatusValue::newGood( [
			$result,
			'nowiki' => $shouldEscape,
		] );
	}

	private function canUseDataSource( Parser $parser, IDataSource $dataSource ): StatusValue {
		if ( !$dataSource->isEnabled() ) {
			return StatusValue::newFatal( 'robloxapi-error-datasource-disabled', $dataSource->getId() );
		}

		$dataSourceId = $dataSource->getFetcherSourceId();
		if ( !array_key_exists( $dataSourceId, $this->usageLimits ) ) {
			// no limit
			return StatusValue::newGood();
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
				return StatusValue::newFatal( 'robloxapi-error-usage-limit-dependent', $dataSourceId, (string)$limit,
					$dataSource->getId() );
			} else {
				return StatusValue::newFatal( 'robloxapi-error-usage-limit', $dataSourceId, (string)$limit );
			}
		}

		return StatusValue::newGood();
	}

	/**
	 * @inheritDoc
	 */
	public function onParserTestGlobals( &$globals ): void {
		$defaults = [
			RobloxAPIConstants::ConfAllowedArguments => [ 'UserID' => [ 54321 ] ],
			// show errors as plain text to make parser tests not depend on changes in Html:errorBox
			RobloxAPIConstants::ConfShowPlainErrors => true,
			RobloxAPIConstants::ConfCacheSplittingOptionalArguments =>
				$this->config->get( RobloxAPIConstants::ConfCacheSplittingOptionalArguments )
		];

		foreach ( $defaults as $key => $value ) {
			$globals["wg$key"] = $value;
		}
		$this->utils->initForParserTests( $defaults );
	}
}
