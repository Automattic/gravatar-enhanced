<?php

// Shared bootstrap between test suites.
require_once __DIR__ . '/../bootstrap-common.php';

// Constants normally defined by the plugin file, which unit tests don't load.
define( 'GRAVATAR_ENHANCED_PLUGIN_FILE', ROOT_DIR . '/gravatar-enhanced.php' );
define( 'GRAVATAR_ENHANCED_VERSION', 'version' );

// Framework.
require __DIR__ . '/test-case.php';

function __( $text, $group ) {
	return $text;
}

function sanitize_textarea_field( $text ) {
	return $text;
}

function wp_unslash( $text ) {
	return $text;
}
