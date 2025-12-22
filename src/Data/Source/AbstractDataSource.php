<?php
/**
 * @license GPL-2.0-or-later
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
	 * Returns an error stating that the data source returned no data.
	 */
	protected function failNoData(): StatusValue {
		return StatusValue::newFatal( 'robloxapi-error-datasource-returned-no-data' );
	}

	/**
	 * Returns an error stating that the data source returned an unexpected data structure.
	 */
	protected function failUnexpectedDataStructure(): StatusValue {
		return StatusValue::newFatal( 'robloxapi-error-unexpected-data-structure' );
	}

	/**
	 * Returns an error stating that the data source returned invalid data.
	 */
	protected function failInvalidData(): StatusValue {
		return StatusValue::newFatal( 'robloxapi-error-invalid-data' );
	}

}
