# Gravatar Enhanced

![Banner](assets/banner-1544x500.png?raw=true "Gravatar Enhanced")

An enhanced version of Gravatar for WordPress.

## Development

- Clone this repo to the `wp-content/plugins` directory of a WordPress installation
- `composer install`
- `yarn install`
- Activate plugin
- Configure plugin from the Settings > Discussion page

## Building

The JS and CSS needs to be compiled. You can do this in development mode, which will monitor for updates to the files:

`yarn start`

If you want to build production files (minified and without debugging) then:

`yarn build`

## Releasing
A release packages up all the JS, CSS, and PHP files into a clean directory without any development tooling.

`yarn release`

The plugin will be available in the `release` directory.

## Distribution
To produce a released and versioned distribution of the plugin run:

`yarn dist`

This will produce a zip file, upload it to Github, and mark it as an official release.

You can sync this to the WordPress.org SVN repo with:

`yarn dist:svn`

You will need appropriate permissions.
