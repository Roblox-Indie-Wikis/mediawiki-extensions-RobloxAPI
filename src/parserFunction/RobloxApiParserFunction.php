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

namespace MediaWiki\Extension\RobloxAPI\parserFunction;

use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\data\source\IDataSource;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Parser\Parser;

/**
 * Defines a parser function that can be used to access the Roblox API.
 * @deprecated Replaced by data sources in v1.2.0.
 */
abstract class RobloxApiParserFunction {

	/**
	 * @var DataSourceProvider An instance of the data source provider.
	 */
	protected DataSourceProvider $dataSourceProvider;

	public function __construct( DataSourceProvider $dataSourceProvider ) {
		$this->dataSourceProvider = $dataSourceProvider;
	}

	/**
	 * Executes the parser function
	 * @param DataSourceProvider $dataSourceProvider
	 * @param Parser $parser
	 * @param mixed ...$args
	 * @throws RobloxAPIException If any error regarding the API or data occurs during execution.
	 */
	abstract public function exec( DataSourceProvider $dataSourceProvider, Parser $parser, ...$args ): mixed;

	/**
	 * @param mixed $result The result of the parser function.
	 * @return bool Whether the result should be escaped and url-encoded.
	 */
	public function shouldEscapeResult( mixed $result ): bool {
		return true;
	}

	/**
	 * @return IDataSource The data source associated with this parser function.
	 */
	abstract public function getDataSource(): IDataSource;

}
