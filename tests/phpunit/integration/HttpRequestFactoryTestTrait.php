<?php
/**
 * @license GPL-2.0-or-later
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\Tests\Integration;

use GuzzleHttpRequest;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Status\Status;
use StatusValue;

trait HttpRequestFactoryTestTrait {

	/**
	 * @param ?string $returnedContent Content to be returned by the request
	 * @param int $status HTTP status code to be returned by the request
	 * @param bool $goodStatus Whether the request should be considered successful
	 * @return array{HttpRequestFactory, GuzzleHttpRequest}
	 */
	private function createMockHttpRequestFactory(
		?string $returnedContent,
		int $status = 200,
		bool $goodStatus = true,
	): array {
		$request = $this->createPartialMock(
			GuzzleHttpRequest::class,
			[ 'execute', 'getContent' ]
		);

		if ( $goodStatus ) {
			$requestStatus = StatusValue::newGood( $status );
		} else {
			$requestStatus = StatusValue::newFatal( 'mock-http-request-failure', $status );
		}

		$request->expects( $this->once() )->method( 'execute' )->willReturn( Status::wrap( $requestStatus ) );

		if ( $returnedContent ) {
			$request->expects( $this->once() )->method( 'getContent' )->willReturn( $returnedContent );
		} else {
			$request->expects( $this->atMost( 2 ) )->method( 'getContent' )->willReturn( '' );
		}

		$httpRequestFactory = $this->createPartialMock( HttpRequestFactory::class, [ 'create' ] );
		$httpRequestFactory->expects( $this->once() )->method( 'create' )->willReturn( $request );

		return [ $httpRequestFactory, $request ];
	}

}
