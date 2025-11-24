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

namespace MediaWiki\Extension\RobloxAPI\ParserFunction;

use MediaWiki\Extension\RobloxAPI\data\Source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\data\Source\IDataSource;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtil;
use MediaWiki\Parser\Parser;

/**
 * A parser function that provides the data of a data source.
 * @deprecated Replaced by data sources in v1.2.0.
 */
class DataSourceParserFunction extends RobloxApiParserFunction {

	public function __construct( DataSourceProvider $dataSourceProvider, private readonly IDataSource $dataSource ) {
		parent::__construct( $dataSourceProvider );
	}

	/**
	 * @inheritDoc
	 */
	public function exec( DataSourceProvider $dataSourceProvider, Parser $parser, ...$args ): mixed {
		[ $requiredArgs, $optionalArgs ] =
			RobloxAPIUtil::parseArguments( $this->dataSource->getArgumentSpecification(), $args,
				$this->dataSourceProvider->options );

		return $this->dataSource->exec( $this->dataSourceProvider, $parser, $requiredArgs, $optionalArgs );
	}

	/**
	 * @inheritDoc
	 */
	public function shouldEscapeResult( mixed $result ): bool {
		return $this->dataSource->shouldEscapeResult( $result );
	}

	/**
	 * @inheritDoc
	 */
	public function getDataSource(): IDataSource {
		return $this->dataSource;
	}

}
