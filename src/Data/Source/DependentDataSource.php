<?php
/**
 * @license GPL-2.0-or-later
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

	/** @inheritDoc */
	public function isEnabled(): bool {
		return $this->dataSource->isEnabled() && parent::isEnabled();
	}

}
