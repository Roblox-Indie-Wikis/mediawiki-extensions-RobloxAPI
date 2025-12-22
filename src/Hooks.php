<?php
/**
 * @license GPL-2.0-or-later
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
				return $status->getValue();
			} else {
				$parser->addTrackingCategory( 'robloxapi-category-error' );
				return $this->utils->formatStatusValue( $status, $parser );
			}
		} );

		if ( $this->config->get( RobloxAPIConstants::ConfRegisterLegacyParserFunctions ) ) {
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

						$status = $this->argumentParser->parse( $dataSource->getArgumentSpecification(), $args );
						if ( !$status->isGood() ) {
							$parser->addTrackingCategory( 'robloxapi-category-error' );
							return $this->utils->formatStatusValue( $status, $parser );
						}
						$parseResult = $status->getValue();
						$execStatus = $dataSource->exec(
							$parser,
							$parseResult->getRequiredArgs(),
							$parseResult->getOptionalArgs()
						);
						if ( !$execStatus->isGood() ) {
							$parser->addTrackingCategory( 'robloxapi-category-error' );
							return $this->utils->formatStatusValue( $execStatus, $parser );
						}
						$result = $execStatus->getValue();

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
			// @phan-suppress-next-line PhanTypeMismatchReturn Bad status, value type is irrelevant
			return $status;
		}
		$dataSource = $status->getValue();

		$canUse = $this->canUseDataSource( $parser, $dataSource );
		if ( !$canUse->isGood() ) {
			return $canUse;
		}

		$otherArgs = array_slice( $args, 1 );

		$argumentSpecification = $dataSource->getArgumentSpecification();

		$status = $this->argumentParser->parse( $argumentSpecification, $otherArgs );
		if ( !$status->isGood() ) {
			// @phan-suppress-next-line PhanTypeMismatchReturn Bad status, value type is irrelevant
			return $status;
		}
		$parseResult = $status->getValue();

		$execStatus = $dataSource->exec( $parser, $parseResult->getRequiredArgs(), $parseResult->getOptionalArgs() );
		if ( !$execStatus->isGood() ) {
			return $execStatus;
		}
		$result = $execStatus->getValue();
		$shouldEscape = $dataSource->shouldEscapeResult( $result );

		if ( $this->utils->shouldReturnJson( $result ) ) {
			$result = $this->utils->createJsonResult( $result, $parseResult->getOptionalArgs() );
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
			return StatusValue::newFatal(
				'robloxapi-error-datasource-disabled',
				wfEscapeWikiText( $dataSource->getId() )
			);
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
		$this->argumentParser->overrideOptions( [
			RobloxAPIConstants::ConfAllowedArguments => [ 'user-id' => [ 54321 ] ],
		] );
		$this->utils->overrideOptions( [
			RobloxAPIConstants::ConfShowPlainErrors => true,
		] );
	}
}
