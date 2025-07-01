# Usage

- [Usage](#usage)
    * [Basic Usage](#basic-usage)
    * [Data Sources](#data-sources)
        + [gameData](#gamedata)
        + [activePlayers](#activeplayers)
        + [visits](#visits)
        + [userId](#userid)
        + [userAvatarThumbnail](#useravatarthumbnail)
        + [userAvatarThumbnailUrl](#useravatarthumbnailurl)
        + [assetThumbnail](#assetthumbnail)
        + [assetThumbnailUrl](#assetthumbnailurl)
        + [gameIcon](#gameicon)
        + [gameIconUrl](#gameiconurl)
        + [groupRoles](#grouproles)
        + [groupRank](#grouprank)
        + [groupData](#groupdata)
        + [groupMembers](#groupmembers)
        + [badgeInfo](#badgeinfo)
        + [userInfo](#userinfo)
        + [assetDetails](#assetdetails)
        + [groupRolesList](#grouproleslist)
        + [gameNameDescription](#gamenamedescription)
        + [universeInfo](#universeinfo)
        + [userGames](#usergames)
        + [userPlaceVisits](#userplacevisits)
    * [Handling JSON data](#handling-json-data)
        + [JSON keys](#json-keys)
        + [Pretty-printing JSON data](#pretty-printing-json-data)
    * [FAQs](#faqs)
        + [How do I obtain the Universe ID of a game?](#how-do-i-obtain-the-universe-id-of-a-game)
        + [Embedding images from the Roblox CDN](#embedding-images-from-the-roblox-cdn)
        + [Migrating from the old parser functions](#migrating-from-the-old-parser-functions)
    * [Configuration](#configuration)
        + [`$wgRobloxAPIEnabledDatasources`](#wgrobloxapienableddatasources)
        + [`$wgRobloxAPICachingExpiries`](#wgrobloxapicachingexpiries)
        + [`$wgRobloxAPIAllowedArguments`](#wgrobloxapiallowedarguments)
        + [`$wgRobloxAPIRequestUserAgent`](#wgrobloxapirequestuseragent)
        + [`$wgRobloxAPIDisableCache`](#wgrobloxapidisablecache)
        + [`$wgRobloxAPIParserFunctionsExpensive`](#wgrobloxapiparserfunctionsexpensive)
        + [`$wgRobloxAPIRegisterLegacyParserFunctions`](#wgrobloxapiregisterlegacyparserfunctions)

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

Provides information about a game/place in the [JSON format](#handling-json-data).

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

### activePlayers

Provides the number of active players in a place. Requires [gameData](#gameData) to be enabled.

#### Example

Get the number of active players in a place:

```
{{#robloxAPI: activePlayers | 6483209208 | 132813250731469 }}
```

Get the formatted number of active players in a place:

```
{{formatnum: {{#robloxAPI: activePlayers | 6483209208 | 132813250731469 }} }}
```

#### Required Arguments

| Name         | Description                                  | Type       |
|--------------|----------------------------------------------|------------|
| `UniverseId` | The [universe ID](#universe-id) of the game. | Numeric ID |
| `PlaceId`    | The place ID of the game.                    | Numeric ID |

### visits

Provides the number of visits to a place. Requires [gameData](#gameData) to be enabled.

#### Example

Get the number of visits to a place:

```
{{#robloxAPI: visits | 6483209208 | 132813250731469 }}
```

Get the formatted number of visits to a place:

```
{{formatnum: {{#robloxAPI: visits | 6483209208 | 132813250731469 }} }}
```

#### Required Arguments

| Name         | Description                                  | Type       |
|--------------|----------------------------------------------|------------|
| `UniverseId` | The [universe ID](#universe-id) of the game. | Numeric ID |
| `PlaceId`    | The place ID of the game.                    | Numeric ID |

### userId

Provides the user ID for a given username.

#### Example

Get the user ID of a user:

```
{{#robloxAPI: userId | builderman }}
```

#### Required Arguments

| Name       | Description               | Type   |
|------------|---------------------------|--------|
| `Username` | The username of the user. | String |

### userAvatarThumbnail

Provides data about a user's avatar thumbnail in the [JSON format](#handling-json-data).

#### Example

Get the data about the user avatar thumbnail of builderman (ID 156):

```
{{#robloxAPI: userAvatarThumbnail | 156 | 150x150 }}
```

#### Required Arguments

| Name            | Description                | Type                                                                                                                                                      |
|-----------------|----------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------|
| `UserId`        | The user ID of the user.   | Numeric ID                                                                                                                                                |
| `ThumbnailSize` | The size of the thumbnail. | String (`30x30`, `48x48`, `60x60`, `75x75`, `100x100`, `110x110`, `140x140`, `150x150`, `150x200`, `180x180`, `250x250`, `352x352`, `420x420`, `720x720`) |

#### Optional Arguments

| Name          | Description                               | Type                   | Default | Example            |
|---------------|-------------------------------------------|------------------------|---------|--------------------|
| `is_circular` | Whether the thumbnail should be circular. | Boolean                | `false` | `is_circular=true` |
| `format`      | The format of the thumbnail.              | String (`Png`, `Webp`) | `Png`   | `format=Webp`      |

### userAvatarThumbnailUrl

Provides the URL of a user's avatar thumbnail. Allows [embedding](#Embedding-images-from-the-Roblox-CDN) the avatar
image. Requires [userAvatarThumbnail](#userAvatarThumbnail) to be enabled.

#### Example

Get the URL of the user avatar thumbnail of builderman (ID 156):

```
{{#robloxAPI: userAvatarThumbnailUrl | 156 | 150x150 }}
```

#### Required Arguments

| Name            | Description                | Type                                                                                                                                                      |
|-----------------|----------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------|
| `UserId`        | The user ID of the user.   | Numeric ID                                                                                                                                                |
| `ThumbnailSize` | The size of the thumbnail. | String (`30x30`, `48x48`, `60x60`, `75x75`, `100x100`, `110x110`, `140x140`, `150x150`, `150x200`, `180x180`, `250x250`, `352x352`, `420x420`, `720x720`) |

#### Optional Arguments

| Name          | Description                               | Type                   | Default | Example            |
|---------------|-------------------------------------------|------------------------|---------|--------------------|
| `is_circular` | Whether the thumbnail should be circular. | Boolean                | `false` | `is_circular=true` |
| `format`      | The format of the thumbnail.              | String (`Png`, `Webp`) | `Png`   | `format=Webp`      |

### assetThumbnail

> [!WARNING]
> Roblox enforces a stricter rate limit on the API used for this than on the other APIs.
> It is in general recommended to use it at most once per page.

Provides the data about an asset thumbnail in the [JSON format](#handling-json-data).

#### Example

Get the data about the asset thumbnail of the asset with ID 102611803:

```
{{#robloxAPI: assetThumbnail | 1962446128 | 140x140 }}
```

#### Required Arguments

| Name            | Description                | Type                                                                                                                                                                                                                                                                 |
|-----------------|----------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `AssetId`       | The asset ID of the asset. | Numeric ID                                                                                                                                                                                                                                                           |
| `ThumbnailSize` | The size of the thumbnail. | String (`30x30`, `42x42`, `50x50`, `60x62`, `75x75`, `110x110`, `140x140`, `150x150`, `160x100`, `160x600`, `250x250`, `256x144`, `300x250`, `304x166`, `384x216`, `396x216`, `420x420`, `480x270`, `512x512`, `576x324`, `700x700`, `728x90`, `768x432`, `1200x80`) |

#### Optional Arguments

| Name          | Description                               | Type                   | Default | Example            |
|---------------|-------------------------------------------|------------------------|---------|--------------------|
| `is_circular` | Whether the thumbnail should be circular. | Boolean                | `false` | `is_circular=true` |
| `format`      | The format of the thumbnail.              | String (`Png`, `Webp`) | `Png`   | `format=Webp`      |

### assetThumbnailUrl

> [!WARNING]
> Roblox enforces a stricter rate limit on the API used for this than on the other APIs.
> It is in general recommended to use it at most once per page.

Provides the URL of an asset thumbnail. Allows [embedding](#Embedding-images-from-the-Roblox-CDN) the asset image.
Requires [assetThumbnail](#assetThumbnail) to be enabled.

#### Example

Get the URL of the asset thumbnail of the asset with ID 102611803:

```
{{#robloxAPI: assetThumbnailUrl | 1962446128 | 140x140 }}
```

#### Required Arguments

| Name            | Description                | Type                                                                                                                                                                                                                                                                 |
|-----------------|----------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `AssetId`       | The asset ID of the asset. | Numeric ID                                                                                                                                                                                                                                                           |
| `ThumbnailSize` | The size of the thumbnail. | String (`30x30`, `42x42`, `50x50`, `60x62`, `75x75`, `110x110`, `140x140`, `150x150`, `160x100`, `160x600`, `250x250`, `256x144`, `300x250`, `304x166`, `384x216`, `396x216`, `420x420`, `480x270`, `512x512`, `576x324`, `700x700`, `728x90`, `768x432`, `1200x80`) |

#### Optional Arguments

| Name          | Description                               | Type                   | Default | Example            |
|---------------|-------------------------------------------|------------------------|---------|--------------------|
| `is_circular` | Whether the thumbnail should be circular. | Boolean                | `false` | `is_circular=true` |
| `format`      | The format of the thumbnail.              | String (`Png`, `Webp`) | `Png`   | `format=Webp`      |

### gameIcon

Provides the data about a game icon in the [JSON format](#handling-json-data).

#### Example

Get the data about the game icon of the game with ID 132813250731469:

```
{{#robloxAPI: gameIcon | 132813250731469 | 150x150 }}
```

#### Required Arguments

| Name            | Description               | Type                                                                    |
|-----------------|---------------------------|-------------------------------------------------------------------------|
| `PlaceId`       | The place ID of the game. | Numeric ID                                                              |
| `ThumbnailSize` | The size of the icon.     | String (`50x50`, `128x128`, `150x150`, `256x256`, `420x420`, `512x512`) |

#### Optional Arguments

| Name            | Description                          | Type                                                                              | Default       | Example                     |
|-----------------|--------------------------------------|-----------------------------------------------------------------------------------|---------------|-----------------------------|
| `is_circular`   | Whether the icon should be circular. | Boolean                                                                           | `false`       | `is_circular=true`          |
| `format`        | The format of the icon.              | String (`Png`, `Webp`)                                                            | `Png`         | `format=Webp`               |
| `return_policy` | The return policy of the icon.       | String (`PlaceHolder`, `ForcePlaceHolder`, `AutoGenerated`, `ForceAutoGenerated`) | `PlaceHolder` | `return_policy=PlaceHolder` |

### gameIconUrl

Provides the URL of a game icon. Allows [embedding](#Embedding-images-from-the-Roblox-CDN) the game icon image.
Requires [gameIcon](#gameIcon) to be enabled.

#### Example

Get the URL of the game icon of the game with ID 132813250731469:

```
{{#robloxAPI: gameIconUrl | 132813250731469 | 150x150 }}
```

#### Required Arguments

| Name            | Description               | Type                                                                    |
|-----------------|---------------------------|-------------------------------------------------------------------------|
| `PlaceId`       | The place ID of the game. | Numeric ID                                                              |
| `ThumbnailSize` | The size of the icon.     | String (`50x50`, `128x128`, `150x150`, `256x256`, `420x420`, `512x512`) |

#### Optional Arguments

| Name            | Description                          | Type                                                                              | Default       | Example                     |
|-----------------|--------------------------------------|-----------------------------------------------------------------------------------|---------------|-----------------------------|
| `is_circular`   | Whether the icon should be circular. | Boolean                                                                           | `false`       | `is_circular=true`          |
| `format`        | The format of the icon.              | String (`Png`, `Webp`)                                                            | `Png`         | `format=Webp`               |
| `return_policy` | The return policy of the icon.       | String (`PlaceHolder`, `ForcePlaceHolder`, `AutoGenerated`, `ForceAutoGenerated`) | `PlaceHolder` | `return_policy=PlaceHolder` |

### groupRoles

Provides all group roles a user has in all groups they have joined in the [JSON format](#handling-json-data).

[Official API documentation](https://groups.roblox.com//docs/index.html?urls.primaryName=Groups%20Api%20v1#operations-Membership-get_v1_users__userId__groups_roles)

#### Example

Get all JSON data of the group roles of a user:

```
{{#robloxAPI: groupRoles | 4182456156 }}
```

#### Required Arguments

| Name     | Description              | Type       |
|----------|--------------------------|------------|
| `UserId` | The user ID of the user. | Numeric ID |

### groupRank

Provides the name of a user's rank in a group.
Requires [groupRoles](#groupRoles) to be enabled.

#### Example

Get the name of the rank of the user with ID `4182456156` in the group with ID `3620943`:

```
{{#robloxAPI: groupRank | 3620943 | 4182456156 }}
```

#### Required Arguments

| Name      | Description                | Type       |
|-----------|----------------------------|------------|
| `GroupId` | The group ID of the group. | Numeric ID |
| `UserId`  | The user ID of the user.   | Numeric ID |

### groupData

Provides data about a group in the [JSON format](#handling-json-data).

[Official API documentation](https://groups.roblox.com//docs/index.html?urls.primaryName=Groups%20Api%20v1#operations-Groups-get_v1_groups__groupId_)

#### Example

Get all JSON data of a group:

```
{{#robloxAPI: groupData | 3620943 }}
```

#### Required Arguments

| Name      | Description                | Type       |
|-----------|----------------------------|------------|
| `GroupId` | The group ID of the group. | Numeric ID |

### groupMembers

Provides the number of members in a group.
Requires [groupData](#groupData) to be enabled.

#### Example

Get the number of members in a group:

```
{{#robloxAPI: groupMembers | 3620943 }}
```

Get the formatted number of members in a group:

```
{{formatnum: {{#robloxAPI: groupMembers | 3620943 }} }}
```

#### Required Arguments

| Name      | Description                | Type       |
|-----------|----------------------------|------------|
| `GroupId` | The group ID of the group. | Numeric ID |

### badgeInfo

Provides information about a badge in the [JSON format](#handling-json-data).

#### Example

Get all JSON data of a badge:

```
{{#robloxAPI: badgeInfo | 4488119458388820}}
```

#### Required Arguments

| Name      | Description                | Type       |
|-----------|----------------------------|------------|
| `BadgeId` | The badge ID of the badge. | Numeric ID |

### userInfo

Provides information about a user in the [JSON format](#handling-json-data).

#### Example

Get all JSON data of a user:

```
{{#robloxAPI: userInfo | 156 }}
```

#### Required Arguments

| Name     | Description              | Type       |
|----------|--------------------------|------------|
| `UserId` | The user ID of the user. | Numeric ID |

### assetDetails

Provides information about an asset in the [JSON format](#handling-json-data).

#### Example

Get all JSON data of an asset:

```
{{#robloxAPI: assetDetails | 102611803 }}
```

#### Required Arguments

| Name      | Description                | Type       |
|-----------|----------------------------|------------|
| `AssetId` | The asset ID of the asset. | Numeric ID |

### groupRolesList

Provides a list of roles in a group in the [JSON format](#handling-json-data).

#### Example

Get the roles of a group:

```
{{#robloxAPI: groupRolesList | 5353743 }}
```

#### Required Arguments

| Name      | Description                | Type       |
|-----------|----------------------------|------------|
| `GroupId` | The group ID of the group. | Numeric ID |

### gameNameDescription

Provides the name and description of a game in all supported languages in the [JSON format](#handling-json-data).

#### Example

Get the name and description of a game:

```
{{#robloxAPI: gameNameDescription | 6483209208 }}
```

Get the description of a game in English:

```
{{#robloxAPI: gameNameDescription | 6483209208 | json_key=data->0->description }}
```

#### Required Arguments

| Name         | Description                                  | Type       |
|--------------|----------------------------------------------|------------|
| `UniverseId` | The [universe ID](#universe-id) of the game. | Numeric ID |

### universeInfo

Provides info about a universe in the [JSON format](#handling-json-data).

#### Example

Get info about a universe:

```
{{#robloxAPI: universeInfo | 4864117649 }}
```

Get the privacy type of a universe:

```
{{#robloxAPI: universeInfo | 4864117649 | json_key=privacyType }}
```

#### Required Arguments

| Name         | Description                                  | Type       |
|--------------|----------------------------------------------|------------|
| `UniverseId` | The [universe ID](#universe-id) of the game. | Numeric ID |

### userGames

Provides a list of games a user has created in the [JSON format](#handling-json-data).

Note that it is not possible to get more than 50 games.

#### Example

Get the list of games a user has created:

```
{{#robloxAPI: userGames | 1995870730 }}
```

#### Required Arguments

| Name     | Description              | Type       |
|----------|--------------------------|------------|
| `UserId` | The user ID of the user. | Numeric ID |

#### Optional Arguments

| Name         | Description                            | Type                      | Default |
|--------------|----------------------------------------|---------------------------|---------|
| `limit`      | The maximum number of games to return. | Numeric ID (10, 25 or 50) | `50`    |
| `sort_order` | The order to sort the games.           | String (`Asc`, `Desc`)    | `Asc`   |

### userPlaceVisits

Provides the number of visits of all places a user has created.

Note that due to performance reasons, only the views of the first 50 places of the user are returned.

#### Example

Get the number of visits of all places a user has created:

```
{{#robloxAPI: userPlaceVisits | 1995870730 }}
```

#### Required Arguments

| Name     | Description              | Type       |
|----------|--------------------------|------------|
| `UserId` | The user ID of the user. | Numeric ID |

#### Optional Arguments

| Name         | Description                                                                                                                       | Type                      | Default |
|--------------|-----------------------------------------------------------------------------------------------------------------------------------|---------------------------|---------|
| `limit`      | The maximum number of games to consider in the calculation.                                                                       | Numeric ID (10, 25 or 50) | `50`    |
| `sort_order` | The order to sort the games. This is used by the api and may change the results if the user has more games than the limit allows. | String (`Asc`, `Desc`)    | `Asc`   |

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

To access an item in an array, you can use the index of the item, e.g.:

```
{{#robloxAPI: gameData | 6483209208 | 132813250731469 | json_key=allowedGearGenres->0 }}
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

### Embedding images from the Roblox CDN

The result of the `{{#rblxUserAvatarThumbnailUrl}}` parser function can be used to embed avatar images in your wiki.

#### Wikis on Miraheze

Go to `Special:ManageWiki/settings` on your wiki, search for "External Images" and make sure this option is enabled:
![image](https://github.com/user-attachments/assets/78e86d78-c3d1-487f-8974-1b4e5dbeaab7)

#### 3rd party wikis

To do this, the `$wgEnableImageWhitelist` configuration variable must be set to `true`.

Then, add the following line to the `MediaWiki:External image whitelist` page on your wiki:

> [!WARNING]
> This allows users to embed any image from the Roblox CDN on your wiki.

```regex
^https://([a-zA-Z0-9]{2})\.rbxcdn\.com/
```

### Migrating from the old parser functions

If you are using the old parser functions, it is highly recommended to migrate to the new `{{#robloxAPI}}` parser
function by replacing the old parser function with the new one. Please refer to the documentation above for examples on
how to use each data source with the new parser function. For example, replace:

```
{{#rblxUserId: builderman}}
```

with:

```
{{#robloxAPI: userId | builderman}}
```

## Configuration

### `$wgRobloxAPIEnabledDatasources`

An array of data sources that should be enabled and available. By default, all data sources are enabled:

```php
$wgRobloxAPIEnabledDatasources = [
    'gameData',    
    'activePlayers',
    'visits',
    'userId',
    'userAvatarThumbnail',
    'userAvatarThumbnailUrl',
    'assetThumbnail',
    'assetThumbnailUrl',
    'gameIcon',
    'gameIconUrl',
    'groupRoles',
    'groupData',
    'groupRank',
    'groupMembers',
    'badgeInfo',
    'userInfo',
    'assetDetails',
];
```

### `$wgRobloxAPICachingExpiries`

An array of cache expiry times (in seconds) for each data source.
Default caching expiries:

| Data source           | Expiry            |
|-----------------------|-------------------|
| `*` (default)         | 600 (10 minutes)  |
| `assetThumbnail`      | 7200 (2 hours)    |
| `badgeInfo`           | 1800 (30 minutes) |
| `groupData`           | 3600 (1 hour)     |
| `userAvatarThumbnail` | 3600 (1 hour)     |
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

### `$wgRobloxAPIRegisterLegacyParserFunctions`

Whether to register the legacy parser functions for the extension that were deprecated in version 1.2.0.
By default, they are registered for backwards compatibility:

```php
$wgRobloxAPIRegisterLegacyParserFunctions = true;
```

If you do not need the legacy parser functions, you can set this variable to `false`:

```php
$wgRobloxAPIRegisterLegacyParserFunctions = false;
```

### `$wgRobloxAPIDataSourceUsageLimits`

The maximum number of times a data source can be used on a single page. By default, there are no limits:

```php
$wgRobloxAPIDataSourceUsageLimits = [];
```

If you want to limit the usage of a data source, you can do so like this:

```php
$wgRobloxAPIDataSourceUsageLimits = [
    'gameData' => 1,
    'userId' => 2,
];
```

In this example, the `gameData` data source can only be used once per page, and the `userId` data source can be used
twice per page. If a data source is used more than the allowed limit, an error message will be displayed.

Setting a limit for a data source will also affect data sources that depend on it. Dependent data sources themselves
currently cannot be limited.
