# Usage

TODO: REGENERATE TOC

## Basic Usage

To use any data source, you can use the `{{#robloxAPI: ... }}` parser function. The first argument is the name of the
data source, and the rest of the arguments are the arguments for the data source.

For example, to get the ID of a user named `builderman`, you can use:

```
{{#robloxAPI: userId | builderman }}
```

This example uses the data source `userId` and provides one required argument, `builderman`.

Each data source has a set number of required arguments. Additionally, there are some optional arguments that are
specified in the `key=value` format.

## Data Sources

### gameData

Provides information about a game/place in the [JSON format](#Handling-JSON-data).

#### Example

Get all JSON data of a game:

```
{{#robloxAPI: gameData | 6483209208 | 132813250731469 }}
```

Get the name of the creator of a game:

```
{{#robloxAPI: gameData | 6483209208 | 132813250731469 | json_key=creator->name }}
```

#### Required arguments

| Name         | Description                                  | Type       |
|--------------|----------------------------------------------|------------|
| `UniverseId` | The [universe ID](#universe-id) of the game. | Numeric ID |
| `PlaceId`    | The place ID of the game.                    | Numeric ID |

## Handling JSON data

### JSON keys

Some data sources return plain JSON data from the Roblox API. To parse this data, you can either use Lua (with the
Scribunto extension) or use the `json_key` optional argument:

```
{{#robloxAPI: userInfo | 156 | json_key=created }}
```

This example gets the user info of the user with the ID `156` and returns the `created` key from the JSON data.

Nested keys can be accessed by separating them with '->', e.g.:

```
{{#robloxAPI: gameData | 6483209208 | 132813250731469 | json_key=creator->name }}
```

### Pretty-printing JSON data

To pretty-print JSON data, you can use the `pretty` optional argument:

```
{{#robloxAPI: userInfo | 156 | pretty=true }}
```

## FAQs

<a id="universe-id"></a>

### How do I obtain the Universe ID of a game?

To get the universe ID of a place, input the place ID to this API:

```
https://apis.roblox.com/universes/v1/places/<GAMEID>/universe
```

---

---

### Data parser functions

These functions return processed data from the Roblox API. See below on how to get the universe ID of any experience.

| Name                              | Description                                  | Arguments                 | Example                                                   | Internal name                   |
|-----------------------------------|----------------------------------------------|---------------------------|-----------------------------------------------------------|---------------------------------|
| `{{#rblxGroupRank}}`              | Get the name of a user's rank in a group.    | `GroupId`, `UserId`       | `{{#rblxGroupRank: 32670248 \| 4182456156}}`              | `roblox_grouprank`              |
| `{{#rblxPlaceActivePlayers}}`     | Get the number of active players in a place. | `UniverseId`, `PlaceId`   | `{{#rblxPlaceActivePlayers: 4252370517 \| 12018816388}}`  | `roblox_activeplayers`          |
| `{{#rblxPlaceVisits}}`            | Get the number of visits to a place.         | `UniverseId`, `PlaceId`   | `{{#rblxPlaceVisits: 4252370517 \| 12018816388}}`         | `roblox_visits`                 |
| `{{#rblxGroupMembers}}`           | Get the number of members in a group.        | `GroupId`                 | `{{#rblxGroupMembers: 32670248}}`                         | `roblox_groupmembers`           |
| `{{#rblxUserAvatarThumbnailUrl}}` | Get the URL of a user's avatar thumbnail.    | `UserId`, `ThumbnailSize` | `{{#rblxUserAvatarThumbnailUrl: 1995870730 \| 140x140 }}` | `roblox_useravatarthumbnailurl` |
| `{{#rblxUserId}}`                 | Get the user ID of a user.                   | `Username`                | `{{#rblxUserId: builderman}}`                             | `roblox_userid`                 |

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
| `userId`              | Returns the user ID of a user.                |

Note that not all data sources have a corresponding JSON parser function.
Check [JSON parser functions](#json-parser-functions) for more information on which data sources have JSON parser
functions.

### Argument types

#### IDs

Allowed values: Any positive integer.

#### Obtaining the Universe ID

To get an experience universe ID, input the game ID to this API:

```
https://apis.roblox.com/universes/v1/places/<GAMEID>/universe
```

#### `ThumbnailSize`

Allowed values: `30x30`, `48x48`, `60x60`, `75x75`, `100x100`, `110x110`, `140x140`, `150x150`, `150x200`, `180x180`,
`250x250`, `352x352`, `420x420`, `720x720`

## Configuration

### `$wgRobloxAPIEnabledDatasources`

An array of data sources that should be enabled and available. By default, all data sources are enabled:

```php
$wgRobloxAPIEnabledDatasources = [
    'gameData',
    'groupRoles',
    'groupData',
    'userAvatarThumbnail',
    'badgeInfo',
    'userInfo',
    'assetDetails',
];
```

### `$wgRobloxAPIEnabledParserFunctions`

An array of parser functions that should be enabled. JSON parser functions cannot be enabled or disabled here, instead
they are enabled if their corresponding data source is enabled. By default, all data parser functions are enabled:

```php
$wgRobloxAPIEnabledParserFunctions = [
    'roblox_grouprank',
    'roblox_activeplayers',
    'roblox_visits',
    'roblox_groupmembers',
    'roblox_useravatarthumbnailurl',
];
```

### `$wgRobloxAPICachingExpiries`

An array of cache expiry times (in seconds) for each data source.
Default caching expiries:

| Data source           | Expiry            |
|-----------------------|-------------------|
| `*` (default)         | 600 (10 minutes)  |
| `userAvatarThumbnail` | 3600 (1 hour)     |
| `groupData`           | 3600 (1 hour)     |
| `badgeInfo`           | 1800 (30 minutes) |
| `userId`              | 86400 (24 hours)  |
| `userInfo`            | 86400 (24 hours)  |

> [!WARNING]
> Lower cache expiry times can lead to more requests to the Roblox API, which can lead to rate limiting and decreased
> wiki performance.

If you want to set different cache expiry times for specific data sources, you can do so like this:

```php
$wgRobloxAPICachingExpiries = [
    '*' => 6000,
    'gameData' => 120,
    'groupRoles' => 180,
];
```

In this example, all other data sources will have a cache expiry time of 60 seconds.

### `$wgRobloxAPIAllowedArguments`

An array of allowed arguments per argument type. If empty, all arguments for the type are allowed. Any argument types
that do not have an entry in this array will allow any value. This is useful for restricting arguments. By default, all
arguments are allowed:

```php
$wgRobloxAPIAllowedArguments = [];
```

If you want to restrict the allowed arguments for a specific type, you can do so like this:

```php
$wgRobloxAPIAllowedArguments = [
    'GameID' => [123456, 789012],
];
```

In this example, only the Game IDs 123456 and 789012 are allowed.

### `$wgRobloxAPIRequestUserAgent`

The user agent that should be used when making requests to the Roblox API. By default, it uses the
default one provided by MediaWiki. If you want to change it, you can set this variable to a custom user agent:

```php
$wgRobloxAPIRequestUserAgent = 'RobloxAPI MediaWiki Extension';
```

### `$wgRobloxAPIDisableCache`

Whether to disable the cache for the extension. By default, caching is enabled:

```php
$wgRobloxAPIDisableCache = false;
```

If you want to disable caching, you can set this variable to `true`:

```php
$wgRobloxAPIDisableCache = true;
```

### `$wgRobloxAPIParserFunctionsExpensive`

Whether to mark the extension's parser functions as expensive. By default, they are marked as expensive:

```php
$wgRobloxAPIParserFunctionsExpensive = true;
```

If you don't want to mark the extension's parser functions as expensive, you can set this variable to `false`:

```php
$wgRobloxAPIParserFunctionsExpensive = false;
```

## Embedding avatar images

The result of the `{{#rblxUserAvatarThumbnailUrl}}` parser function can be used to embed avatar images in your wiki.
To do this, the `$wgEnableImageWhitelist` configuration variable must be set to `true`.

Then, add the following line to the `MediaWiki:External image whitelist` page on your wiki:

> [!WARNING]
> This allows users to embed any image from the Roblox CDN on your wiki.

```regex
^https://([a-zA-Z0-9]{2})\.rbxcdn\.com/
```
