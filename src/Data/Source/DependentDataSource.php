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

namespace MediaWiki\Extension\RobloxAPI\Data\Source;

use LogicException;

abstract class DependentDataSource extends AbstractDataSource {

	/**
	 * @var IDataSource The data source that this data source depends on.
	 */
	protected IDataSource $dataSource;

	/**
	 * @param DataSourceProvider $dataSourceProvider
	 * @param string $id The id of this data source.
	 * @param string $dependencyId
	 */
	public function __construct(
		DataSourceProvider $dataSourceProvider,
		string $id,
		string $dependencyId
	) {
		parent::__construct( $id );
		$nullableDataSource = $dataSourceProvider->getDataSource( $dependencyId );
		if ( $nullableDataSource === null ) {
			throw new LogicException( "Tried constructing dependent data source $this->id" .
				", but dependency $dependencyId was not found!" );
		}
		$this->dataSource = $nullableDataSource;
	}

	/** @inheritDoc */
	public function getFetcherSourceId(): string {
		return $this->dataSource->getFetcherSourceId();
	}

	/**
	 * @inheritDoc
	 */
	public function isEnabled(): bool {
		return $this->dataSource->isEnabled() && parent::isEnabled();
	}

}
