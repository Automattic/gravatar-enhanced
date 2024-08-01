<?php

namespace Automattic\Gravatar\GravatarEnhanced\Hovercards;

use GravatarEnhanced\Tests\Unit\TestCase;

require_once ROOT_DIR . '/classes/hovercards/class-hovercards.php';

class HovercardsTest extends TestCase {

	/**
	 * @var Hovercards
	 */
	private $hovercards;

	public function setUp(): void {
		parent::setUp();

		$this->hovercards = new Hovercards();
	}

	public function testInit_WhenCalled_ThenAddsExpectedHooks() {
		// Act.
		$this->hovercards->init();

		// Assert.
		$this->assertNotFalse(
			has_action(
				'admin_init',
				[ $this->hovercards, 'admin_init' ]
			),
			'admin_init action is missing'
		);
		$this->assertNotFalse(
			has_action(
				'wp_enqueue_scripts',
				[ $this->hovercards, 'maybe_add_hovercards' ]
			),
			'maybe_add_hovercards action is missing'
		);
	}

	public function testMaybeAddHovercards_WhenDisabled_ThenDoesNothing() {
		// Arrange.
		\Brain\Monkey\Functions\expect( 'get_option' )->once()->with( 'gravatar_hovercards' )->andReturn( false );
		\Brain\Monkey\Functions\expect( 'wp_enqueue_script' )->never();
		\Brain\Monkey\Functions\expect( 'wp_enqueue_style' )->never();
		\Brain\Monkey\Functions\expect( 'wp_add_inline_script' )->never();

		// Act.
		$this->hovercards->maybe_add_hovercards();

		// Assert – implicit in arrange.
	}

	public function testGravatarEnhancedAddHovercards_WhenEnabled_ThenEnqueuesScripts() {
		// Arrange.
		\Brain\Monkey\Functions\expect( 'get_option' )->once()->with( 'gravatar_hovercards' )->andReturn( true );
		\Brain\Monkey\Functions\expect( 'wp_enqueue_script' )->once()->with( 'gravatar-enhanced-js', Hovercards::GRAVATAR_ENHANCED_HOVERCARD_URL, [], Hovercards::GRAVATAR_ENHANCED_HOVERCARD_VERSION, true );
		\Brain\Monkey\Functions\expect( 'wp_enqueue_style' )->once()->with( 'gravatar-enhanced-style', Hovercards::GRAVATAR_ENHANCED_HOVERCARD_STYLES_URL, [], Hovercards::GRAVATAR_ENHANCED_HOVERCARD_VERSION );
		$inline_script = null;
		\Brain\Monkey\Functions\expect( 'wp_add_inline_script' )->once()->with( 'gravatar-enhanced-js', \Mockery::capture( $inline_script ) );

		// Act.
		$this->hovercards->maybe_add_hovercards();

		// Assert – mostly implicit in arrange.
		$this->assertStringContainsString( 'if ( Gravatar.Hovercards ) {', $inline_script, 'Gravatar.Hovercards object is checked' );
		$this->assertStringContainsString( 'const hovercards = new Gravatar.Hovercards();', $inline_script, 'Gravatar.Hovercards object is instantiated' );
		$this->assertStringContainsString( 'hovercards.attach( document.body );', $inline_script, 'Gravatar.Hovercards is attached to the body' );
	}
}
