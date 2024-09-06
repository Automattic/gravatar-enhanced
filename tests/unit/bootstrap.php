<?php

// Shared bootstrap between test suites.
require_once __DIR__ . '/../bootstrap-common.php';

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
