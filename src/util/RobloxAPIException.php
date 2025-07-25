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

namespace MediaWiki\Extension\RobloxAPI\util;

use Exception;

/**
 * Exception thrown if there are any errors happening when calling the roblox API or parsing the
 * data it returns.
 */
class RobloxAPIException extends Exception {

	/**
	 * @var string[] The parameters to be used in the message.
	 */
	public array $messageParams = [];

	/**
	 * Creates a new RobloxAPIException.
	 * @param string $message
	 * @param string ...$messageParams
	 */
	public function __construct( string $message = '', ...$messageParams ) {
		parent::__construct( $message );

		$this->messageParams = $messageParams;
	}

}
