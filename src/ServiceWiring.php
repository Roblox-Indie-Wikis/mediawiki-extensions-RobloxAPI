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

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\data\Cache\DataSourceCache;
use MediaWiki\Extension\RobloxAPI\data\fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;
use MediaWiki\MediaWikiServices;

/** @phpcs-require-sorted-array */
return [
	'RobloxAPI.DataSourceCache' => static function ( MediaWikiServices $services ): DataSourceCache {
		return new DataSourceCache(
			new ServiceOptions(
				DataSourceCache::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->getMainWANObjectCache()
		);
	},
	'RobloxAPI.DataSourceProvider' => static function ( MediaWikiServices $services ): DataSourceProvider {
		return new DataSourceProvider(
			new ServiceOptions(
				DataSourceProvider::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->get( 'RobloxAPI.RobloxAPIFetcher' )
		);
	},
	'RobloxAPI.RobloxAPIFetcher' => static function ( MediaWikiServices $services ): RobloxAPIFetcher {
		return new RobloxAPIFetcher(
			new ServiceOptions(
				RobloxAPIFetcher::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->get( 'RobloxAPI.DataSourceCache' ),
			$services->getHttpRequestFactory()
		);
	}
];
