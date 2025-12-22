<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source\Implementation;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Args\Types\BooleanArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\IdArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\ThumbnailFormatArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\ThumbnailSizeArgument;
use MediaWiki\Extension\RobloxAPI\Data\Fetcher\RobloxAPIFetcher;
use MediaWiki\Extension\RobloxAPI\Data\Source\ThumbnailDataSource;

class AssetThumbnailDataSource extends ThumbnailDataSource {

	/**
	 * @inheritDoc
	 */
	public function __construct( RobloxAPIFetcher $fetcher ) {
		parent::__construct( 'assetThumbnail', $fetcher, 'assets', 'assetIds' );
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return ArgumentSpecification::for( IdArgument::asset(), new ThumbnailSizeArgument() )
			->withOptionalArg( 'is_circular', new BooleanArgument() )
			->withOptionalArg( 'format', new ThumbnailFormatArgument() )
			->withJsonArgs();
	}

}
