<?php

namespace Automattic\Gravatar\GravatarEnhanced\QuickEditor;

use Brain\Monkey\Functions;
use GravatarEnhanced\Tests\Unit\TestCase;

require_once ROOT_DIR . '/classes/quick-editor/class-quick-editor.php';

class QuickEditorTest extends TestCase {
	protected function tearDown(): void {
		$_GET = [];

		parent::tearDown();
	}

	public function test_profile_page_ignores_forged_user_id() {
		$_GET['user_id'] = '1';
		$current_user = (object) [
			'ID' => 7,
			'user_email' => 'subscriber@example.com',
		];

		Functions\expect( 'wp_get_current_user' )->once()->andReturn( $current_user );
		Functions\expect( 'get_userdata' )->never();

		$this->assertSame( $current_user, $this->create_quick_editor( true )->resolve_user() );
	}

	public function test_user_edit_page_rejects_unauthorized_target() {
		$_GET['user_id'] = '17';

		Functions\when( 'absint' )->alias( 'intval' );
		Functions\expect( 'current_user_can' )->once()->with( 'edit_user', 17 )->andReturn( false );
		Functions\expect( 'get_userdata' )->never();

		$this->assertFalse( $this->create_quick_editor( false )->resolve_user() );
	}

	public function test_user_edit_page_resolves_authorized_target() {
		$_GET['user_id'] = '17';
		$target_user = (object) [
			'ID' => 17,
			'user_email' => 'target@example.com',
		];

		Functions\when( 'absint' )->alias( 'intval' );
		Functions\expect( 'current_user_can' )->once()->with( 'edit_user', 17 )->andReturn( true );
		Functions\expect( 'get_userdata' )->once()->with( 17 )->andReturn( $target_user );

		$this->assertSame( $target_user, $this->create_quick_editor( false )->resolve_user() );
	}

	/**
	 * @param bool $is_profile_page Whether the request is for profile.php.
	 */
	private function create_quick_editor( $is_profile_page ) {
		return new class( $is_profile_page ) extends QuickEditor {
			/** @var bool */
			private $is_profile_page;

			public function __construct( $is_profile_page ) {
				$this->is_profile_page = $is_profile_page;
			}

			public function resolve_user() {
				return $this->get_user();
			}

			protected function is_profile_page() {
				return $this->is_profile_page;
			}
		};
	}
}
