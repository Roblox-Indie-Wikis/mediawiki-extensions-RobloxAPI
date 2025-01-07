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
use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\parserFunction\ActivePlayersParserFunction;
use MediaWiki\Extension\RobloxAPI\parserFunction\DataSourceParserFunction;
use MediaWiki\Extension\RobloxAPI\parserFunction\GroupMembersParserFunction;
use MediaWiki\Extension\RobloxAPI\parserFunction\GroupRankParserFunction;
use MediaWiki\Extension\RobloxAPI\parserFunction\PlaceVisitsParserFunction;
use MediaWiki\Extension\RobloxAPI\parserFunction\UserAvatarThumbnailUrlParserFunction;
use MediaWiki\Extension\RobloxAPI\parserFunction\UserIdParserFunction;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\MediaWikiServices;
use Parser;

class Hooks implements ParserFirstCallInitHook {

	private Config $config;
	private DataSourceProvider $dataSourceProvider;
	private array $legacyParserFunctions;

	public function __construct() {
		$this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'RobloxAPI' );

		$this->dataSourceProvider = new DataSourceProvider( $this->config );

		$this->legacyParserFunctions = [
			'roblox_grouprank' => new GroupRankParserFunction( $this->dataSourceProvider ),
			'roblox_activeplayers' => new ActivePlayersParserFunction( $this->dataSourceProvider ),
			'roblox_visits' => new PlaceVisitsParserFunction( $this->dataSourceProvider ),
			'roblox_groupmembers' => new GroupMembersParserFunction( $this->dataSourceProvider ),
			'roblox_useravatarthumbnailurl' => new UserAvatarThumbnailUrlParserFunction( $this->dataSourceProvider ),
			'roblox_userid' => new UserIdParserFunction( $this->dataSourceProvider ),
		];
		$this->legacyParserFunctions += $this->dataSourceProvider->createLegacyParserFunctions();
	}

	/**
	 * @inheritDoc
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook( 'robloxapi', function ( Parser $parser, ...$args ) {
			return $this->handleParserFunctionCall( $parser, $args );
		} );
		foreach ( $this->legacyParserFunctions as $id => $function ) {
			// all data source parser functions are only enabled if the corresponding data source
			// is enabled, so we don't need to check the config for that
			$isEnabled =
				$function instanceof DataSourceParserFunction ||
				in_array( $id, $this->config->get( 'RobloxAPIEnabledParserFunctions' ) );
			if ( $isEnabled ) {
				$parser->setFunctionHook( $id, function ( Parser $parser, ...$args ) use ( $function ) {
					if ( $this->config->get( 'RobloxAPIParserFunctionsExpensive' ) &&
						!$parser->incrementExpensiveFunctionCount() ) {
						return false;
					}
					try {
						$result = $function->exec( $parser, ...$args );

						if ( !$function->shouldEscapeResult( $result ) ) {
							return $result;
						}

						// escape wikitext, we don't need any of the results to be parsed
						return wfEscapeWikiText( $result );
					} catch ( RobloxAPIException $exception ) {
						return wfMessage( $exception->getMessage(), ...$exception->messageParams )->escaped();
					}
				} );
			}
		}
	}

	/**
	 * Handles a call to the #robloxAPI parser function.
	 * @param Parser $parser
	 * @param array $args
	 * @return void
	 * @throws RobloxAPIException
	 */
	private function handleParserFunctionCall( Parser $parser, array $args ): string {
		if ( $this->config->get( 'RobloxAPIParserFunctionsExpensive' ) &&
			!$parser->incrementExpensiveFunctionCount() ) {
			return false;
		}

		if ( count( $args ) == 0 ) {
			return wfMessage( 'robloxapi-error-no-arguments' )->escaped();
		}
		$dataSourceId = $args[0];
		$dataSource = $this->dataSourceProvider->getDataSource( $dataSourceId, true );

		if ( !$dataSource ) {
			return wfMessage( 'robloxapi-error-datasource-not-found', $dataSourceId )->escaped();
		}

		$otherArgs = array_slice( $args, 1 );

		$argumentSpecification = $dataSource->getArgumentSpecification();
		// TODO preprocess args
		// TODO extract this logic into a separate method
		$requiredArgs = [];
		$optionalArgs = [];

		foreach ( $argumentSpecification->requiredArgs as $type ) {
			if ( count( $otherArgs ) === 0 ) {
				return wfMessage( 'robloxapi-error-missing-argument', $type )->escaped();
			}
			$value = array_shift( $otherArgs );
			RobloxAPIUtil::assertArgAllowed( $this->config, $type, $value );
			$requiredArgs[] = $value;
		}

		// optional args are named, e.g. name=value
		foreach ( $otherArgs as $string ) {
			$parts = explode( '=', $string, 2 );

			if ( count( $parts ) === 1 ) {
				return wfMessage( 'robloxapi-error-missing-optional-argument-value', $parts[0] )->escaped();
			}

			$key = $parts[0];
			$value = $parts[1];

			if ( !array_key_exists( $key, $argumentSpecification->optionalArgs ) ) {
				return wfMessage( 'robloxapi-error-unknown-optional-argument', $key )->escaped();
			}

			$type = $argumentSpecification->optionalArgs[$key];
			RobloxAPIUtil::assertArgAllowed( $this->config, $type, $value );

			$optionalArgs[$key] = $value;
		}

		return $dataSource->exec( $this->dataSourceProvider, $parser, $requiredArgs, $optionalArgs );
	}
}
