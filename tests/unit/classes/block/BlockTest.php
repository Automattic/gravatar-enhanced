<?php

namespace Automattic\Gravatar\GravatarEnhanced\Block;

use Brain\Monkey\Functions;
use GravatarEnhanced\Tests\Unit\TestCase;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', ROOT_DIR . '/' );
}

require_once ROOT_DIR . '/classes/block/class-block.php';

class BlockTest extends TestCase {
	public function test_sanitize_placeholder_profile_allows_only_scalar_text_fields() {
		Functions\when( 'sanitize_text_field' )->alias(
			static function ( $value ) {
				return trim( $value );
			}
		);

		$profile = [
			'display_name' => ' Display Name ',
			'job_title' => 'Engineer',
			'company' => 'Automattic',
			'location' => 'Earth',
			'description' => 'Welcome!',
			'avatar_url' => 'x" onerror="alert(1)',
			'profile_url' => 'javascript:alert(1)',
			'verified_accounts' => [ [ 'url' => 'javascript:alert(1)' ] ],
		];

		$this->assertSame(
			[
				'display_name' => 'Display Name',
				'job_title' => 'Engineer',
				'company' => 'Automattic',
				'location' => 'Earth',
				'description' => 'Welcome!',
			],
			Block::sanitize_placeholder_profile( $profile )
		);
	}

	public function test_sanitize_placeholder_profile_rejects_non_array_input() {
		$this->assertSame( [], Block::sanitize_placeholder_profile( 'not-an-array' ) );
	}
}
