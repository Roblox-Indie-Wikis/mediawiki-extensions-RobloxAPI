# Contributing

- [Contributing](#contributing)
    * [Requesting a new data source](#requesting-a-new-data-source)
        + [Requirements](#requirements)
    * [Developing on the RobloxAPI extension](#developing-on-the-robloxapi-extension)
        + [Installing dependencies](#installing-dependencies)
        + [Running tests](#running-tests)
            - [PHP unit tests](#php-unit-tests)
            - [Parser tests](#parser-tests)
            - [JavaScript tests](#javascript-tests)
        + [Code Style](#code-style)
    * [Releasing a new version](#releasing-a-new-version)

## Requesting a new data source

If you would like to request a new data source, please open an issue on the GitHub repository. Please use the
*Roblox API endpoint request* template.

### Requirements

A data source must fulfill the following requirements:

* It must be accessible without authentication
* It must be an official endpoint provided by Roblox
* It must not require multiple requests to be made (if this is required, consider requesting two separate data sources)

## Developing on the RobloxAPI extension

### Installing dependencies

1. install nodejs, npm, and PHP composer
2. change to the extension's directory
3. `npm install`
4. `composer install`

### Running tests

#### PHP unit tests

0. If you are running a MediaWiki docker container, go to the container's directory and run
   `docker compose exec mediawiki bash` to get a shell in the container.
2. Run one of the following commands:
    - `composer phpunit:entrypoint -- --group RobloxAPI` to run all tests for the extension
    - `composer phpunit:entrypoint -- extensions/RobloxAPI/tests/phpunit/unit/<file>` to run all tests in a specific
      file
    - `composer phpunit:entrypoint -- extensions/RobloxAPI/tests/phpunit/unit/<folder>` to run all tests in a specific
      folder

#### Parser tests

0. If you are running a MediaWiki docker container, go to the container's directory and run
   `docker compose exec mediawiki bash` to get a shell in the container.
1. Run `php tests/parser/parserTests.php --file=extensions/RobloxAPI/tests/parser/parserTests.txt` to run the parser
   tests.

#### JavaScript tests

Running `npm test`will run automated code checks.

### Code Style

The PHP part of this project follows
the [MediaWiki coding conventions](https://www.mediawiki.org/wiki/Manual:Coding_conventions/PHP). The code formatting is
enforced by the CI pipeline which runs phan before a PR can be merged.

## Releasing a new version

This guide is only for maintainers of the extension.

1. Update the version number in `extension.json`
2. Update the documentation if there were any relevant changes
3. Merge development into master
4. Create a new release on GitHub
5. Close milestone (if applicable)
6. Create a branch from master with the version number as the branch name, e.g. `ver/1.1.0`
7. Update the documentation on mediawiki.org if there were any relevant changes
