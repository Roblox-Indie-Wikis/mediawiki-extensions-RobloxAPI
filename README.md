# RobloxAPI

A MediaWiki extension which provides easy access to the Roblox API via parser functions. The Roblox API is generally
very poorly documented, and using ExternalData or Lua can be hard or not viable for all wiki users. This extension aims
to make it easy for you to grab data from Roblox and put it on your wiki.

> [!NOTE]
> This extension is still in development.

See it live: https://dovedale.wiki

## Installation

1. Download the repository using the following link: [Download ZIP](https://github.com/dovedalewiki/mediawiki-extensions-RobloxAPI/archive/master.zip)
2. Place the files in a directory called `RobloxAPI` in your `extensions/` folder.
2. Add the following line to the end of your `LocalSettings.php` file:
    ```php
    wfLoadExtension( 'RobloxAPI' );
    ```

Are you on Miraheze? This extension is available on Special:ManageWiki! Search for 'RobloxAPI' and install it with a click.

## Usage

For more information on how to use the extension, see the [USAGE.md](USAGE.md) file.

## Development

1. install nodejs, npm, and PHP composer
2. change to the extension's directory
3. `npm install`
4. `composer install`

Once set up, running `npm test` and `composer test` will run automated code checks.

