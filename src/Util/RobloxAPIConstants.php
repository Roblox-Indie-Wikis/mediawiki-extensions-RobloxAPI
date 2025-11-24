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

namespace MediaWiki\Extension\RobloxAPI\Util;

// phpcs:disable Generic.NamingConventions.UpperCaseConstantName.ClassConstantNotUpperCase

/**
 * Holds constants for certain keys and config names.
 */
class RobloxAPIConstants {

	public const ExtensionDataKey = 'RobloxAPIDataSourceUsage';

	public const ConfAllowedArguments = 'RobloxAPIAllowedArguments';
	public const ConfCachingExpiries = 'RobloxAPICachingExpiries';
	public const ConfDataSourceUsageLimits = 'RobloxAPIDataSourceUsageLimits';
	public const ConfDisableCache = 'RobloxAPIDisableCache';
	public const ConfEnabledDataSources = 'RobloxAPIEnabledDatasources';
	public const ConfParserFunctionsExpensive = 'RobloxAPIParserFunctionsExpensive';
	public const ConfRegisterLegacyParserFunctions = 'RobloxAPIRegisterLegacyParserFunctions';
	public const ConfRequestUserAgent = 'RobloxAPIRequestUserAgent';
	public const ConfShowPlainErrors = 'RobloxAPIShowPlainErrors';

}
