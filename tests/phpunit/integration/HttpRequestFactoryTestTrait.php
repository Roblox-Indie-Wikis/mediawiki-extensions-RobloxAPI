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
