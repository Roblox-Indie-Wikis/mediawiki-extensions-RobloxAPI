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

use MediaWiki\Config\Config;

/**
 * Handles the registration of data sources and stores them.
 */
class DataSourceProvider {

	private Config $config;
	/**
	 * @var array The currently enabled data sources.
	 */
	public array $dataSources = [];

	public function __construct( Config $config ) {
		$this->config = $config;

		$this->registerDataSource( new GameDataSource() );
		$this->registerDataSource( new GroupRolesDataSource() );
	}

	/**
	 * Checks the config on whether a data source is enabled.
	 * @param string $id
	 * @return bool
	 */
	protected function isEnabled( string $id ): bool {
		$enabledDataSources = $this->config->get( 'RobloxAPIEnabledDatasources' );

		return in_array( $id, $enabledDataSources );
	}

	/**
	 * Registers a data source if it is enabled.
	 * @param DataSource $dataSource
	 * @return void
	 */
	public function registerDataSource( DataSource $dataSource ): void {
		$id = $dataSource->id;
		if ( $this->isEnabled( $id ) ) {
			$this->dataSources[$dataSource->id] = $dataSource;
		}
	}

	public function getDataSource( string $id ): ?DataSource {
		if ( array_key_exists( $id, $this->dataSources ) ) {
			return $this->dataSources[$id];
		}
		return null;
	}

}
