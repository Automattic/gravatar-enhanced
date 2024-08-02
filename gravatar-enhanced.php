<?php
/*
Plugin Name: Gravatar Enhanced
Plugin URI: https://wordpress.org/extend/plugins/gravatar-enhanced/
Description: Enhanced functionality for Gravatar-ifying your WordPress site. Once you've enabled the plugin, go to the "Avatars" section on the <a href="options-discussion.php">Discussion Settings page</a> to get started.
Author: Automattic
Version: 0.3.0 (pre-release)
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 6.6
Requires PHP: 7.4
*/

define( 'GRAVATAR_ENHANCED_PLUGIN_FILE', __FILE__ );

if ( version_compare( phpversion(), '7.4' ) >= 0 ) {
	require_once __DIR__ . '/classes/class-plugin.php';

	$gravatar_enhanced = new Automattic\Gravatar\GravatarEnhanced\Plugin();
	$gravatar_enhanced->init();
}
