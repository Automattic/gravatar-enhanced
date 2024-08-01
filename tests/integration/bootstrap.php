<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Gravatar_Enhanced
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

// Shared bootstrap between test suites.
require_once __DIR__ . '/../bootstrap-common.php';

/**
 * Manually load the plugin being tested.
 * @return void
 */
function _manually_load_plugin() {
	require ROOT_DIR . '/gravatar-enhanced.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

echo "{$_tests_dir}/includes/bootstrap.php";

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
