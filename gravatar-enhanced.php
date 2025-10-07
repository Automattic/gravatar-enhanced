<?php
/*
Plugin Name: Gravatar Enhanced
Plugin URI: https://wordpress.org/extend/plugins/gravatar-enhanced/
Description: Enhanced functionality for Gravatar-ifying your WordPress site. Once you've enabled the plugin, go to the "Avatars" section on the <a href="options-discussion.php">Discussion Settings page</a> to get started.
Author: Automattic
Version: 0.13.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 6.6
Requires PHP: 7.4
*/

define( 'GRAVATAR_ENHANCED_PLUGIN_FILE', __FILE__ );
define( 'GRAVATAR_ENHANCED_VERSION', '0.13.0' );

if ( version_compare( phpversion(), '7.4' ) >= 0 ) {
	require_once __DIR__ . '/classes/class-plugin.php';

	$gravatar_enhanced = new Automattic\Gravatar\GravatarEnhanced\Plugin();
	$gravatar_enhanced->init();

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once __DIR__ . '/classes/wp-cli/class-wp-cli.php';
	}
}
