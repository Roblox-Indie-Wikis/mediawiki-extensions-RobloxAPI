<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args;

class ArgumentParserResult {

	/**
	 * @param string[] $requiredArgs
	 * @param array<string, mixed> $optionalArgs
	 */
	public function __construct(
		private readonly array $requiredArgs,
		private readonly array $optionalArgs,
	) {
	}

	/**
	 * @return string[]
	 */
	public function getRequiredArgs(): array {
		return $this->requiredArgs;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getOptionalArgs(): array {
		return $this->optionalArgs;
	}

}
