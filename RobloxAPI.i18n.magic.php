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

$magicWords = [];
$magicWords['en'] = [
	'robloxapi' => [ 0, 'robloxAPI' ],
	// DEPRECATED - all of these parser functions are replaced by #robloxAPI and might be removed in the future
	'roblox_GroupRank' => [ 0, 'rblxGroupRank' ],
	'roblox_ActivePlayers' => [ 0, 'rblxPlaceActivePlayers' ],
	'roblox_Visits' => [ 0, 'rblxPlaceVisits' ],
	'roblox_GroupMembers' => [ 0, 'rblxGroupMembers' ],
	'roblox_UserAvatarThumbnailUrl' => [ 0, 'rblxUserAvatarThumbnailUrl' ],
	'roblox_UserId' => [ 0, 'rblxUserId' ],
	'roblox_GameData' => [ 0, 'rblxGameData' ],
	'roblox_GroupRoles' => [ 0, 'rblxGroupRoles' ],
	'roblox_GroupData' => [ 0, 'rblxGroupData' ],
	'roblox_UserAvatarThumbnail' => [ 0, 'rblxUserAvatarThumbnail' ],
	'roblox_BadgeInfo' => [ 0, 'rblxBadgeInfo' ],
	'roblox_UserInfo' => [ 0, 'rblxUserInfo' ],
	'roblox_AssetDetails' => [ 0, 'rblxAssetDetails' ],
];
