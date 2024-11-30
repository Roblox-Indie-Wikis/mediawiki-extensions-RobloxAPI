# Usage

## Parser functions

### Data parser functions

These functions return processed data from the Roblox API.

| Name                              | Description                                  | Arguments                 | Example                                                   | Internal name                   |
|-----------------------------------|----------------------------------------------|---------------------------|-----------------------------------------------------------|---------------------------------|
| `{{#rblxGroupRank}}`              | Get the name of a user's rank in a group.    | `GroupId`, `UserId`       | `{{#rblxGroupRank: 32670248 \| 4182456156}}`              | `roblox_grouprank`              |
| `{{#rblxPlaceActivePlayers}}`     | Get the number of active players in a place. | `UniverseId`, `PlaceId`   | `{{#rblxPlaceActivePlayers: 4252370517 \| 12018816388}}`  | `roblox_activeplayers`          |
| `{{#rblxPlaceVisits}}`            | Get the number of visits to a place.         | `UniverseId`, `PlaceId`   | `{{#rblxPlaceVisits: 4252370517 \| 12018816388}}`         | `roblox_visits`                 |
| `{{#rblxGroupMembers}}`           | Get the number of members in a group.        | `GroupId`                 | `{{#rblxGroupMembers: 32670248}}`                         | `roblox_groupmembers`           |
| `{{#rblxUserAvatarThumbnailUrl}}` | Get the URL of a user's avatar thumbnail.    | `UserId`, `ThumbnailSize` | `{{#rblxUserAvatarThumbnailUrl: 1995870730 \| 140x140 }}` | `roblox_useravatarthumbnailurl` |

### JSON parser functions

These functions return the raw data from the Roblox API as JSON with little to no processing. Each function is
automatically enabled if the corresponding data source is enabled.

| Name                           | Arguments                 | Example                                                | Data source           |
|--------------------------------|---------------------------|--------------------------------------------------------|-----------------------|
| `{{#rblxGameData}}`            | `UniverseId`, `PlaceId`   | `{{#rblxGameData: 4252370517 \| 12018816388}}`         | `gameData`            |
| `{{#rblxGroupRoles}}`          | `UserID`                  | `{{#rblxGroupRoles: 4182456156}}`                      | `groupRoles`          |
| `{{#rblxGroupData}}`           | `GroupId`                 | `{{#rblxGroupData: 32670248}}`                         | `groupData`           |
| `{{#rblxUserAvatarThumbnail}}` | `UserId`, `ThumbnailSize` | `{{#rblxUserAvatarThumbnail: 1995870730 \| 140x140 }}` | `userAvatarThumbnail` |
| `{{#rblxBadgeInfo}}`           | `BadgeId`                 | `{{#rblxBadgeInfo: 2146223500}}`                       | `badgeInfo`           |
| `{{#rblxUserInfo}}`            | `UserId`                  | `{{#rblxUserInfo: 1995870730}}`                        | `userInfo`            |
| `{{#rblxAssetDetails}}`        | `AssetId`                 | `{{#rblxAssetDetails: 102611803}}`                     | `assetDetails`        |

### Data sources

The following data sources are available:

| Name                  | Description                                   |
|-----------------------|-----------------------------------------------|
| `gameData`            | Game data for a place in a universe.          |
| `groupRoles`          | Returns all group roles of a user.            |
| `groupData`           | Returns data about a group.                   |
| `userAvatarThumbnail` | Returns the URL of a user's avatar thumbnail. |
| `badgeInfo`           | Returns information about a badge.            |
| `userInfo`            | Returns information about a user.             |
| `assetDetails`        | Returns information about an asset.           |

### Argument types

#### IDs

Allowed values: Any positive integer.

#### `ThumbnailSize`

Allowed values: `30x30`, `48x48`, `60x60`, `75x75`, `100x100`, `110x110`, `140x140`, `150x150`, `150x200`, `180x180`,
`250x250`, `352x352`, `420x420`, `720x720`

## Configuration
