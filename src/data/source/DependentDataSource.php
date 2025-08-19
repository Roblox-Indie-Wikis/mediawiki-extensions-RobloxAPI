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

use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;

abstract class DependentDataSource implements IDataSource {

	/**
	 * @var IDataSource The data source that this data source depends on.
	 */
	protected IDataSource $dataSource;

	/**
	 * @param DataSourceProvider $dataSourceProvider
	 * @param string $id The id of this data source.
	 * @param string $dependencyId
	 * @throws RobloxAPIException If the data source could not be registered.
	 */
	public function __construct(
		DataSourceProvider $dataSourceProvider,
		protected readonly string $id,
		string $dependencyId
	) {
		$this->dataSource = $dataSourceProvider->getDataSourceOrThrow( $dependencyId );
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRegisterLegacyParserFunction(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldEscapeResult( mixed $result ): bool {
		return true;
	}

	/**
	 * Throws an exception stating that the data source returned no data.
	 * @throws RobloxAPIException
	 */
	protected function failNoData(): never {
		throw new RobloxAPIException( 'robloxapi-error-datasource-returned-no-data' );
	}

	/**
	 * Throws an exception stating that the data source returned an unexpected data structure.
	 * @throws RobloxAPIException
	 */
	protected function failUnexpectedDataStructure(): never {
		throw new RobloxAPIException( 'robloxapi-error-unexpected-data-structure' );
	}

	/**
	 * Throws an exception stating that the data source returned invalid data.
	 * @throws RobloxAPIException
	 */
	protected function failInvalidData(): never {
		throw new RobloxAPIException( 'robloxapi-error-invalid-data' );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->id;
	}

	public function getFetcherSourceId(): string {
		return $this->dataSource->getFetcherSourceId();
	}

}
