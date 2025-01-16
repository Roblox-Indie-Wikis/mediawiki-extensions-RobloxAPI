# RobloxAPI

A MediaWiki extension which provides easy access to the Roblox API via parser functions. The Roblox API is generally
very poorly documented, and using ExternalData or Lua can be hard or not viable for all wiki users. This extension aims
to make it easy for you to grab data from Roblox and put it on your wiki. Formerly developed
by [Dovedale Wiki](https://github.com/dovedalewiki).

> [!NOTE]
> In version 1.2.0, major changes have been made to the extension. The new `{{#robloxAPI}}` parser function provides new
> features and should now be the only one used to access the Roblox API. While the old parser functions remain working
> as before, they might be removed in a future version.
>
> Please see the [USAGE.md](USAGE.md) file for the latest documentation on how to use the extension.

Live Examples:

* https://dovedale.wiki/
* https://hybridcafe.wiki/
* https://utg.miraheze.org/

## Installation

1. Download the repository using the following
   link: [Download ZIP](https://github.com/Roblox-Indie-Wikis/mediawiki-extensions-RobloxAPI/archive/master.zip)
2. Place the files in a directory called `RobloxAPI` in your `extensions/` folder.
3. Add the following line to the end of your `LocalSettings.php` file:
    ```php
    wfLoadExtension( 'RobloxAPI' );
    ```

Miraheze users may use ManageWiki to install this extension. Search for 'RobloxAPI' in *Special:ManageWiki* and
install it with a click.

## Usage

For more information on how to use the extension, see the [USAGE.md](USAGE.md) file.

## Contributing

See the [CONTRIBUTING.md](CONTRIBUTING.md) file for information on how to contribute to the project.

