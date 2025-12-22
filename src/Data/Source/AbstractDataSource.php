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

use StatusValue;

abstract class AbstractDataSource implements IDataSource {

	/**
	 * @var bool Whether this data source is enabled.
	 */
	private bool $enabled;

	/**
	 * @param string $id The ID of this data source.
	 */
	public function __construct(
		public readonly string $id,
	) {
		$this->enabled = true;
	}

	/**
	 * @inheritDoc
	 */
	public function isEnabled(): bool {
		return $this->enabled;
	}

	/**
	 * @inheritDoc
	 */
	public function disable(): void {
		$this->enabled = false;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldEscapeResult( mixed $result ): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRegisterLegacyParserFunction(): bool {
		return false;
	}

	/**
	 * Throws an exception stating that the data source returned no data.
	 */
	protected function failNoData(): StatusValue {
		return StatusValue::newFatal( 'robloxapi-error-datasource-returned-no-data' );
	}

	/**
	 * Throws an exception stating that the data source returned an unexpected data structure.
	 */
	protected function failUnexpectedDataStructure(): StatusValue {
		return StatusValue::newFatal( 'robloxapi-error-unexpected-data-structure' );
	}

	/**
	 * Throws an exception stating that the data source returned invalid data.
	 */
	protected function failInvalidData(): StatusValue {
		return StatusValue::newFatal( 'robloxapi-error-invalid-data' );
	}

}
