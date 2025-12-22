<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Tests\Integration;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;

/**
 * @method getServiceContainer()
 */
trait ParserDependentTestTrait {

	protected function createParser(): Parser {
		$parser = $this->getServiceContainer()->getParserFactory()->create();
		$lang = $this->getServiceContainer()->getLanguageFactory()->getLanguage( 'en' );
		$parserOptions = ParserOptions::newFromAnon();
		$parserOptions->setTargetLanguage( $lang );
		$parser->setOptions( $parserOptions );
		$parser->setPage( Title::newFromText( 'RobloxAPITest' ) );
		return $parser;
	}

}
