<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Args;

use MediaWiki\Language\Language;

class ArgumentParserContext {

	public function __construct(
		private readonly Language $contentLanguage,
	) {
	}

	public function getContentLanguage(): Language {
		return $this->contentLanguage;
	}

}
