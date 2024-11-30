# RobloxAPI

A MediaWiki extension which provides easy access to the Roblox API via a parser function. 

> [!WARNING]
> This extension is not production-ready yet.
> It is still in development and breaking changes may occur at any time.

<!--
## Usage 
### Group Ranks
Get a users group rank: 
### Experience Statistics
> [!IMPORTANT]
> The Universe ID is not the same as the Game ID. 

Grab concurrent players for a set experience:
``
Grab number of favourites for a set experience: 

Grab number of visits for a set experience: 
!-->
## Current plans

- [ ] Rewrite the parser function/data source system to be more simple and testable
- [ ] Increase test coverage
- [ ] Add more API endpoints
- [ ] Add more config options, e.g. to disable the cache or have a group/place id whitelist

## Installation

Currently, the only way to install the extension is to clone it from the repository directly.

1. Clone the repository:
    ```sh
   cd extensions/
    git clone https://github.com/dovedalewiki/mediawiki-extensions-RobloxAPI.git
    ```
2. Add the following line to the end of your `LocalSettings.php` file:
    ```php
    wfLoadExtension( 'RobloxAPI' );
    ```

## Development

1. install nodejs, npm, and PHP composer
2. change to the extension's directory
3. `npm install`
4. `composer install`

Once set up, running `npm test` and `composer test` will run automated code checks.
