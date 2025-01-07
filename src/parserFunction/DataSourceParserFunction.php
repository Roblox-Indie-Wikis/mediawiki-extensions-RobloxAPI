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

use FormatJson;
use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\data\source\FetcherDataSource;

/**
 * A parser function that provides the data of a data source.
 */
class DataSourceParserFunction extends RobloxApiParserFunction {

	private FetcherDataSource $dataSource;

	public function __construct( DataSourceProvider $dataSourceProvider, FetcherDataSource $dataSource ) {
		parent::__construct( $dataSourceProvider );
		$this->dataSource = $dataSource;
	}

	/**
	 * @inheritDoc
	 */
	public function exec( $parser, ...$args ): string {
		// TODO consider directly returning the raw json instead
		// right now, we encode the json because the data source is returning a StdClass object.
		return FormatJson::encode( $this->dataSource->fetch( ...$args ) );
	}

}
