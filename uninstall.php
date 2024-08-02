<?php

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}

if ( version_compare( phpversion(), '7.4' ) >= 0 ) {
	require_once __DIR__ . '/classes/class-plugin.php';

	$gravatar_enhanced = new Automattic\Gravatar\GravatarEnhanced\Plugin();
	$gravatar_enhanced->uninstall();
}
