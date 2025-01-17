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

namespace MediaWiki\Extension\RobloxAPI\data\args;

/**
 * Represents the specification for the arguments that a data source requires.
 */
class ArgumentSpecification {

	/**
	 * @var string[] The required argument types.
	 */
	public array $requiredArgs;

	/**
	 * @var array<string, string> The optional argument's names and types.
	 */
	public array $optionalArgs;

	/**
	 * @param string[] $requiredArgs The required argument types.
	 * @param array<string, string> $optionalArgs The optional argument's names and types.
	 */
	public function __construct( array $requiredArgs, array $optionalArgs = [] ) {
		$this->requiredArgs = $requiredArgs;
		$this->optionalArgs = $optionalArgs;
	}

	/**
	 * Adds the default optional arguments for JSON data and returns the instance.
	 * @return ArgumentSpecification
	 */
	public function withJsonArgs(): ArgumentSpecification {
		$this->optionalArgs['pretty'] = 'Boolean';
		$this->optionalArgs['json_key'] = 'String';

		return $this;
	}

	/**
	 * Adds a required argument to the specification and returns the instance.
	 * @param string $arg The argument type.
	 * @return ArgumentSpecification
	 */
	public function withRequiredArg( string $arg ): ArgumentSpecification {
		$this->requiredArgs[] = $arg;

		return $this;
	}

	/**
	 * Adds an optional argument to the specification and returns the instance.
	 * @param string $arg The argument name.
	 * @param string $type The argument type.
	 * @return ArgumentSpecification
	 */
	public function withOptionalArg( string $arg, string $type ): ArgumentSpecification {
		$this->optionalArgs[$arg] = $type;

		return $this;
	}

}
