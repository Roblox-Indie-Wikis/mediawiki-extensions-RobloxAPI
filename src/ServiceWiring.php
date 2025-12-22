<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\Args\ArgumentParser;
use MediaWiki\Extension\RobloxAPI\Data\Cache\DataSourceCache;
use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\Data\Source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils;
use MediaWiki\MediaWikiServices;

/**
 * @phpcs-require-sorted-array
 * Tested in ServiceWiringTest.php
 */
return [
	'RobloxAPI.ArgumentParser' => static function ( MediaWikiServices $services ): ArgumentParser {
		return new ArgumentParser(
			new ServiceOptions(
				ArgumentParser::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->getContentLanguage(),
		);
	},
	'RobloxAPI.DataSourceCache' => static function ( MediaWikiServices $services ): DataSourceCache {
		return new DataSourceCache(
			new ServiceOptions(
				DataSourceCache::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->getMainWANObjectCache(),
		);
	},
	'RobloxAPI.DataSourceProvider' => static function ( MediaWikiServices $services ): DataSourceProvider {
		return new DataSourceProvider(
			new ServiceOptions(
				DataSourceProvider::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->get( 'RobloxAPI.RobloxAPIFetcher' ),
			$services->get( 'RobloxAPI.Utils' ),
		);
	},
	'RobloxAPI.RobloxAPIFetcher' => static function ( MediaWikiServices $services ): RobloxAPIFetcher {
		return new RobloxAPIFetcher(
			new ServiceOptions(
				RobloxAPIFetcher::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->get( 'RobloxAPI.DataSourceCache' ),
			$services->getHttpRequestFactory(),
		);
	},
	'RobloxAPI.Utils' => static function ( MediaWikiServices $services ): RobloxAPIUtils {
		return new RobloxAPIUtils(
			new ServiceOptions(
				RobloxAPIUtils::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->getUrlUtils(),
		);
	},
];
