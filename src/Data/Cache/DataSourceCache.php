<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Cache;

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIConstants;
use Wikimedia\ObjectCache\WANObjectCache;

/**
 * Caches data returned by the Roblox API.
 */
class DataSourceCache {

	public const CONSTRUCTOR_OPTIONS = [
		RobloxAPIConstants::ConfCacheSplittingOptionalArguments,
		RobloxAPIConstants::ConfDisableCache,
	];

	/** @var bool Whether the cache is disabled */
	private bool $disabled;

	public function __construct(
		private readonly ServiceOptions $options,
		private readonly WANObjectCache $cache,
	) {
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$this->disabled = $options->get( RobloxAPIConstants::ConfDisableCache );
	}

	/**
	 * Tries to search for a value in the cache.
	 * @param string $endpoint
	 * @param string[] $args
	 * @param array<string, mixed> $optionalArgs
	 */
	public function getResultForEndpoint( string $endpoint, array $args, array $optionalArgs ): mixed {
		if ( $this->disabled ) {
			return null;
		}

		$value = $this->cache->get( $this->getCacheKey( $endpoint, $args, $optionalArgs ) );
		if ( $value === false ) {
			return null;
		}

		return $value;
	}

	/**
	 * Saves an entry to the cache.
	 * @param string $endpoint
	 * @param mixed $value
	 * @param string[] $args
	 * @param array<string, mixed> $optionalArgs
	 * @param int $expiry The expiry in seconds
	 */
	public function registerCacheEntry(
		string $endpoint,
		mixed $value,
		array $args,
		array $optionalArgs,
		int $expiry
	): void {
		if ( $this->disabled ) {
			return;
		}

		$this->cache->set( $this->getCacheKey( $endpoint, $args, $optionalArgs ), $value, $expiry );
	}

	/**
	 * Generates a cache key for the given endpoint and arguments.
	 * @param string $endpoint
	 * @param string[] $args
	 * @param array<string, string> $optionalArgs
	 */
	protected function getCacheKey( string $endpoint, array $args, array $optionalArgs ): string {
		$cacheSplittingOptionalArgs = array_intersect_key(
			$optionalArgs,
			array_flip( $this->options->get( RobloxAPIConstants::ConfCacheSplittingOptionalArguments ) )
		);
		ksort( $cacheSplittingOptionalArgs );

		$argsJson = json_encode( $args );
		$optionalArgsJson = json_encode( $cacheSplittingOptionalArgs );

		return $this->cache->makeKey(
			'robloxapi',
			$endpoint,
			md5( $argsJson ),
			md5( $optionalArgsJson ),
		);
	}

}
