<?php

// Constants
define( 'ROOT_DIR', dirname( __DIR__ ) );
define( 'GRAVATAR_ENHANCED_PLUGIN_FILE', ROOT_DIR . '/gravatar-enhanced.php' );
define( 'GRAVATAR_ENHANCED_VERSION', 'version' );

// Autoload
require_once ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/classes/options/class-saved-options.php';
require_once ROOT_DIR . '/classes/avatar/class-avatar-options.php';
require_once ROOT_DIR . '/classes/proxy/class-proxy-options.php';
require_once ROOT_DIR . '/classes/analytics/class-analytics-options.php';
