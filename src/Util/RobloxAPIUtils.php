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

namespace MediaWiki\Extension\RobloxAPI\Util;

use LogicException;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Html\Html;
use MediaWiki\Json\FormatJson;
use MediaWiki\Parser\Parser;
use MediaWiki\Utils\UrlUtils;
use StatusValue;
use stdClass;

/**
 * Provides utilities for working with the Roblox API.
 */
class RobloxAPIUtils {

	public const CONSTRUCTOR_OPTIONS = [
		RobloxAPIConstants::ConfShowPlainErrors,
	];

	public function __construct(
		private ServiceOptions $options,
		private readonly UrlUtils $urlUtils,
	) {
		$this->options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
	}

	/**
	 * Verifies that a URL is a Roblox CDN URL
	 * @param string $url The URL to verify
	 */
	public function verifyIsRobloxCdnUrl( string $url ): bool {
		$urlParts = $this->urlUtils->parse( $url );

		return $urlParts !== null
			&& !isset( $urlParts['port'] )
			&& !isset( $urlParts['query'] )
			&& !isset( $urlParts['fragment'] )
			&& $urlParts['scheme'] === 'https'
			&& preg_match( "/^[a-zA-Z0-9]{2}\.rbxcdn\.com$/", $urlParts['host'] )
			&& preg_match( "/[0-9A-Za-z\-\/]*\.(png|webp)?$/", $urlParts['path'] );
	}

	/**
	 * Decides whether a result should be returned as JSON
	 * @param mixed $value The value to check
	 */
	public function shouldReturnJson( mixed $value ): bool {
		return $value instanceof stdClass || is_array( $value );
	}

	/**
	 * Creates a JSON result
	 * @param mixed $jsonObject The JSON object
	 * @param array<string, string> $optionalArgs The optional arguments
	 */
	public function createJsonResult( mixed $jsonObject, array $optionalArgs ): string {
		$pretty = strtolower( $optionalArgs['pretty'] ?? '' ) === 'true';

		// only return the value of json_key in the JSON object
		if ( ( is_object( $jsonObject ) || is_array( $jsonObject ) ) &&
			isset( $optionalArgs['json_key'] ) && trim( $optionalArgs['json_key'] ) !== '' ) {
			$jsonObject = $this->getJsonKey( $jsonObject, $optionalArgs['json_key'] );

			if ( !is_object( $jsonObject ) && !is_array( $jsonObject ) ) {
				return $jsonObject ?? 'null';
			}
		}

		return FormatJson::encode( $jsonObject, $pretty );
	}

	/**
	 * Get a JSON key from a JSON object. This accepts recursively nested keys using '->' as a separator.
	 * @param stdClass|array|mixed|null $jsonObject The JSON object
	 * @param string $jsonKey The JSON key
	 * @return stdClass|mixed|null
	 */
	public function getJsonKey( mixed $jsonObject, string $jsonKey ): mixed {
		if ( !is_object( $jsonObject ) && !is_array( $jsonObject ) ) {
			return null;
		}

		// recursion
		if ( str_contains( $jsonKey, '->' ) ) {
			// split only once by ->
			$parts = explode( '->', $jsonKey, 2 );
			$firstPart = $parts[0];
			$secondPart = $parts[1];

			return $this->getJsonKey( $this->getJsonKey( $jsonObject, $firstPart ), $secondPart );
		}

		// allow array access
		if ( is_array( $jsonObject ) && is_numeric( $jsonKey ) ) {
			return $jsonObject[intval( $jsonKey )] ?? null;
		}

		if ( !property_exists( $jsonObject, $jsonKey ) ) {
			return null;
		}

		return $jsonObject->{$jsonKey};
	}

	/**
	 * @return string Wikitext
	 */
	public function formatStatusValue( StatusValue $status, Parser $parser ): string {
		if ( $status->isGood() ) {
			throw new LogicException( __METHOD__ . ' should only be called for error StatusValues!' );
		}

		$result = '';

		foreach ( $status->getMessages() as $msg ) {
			// Parser::msg doesn't allow a MessageSpecifier as the first arg...
			$message = wfMessage( $msg )
				->inLanguage( $parser->getTargetLanguage() )
				->page( $parser->getPage() )
				->plain();

			$result .= $this->options->get( RobloxAPIConstants::ConfShowPlainErrors )
				? $message
				: Html::errorBox( $message );
		}

		return $result;
	}

	/**
	 * @internal
	 */
	public function initForParserTests( array $defaults ): void {
		$this->options = new ServiceOptions( self::CONSTRUCTOR_OPTIONS, $defaults );
	}

}
