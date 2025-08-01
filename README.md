# RobloxAPI

A MediaWiki extension which provides easy access to the Roblox API via parser functions. The Roblox API is generally
very poorly documented, and using ExternalData or Lua can be hard or not viable for all wiki users. This extension aims
to make it easy for you to grab data from Roblox and put it on your wiki. Formerly developed
for [Dovedale Wiki](https://github.com/dovedalewiki).

> [!NOTE]
> In version 1.2.0, major changes have been made to the extension. The new `{{#robloxAPI}}` parser function provides new
> features and should now be the only one used to access the Roblox API. While the old parser functions remain working
> as before, they might be removed in a future version.
>
> Please see the [USAGE.md](USAGE.md#Migrating-from-the-old-parser-functions) file for the latest documentation on how
> to use the extension.

Live Examples:

* [Hybrid Cafe Wiki](https://hybridcafe.wiki/)
* [Untitled Tag Game Wiki](https://utg.miraheze.org/)

## Installation

> [!TIP]
> Miraheze users may use ManageWiki to install this extension. Search for 'RobloxAPI' in *Special:ManageWiki/extensions*
> and install it with a click.

Requirements:
* MediaWiki 1.43 or higher
* PHP 8.1.0 or higher

1. Download the repository using the following
   link: [Download ZIP](https://github.com/Roblox-Indie-Wikis/mediawiki-extensions-RobloxAPI/archive/master.zip)
2. Place the files in a directory called `RobloxAPI` in your `extensions/` folder.
3. Add the following line to the end of your `LocalSettings.php` file:
    ```php
    wfLoadExtension( 'RobloxAPI' );
    ```

## Usage

For more information on how to use the extension, see the [USAGE.md](USAGE.md) file.

## Contributing

See the [CONTRIBUTING.md](CONTRIBUTING.md) file for information on how to contribute to the project.

## Indie Roblox Wikis 
If you are interested in the work we do - like developing this extension, please do check out our website and members [here](https://indierobloxwikis.org). 
