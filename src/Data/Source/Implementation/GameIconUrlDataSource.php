<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source\Implementation;

use MediaWiki\Extension\RobloxAPI\Args\Types\IArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\IdArgument;
use MediaWiki\Extension\RobloxAPI\Data\Source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\Data\Source\ThumbnailUrlDataSource;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils;

class GameIconUrlDataSource extends ThumbnailUrlDataSource {

	/** @inheritDoc */
	public function __construct( DataSourceProvider $dataSourceProvider, RobloxAPIUtils $utils ) {
		parent::__construct( $dataSourceProvider, $utils, 'gameIconUrl', 'gameIcon' );
	}

	/** @inheritDoc */
	protected function getMainArgument(): IArgument {
		return IdArgument::place();
	}
}
