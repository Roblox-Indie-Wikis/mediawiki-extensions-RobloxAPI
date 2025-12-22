<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source\Implementation;

use MediaWiki\Extension\RobloxAPI\Data\Source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\Data\Source\ThumbnailUrlDataSource;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils;

class AssetThumbnailUrlDataSource extends ThumbnailUrlDataSource {

	/** @inheritDoc */
	public function __construct( DataSourceProvider $dataSourceProvider, RobloxAPIUtils $utils ) {
		parent::__construct( $dataSourceProvider, $utils, 'assetThumbnailUrl', 'assetThumbnail' );
	}

}
