<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Data\Source;

use MediaWiki\Extension\RobloxAPI\Args\ArgumentSpecification;
use MediaWiki\Extension\RobloxAPI\Args\Types\BooleanArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\IdArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\ThumbnailFormatArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\ThumbnailSizeArgument;
use MediaWiki\Extension\RobloxAPI\Util\RobloxAPIUtils;
use MediaWiki\Parser\Parser;
use StatusValue;

abstract class ThumbnailUrlDataSource extends DependentDataSource {
	/**
	 * @inheritDoc
	 */
	public function __construct(
		DataSourceProvider $dataSourceProvider,
		private readonly RobloxAPIUtils $utils,
		string $id,
		string $dependencyId,
	) {
		parent::__construct( $dataSourceProvider, $id, $dependencyId );
	}

	/**
	 * @inheritDoc
	 * @return StatusValue<string> URL of the thumbnail
	 */
	public function exec( Parser $parser, array $requiredArgs, array $optionalArgs = [] ): StatusValue {
		$dataStatus = $this->dataSource->exec( $parser, $requiredArgs, $optionalArgs );

		if ( !$dataStatus->isOK() ) {
			return $dataStatus;
		}
		$data = $dataStatus->getValue();

		if ( !$data ) {
			return $this->failNoData();
		}

		if ( count( $data ) === 0 ) {
			return $this->failInvalidData();
		}

		$url = $data[0]->imageUrl;

		if ( !$url ) {
			return $this->failInvalidData();
		}

		$format = $optionalArgs['format'] ?? 'Png';
		$lowerFormat = strtolower( $format );

		$url = "$url.$lowerFormat";

		if ( !$this->utils->verifyIsRobloxCdnUrl( $url ) ) {
			return $this->failInvalidData();
		}

		return StatusValue::newGood( $url );
	}

	/**
	 * @inheritDoc
	 */
	public function shouldEscapeResult( mixed $result ): bool {
		// The url should not be escaped here in order to be embedded correctly using $wgEnableImageWhitelist.
		// If the URL was escaped here, it would be URL-encoded and not recognized by MediaWiki as an image URL.
		return !$this->utils->verifyIsRobloxCdnUrl( $result );
	}

	/**
	 * @inheritDoc
	 */
	public function getArgumentSpecification(): ArgumentSpecification {
		return ArgumentSpecification::for( IdArgument::user(), new ThumbnailSizeArgument() )
			->withOptionalArg( 'is_circular', new BooleanArgument() )
			->withOptionalArg( 'format', new ThumbnailFormatArgument() );
	}

}
