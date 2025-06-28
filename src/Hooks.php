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
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\ParserTestGlobalsHook;
use MediaWiki\Parser\Parser;

class Hooks implements ParserFirstCallInitHook, ParserTestGlobalsHook {

	private Config $config;
	private DataSourceProvider $dataSourceProvider;
	/**
	 * @var array|data\source\IDataSource[]
	 */
	private array $legacyParserFunctions;

	public function __construct( ConfigFactory $configFactory ) {
		$this->config = $configFactory->makeConfig( 'RobloxAPI' );

		$this->dataSourceProvider = new DataSourceProvider( $this->config );

		$this->legacyParserFunctions = [];
		if ( $this->config->get( 'RobloxAPIRegisterLegacyParserFunctions' ) ) {
			$this->legacyParserFunctions += $this->dataSourceProvider->createLegacyParserFunctions();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onParserFirstCallInit( $parser ): void {
		$parser->setFunctionHook( 'robloxapi', function ( Parser $parser, ...$args ): array|bool {
			try {
				return $this->handleParserFunctionCall( $parser, $args );
			} catch ( RobloxAPIException $exception ) {
				return wfMessage( $exception->getMessage() )
					->inContentLanguage()
					->plaintextParams( ...$exception->messageParams )
					->escaped();
			}
		} );

		foreach ( $this->legacyParserFunctions as $id => $function ) {
			// all data source parser functions are only enabled if the corresponding data source
			// is enabled, so we don't need to check the config for that
			$parser->setFunctionHook( $id, function ( Parser $parser, ...$args ) use ( $function ) {
				if ( $this->config->get( 'RobloxAPIParserFunctionsExpensive' ) &&
					!$parser->incrementExpensiveFunctionCount() ) {
					return false;
				}
				try {
					$result = $function->exec( $parser, ...$args );

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
					return wfMessage( $exception->getMessage() )
						->inContentLanguage()
						->plaintextParams( ...$exception->messageParams )
						->escaped();
				}
			} );
		}
	}

	/**
	 * Handles a call to the #robloxAPI parser function.
	 * @param Parser $parser
	 * @param array $args
	 * @return array|bool
	 * @throws RobloxAPIException
	 */
	private function handleParserFunctionCall( Parser $parser, array $args ): array|bool {
		if ( $this->config->get( 'RobloxAPIParserFunctionsExpensive' ) &&
			!$parser->incrementExpensiveFunctionCount() ) {
			return false;
		}

		if ( count( $args ) == 0 ) {
			throw new RobloxAPIException( 'robloxapi-error-no-arguments' );
		}
		$dataSourceId = $args[0];
		$dataSource = $this->dataSourceProvider->getDataSource( $dataSourceId, true );

		if ( !$dataSource ) {
			throw new RobloxAPIException( 'robloxapi-error-datasource-not-found', $dataSourceId );
		}

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
	 * @inheritDoc
	 */
	public function onParserTestGlobals( &$globals ): void {
		$globals += [
			'wgRobloxAPIAllowedArguments' => [ 'UserID' => [ 54321 ] ],
		];
	}
}
