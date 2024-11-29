# RobloxAPI

A MediaWiki extension which provides access to the roblox API.

> [!WARNING]
> This extension is not production-ready yet.
> It is still in development and breaking changes may occur at any time.

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
