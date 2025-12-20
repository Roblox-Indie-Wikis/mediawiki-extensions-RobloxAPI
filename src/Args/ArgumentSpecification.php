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

namespace MediaWiki\Extension\RobloxAPI\Args;

use MediaWiki\Extension\RobloxAPI\Args\Types\BooleanArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\IArgument;
use MediaWiki\Extension\RobloxAPI\Args\Types\JsonKeyArgument;

/**
 * Represents the specification for the arguments that a data source requires.
 */
class ArgumentSpecification {

	/**
	 * @param IArgument[] $requiredArgs The required argument types.
	 * @param array<string, IArgument> $optionalArgs The optional argument's names and types.
	 * @param bool $withJsonArgs Whether to add the default optional arguments for JSON data.
	 */
	public function __construct(
		public array $requiredArgs,
		public array $optionalArgs = [],
		bool $withJsonArgs = false
	) {
		if ( $withJsonArgs ) {
			$this->withJsonArgs();
		}
	}

	public static function for( IArgument ...$requiredArgs ): self {
		return new self( $requiredArgs );
	}

	/**
	 * Adds the default optional arguments for JSON data and returns the instance.
	 */
	public function withJsonArgs(): ArgumentSpecification {
		return $this
			->withOptionalArg( 'pretty', new BooleanArgument() )
			->withOptionalArg( 'json_key', new JsonKeyArgument() );
	}

	/**
	 * Adds a required argument to the specification and returns the instance.
	 * @param IArgument $arg The argument type.
	 */
	public function withRequiredArg( IArgument $arg ): ArgumentSpecification {
		$this->requiredArgs[] = $arg;

		return $this;
	}

	/**
	 * Adds an optional argument to the specification and returns the instance.
	 * @param string $arg The argument name.
	 * @param IArgument $type The argument type.
	 */
	public function withOptionalArg( string $arg, IArgument $type ): ArgumentSpecification {
		$this->optionalArgs[$arg] = $type;

		return $this;
	}

}
