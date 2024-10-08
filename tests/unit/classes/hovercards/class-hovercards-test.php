<?php

namespace Automattic\Gravatar\GravatarEnhanced\Hovercards;

use GravatarEnhanced\Tests\Unit\TestCase;
use function Brain\Monkey\Functions\expect;

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

	public function testInit_WhenCalled_ThenAddsExpectedInitHook() {
		// Act.
		$this->hovercards->init();

		// Assert.
		$this->assertNotFalse(
			has_action(
				'init',
				[ $this->hovercards, 'maybe_load' ]
			),
			'maybe_load action is missing'
		);
	}

	public function testMaybeLoad_WhenDisabledByFilter_ThenDoesNotAddAnyHook() {
		// Arrange.
		\Brain\Monkey\Functions\expect( 'apply_filters' )
			->once()
			->with( 'gravatar_enhanced_hovercards_module_enabled', true )
			->andReturn( false );
		\Brain\Monkey\Functions\expect( 'add_action' )->never();
		\Brain\Monkey\Functions\expect( 'add_filter' )->never();

		// Act.
		$this->hovercards->maybe_load();

		// Assert – implicit in arrange. No calls to add_action or add_filter.
	}

	public function testMaybeLoad_WhenJetpackHovercardModuleISActive_ThenDoesNotAddAnyHook() {
		// Arrange.
		$jetpack_mock = \Mockery::mock( 'alias:\Jetpack' );
		$jetpack_mock
			->expects( 'is_module_active' )
			->once()
			->with( 'gravatar-hovercards' )
			->andReturn( true );
		\Brain\Monkey\Functions\expect( 'add_action' )->never();
		\Brain\Monkey\Functions\expect( 'add_filter' )->never();

		// Act.
		$this->hovercards->maybe_load();

		// Assert – implicit in arrange. No calls to add_action or add_filter.
	}

	public function testMaybeAddHovercards_WhenDisabled_ThenDoesNothing() {
		// Arrange.
		\Brain\Monkey\Functions\expect( 'get_option' )->once()->with( 'gravatar_hovercards', true )->andReturn( false );
		\Brain\Monkey\Functions\expect( 'wp_enqueue_script' )->never();
		\Brain\Monkey\Functions\expect( 'wp_enqueue_style' )->never();
		\Brain\Monkey\Functions\expect( 'wp_add_inline_script' )->never();

		// Act.
		$this->hovercards->maybe_add_hovercards();

		// Assert – implicit in arrange.
	}

	public function testGravatarEnhancedAddHovercards_WhenEnabled_ThenEnqueuesScripts() {
		$asset = require dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/hovercards.asset.php';

		// Arrange.
		\Brain\Monkey\Functions\expect( 'plugins_url' )->zeroOrMoreTimes()->andReturnFirstArg(); // Let's just return the first argument, which is the file name.
		\Brain\Monkey\Functions\expect( 'get_option' )->once()->with( 'gravatar_hovercards', true )->andReturn( true );
		\Brain\Monkey\Functions\expect( 'wp_enqueue_script' )->once()->with( 'gravatar-enhanced-hovercards', 'build/hovercards.js', [], $asset['version'], true );
		\Brain\Monkey\Functions\expect( 'wp_enqueue_style' )->once()->with( 'gravatar-enhanced-hovercards' );
		\Brain\Monkey\Functions\expect( 'wp_register_style' )->once()->with( 'gravatar-enhanced-hovercards', 'build/style-hovercards.css', [], $asset['version'] );

		// Act.
		$this->hovercards->maybe_add_hovercards();
	}
}
