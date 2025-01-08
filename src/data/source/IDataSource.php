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

namespace MediaWiki\Extension\RobloxAPI\data\source;

use MediaWiki\Extension\RobloxAPI\data\args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use Parser;

/**
 * Represents a data source.
 */
interface IDataSource {

	/**
	 * Executes the data source. This is called when the #robloxAPI parser function is used.
	 * @param DataSourceProvider $dataSourceProvider
	 * @param Parser $parser
	 * @param array<string> $requiredArgs
	 * @param array<string, string> $optionalArgs
	 * @return string
	 * @throws RobloxAPIException If the data source fails to execute
	 */
	public function exec(
		DataSourceProvider $dataSourceProvider, Parser $parser, array $requiredArgs, array $optionalArgs
	): string;

	/**
	 * Determines whether a legacy parser function should be registered.
	 * @return bool
	 */
	public function shouldRegisterLegacyParserFunction(): bool;

	/**
	 * Gets the argument specification for this data source.
	 * @return ArgumentSpecification
	 */
	public function getArgumentSpecification(): ArgumentSpecification;

	/**
	 * @param string $result The result of the parser function.
	 * @return bool Whether the result should be escaped and url-encoded.
	 */
	public function shouldEscapeResult( string $result ): bool;

}
