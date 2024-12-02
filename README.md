# RobloxAPI

A MediaWiki extension which provides easy access to the Roblox API via parser functions. The Roblox API is generally
very poorly documented, and using ExternalData or Lua can be hard or not viable for all wiki users. This extension aims
to make it easy for you to grab data from Roblox and put it on your wiki.

> [!NOTE]
> This extension is still in development.

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

## Usage

For more information on how to use the extension, see the [USAGE.md](USAGE.md) file.
<!-- See this extension in action on the [Dovedale Wiki])(https://dovedale.wiki) and the [Hybrid Cafe](https://hybridcafe.wiki)!-->  

## Development

1. install nodejs, npm, and PHP composer
2. change to the extension's directory
3. `npm install`
4. `composer install`

Once set up, running `npm test` and `composer test` will run automated code checks.

