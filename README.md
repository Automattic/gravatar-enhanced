# Gravatar Enhanced

![Banner](assets/banner-1544x500.png?raw=true "Gravatar Enhanced")

An enhanced version of Gravatar for WordPress.

## Development

- Clone this repo to the `wp-content/plugins` directory of a WordPress installation
- `composer install`
- `yarn install`
- `yarn build`
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

## Using the WordPress.org plugin

Through the magic of WordPress Playground you can use the released plugin in WordPress.org by clicking this link:

[Open and use WordPress with Gravatar Enhanced](https://playground.wordpress.net/#ewoJIiRzY2hlbWEiOiAiaHR0cHM6Ly9wbGF5Z3JvdW5kLndvcmRwcmVzcy5uZXQvYmx1ZXByaW50LXNjaGVtYS5qc29uIiwKCSJsYW5kaW5nUGFnZSI6ICJ3cC1hZG1pbi9vcHRpb25zLWRpc2N1c3Npb24ucGhwIiwKCSJwcmVmZXJyZWRWZXJzaW9ucyI6IHsKCQkicGhwIjogIjguMCIsCgkJIndwIjogImxhdGVzdCIKCX0sCgkic3RlcHMiOiBbCgkJewoJCQkic3RlcCI6ICJsb2dpbiIsCgkJCSJ1c2VybmFtZSI6ICJhZG1pbiIsCgkJCSJwYXNzd29yZCI6ICJwYXNzd29yZCIKCQl9LAoJCXsKCQkJInN0ZXAiOiAiaW5zdGFsbFBsdWdpbiIsCgkJCSJwbHVnaW5aaXBGaWxlIjogewoJCQkJInJlc291cmNlIjogIndvcmRwcmVzcy5vcmdcL3BsdWdpbnMiLAoJCQkJInNsdWciOiAiZ3JhdmF0YXItZW5oYW5jZWQiCgkJCX0sCgkJCSJvcHRpb25zIjogewoJCQkJImFjdGl2YXRlIjogdHJ1ZQoJCQl9CgkJfSwKCQl7CgkJCSJzdGVwIjogInNldFNpdGVPcHRpb25zIiwKCQkJIm9wdGlvbnMiOiB7CgkJCQkiZ3JhdmF0YXJfaGlkZV9yZWZlcnJlciI6IDEsCgkJCQkiZ3JhdmF0YXJfaG92ZXJjYXJkcyI6IDEsCgkJCQkiZ3JhdmF0YXJfcHJveHkiOiAxLAoJCQkJImdyYXZhdGFyX3Byb3h5X2hhc2giOiAibG9jYWwiLAoJCQkJImdyYXZhdGFyX2ludml0YXRpb25fZW1haWwiOiAxCgkJCX0KCQl9CgldCn0=)

You can use `yarn blueprint` to generate this URL.
