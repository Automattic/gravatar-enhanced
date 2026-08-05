<?php
/**
 * Tests for the Fediverse module.
 *
 * @package Automattic\Gravatar\GravatarEnhanced
 */

namespace Automattic\Gravatar\GravatarEnhanced\Fediverse;

use GravatarEnhanced\Tests\Unit\TestCase;
use function Brain\Monkey\Functions\expect;

require_once ROOT_DIR . '/classes/fediverse/class-fediverse.php';

/**
 * Fediverse module tests.
 */
class FediverseTest extends TestCase {

	/**
	 * The Fediverse instance.
	 *
	 * @var Fediverse
	 */
	private $fediverse;

	/**
	 * Set up each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->fediverse = new Fediverse();
	}

	/**
	 * Test init adds expected hooks.
	 *
	 * @return void
	 */
	public function testInit_WhenCalled_ThenAddsExpectedInitHook() {
		$this->fediverse->init();

		$this->assertNotFalse(
			has_action(
				'init',
				array( $this->fediverse, 'maybe_load' )
			),
			'maybe_load action is missing'
		);
	}

	/**
	 * Test maybe_load does nothing when disabled by filter.
	 *
	 * @return void
	 */
	public function testMaybeLoad_WhenDisabledByFilter_ThenDoesNotRegisterRoutes() {
		expect( 'apply_filters' )
			->once()
			->with( 'gravatar_enhanced_fediverse_module_enabled', true )
			->andReturn( false );

		expect( 'add_action' )->never();

		$this->fediverse->maybe_load();
	}

	/**
	 * Test maybe_load does nothing when option is disabled.
	 *
	 * @return void
	 */
	public function testMaybeLoad_WhenOptionDisabled_ThenDoesNotRegisterRoutes() {
		expect( 'apply_filters' )
			->once()
			->with( 'gravatar_enhanced_fediverse_module_enabled', true )
			->andReturn( true );

		expect( 'get_option' )
			->once()
			->with( 'gravatar_fediverse_hovercards', true )
			->andReturn( false );

		expect( 'add_action' )->never();

		$this->fediverse->maybe_load();
	}

	/**
	 * Test maybe_load registers REST routes when enabled.
	 *
	 * @return void
	 */
	public function testMaybeLoad_WhenEnabled_ThenRegistersRestRoutes() {
		expect( 'apply_filters' )
			->once()
			->with( 'gravatar_enhanced_fediverse_module_enabled', true )
			->andReturn( true );

		expect( 'get_option' )
			->once()
			->with( 'gravatar_fediverse_hovercards', true )
			->andReturn( true );

		expect( 'add_action' )
			->once()
			->with( 'rest_api_init', array( $this->fediverse, 'register_rest_routes' ) );

		$this->fediverse->maybe_load();
	}

	/**
	 * Test uninstall deletes the option.
	 *
	 * @return void
	 */
	public function testUninstall_WhenCalled_ThenDeletesOption() {
		expect( 'delete_option' )
			->once()
			->with( 'gravatar_fediverse_hovercards' );

		$this->fediverse->uninstall();
	}

	/**
	 * Test REST namespace constant.
	 *
	 * @return void
	 */
	public function testRestRouteConstants_AreCorrect() {
		$this->assertSame( 'gravatar-enhanced/v1', Fediverse::REST_NAMESPACE );
		$this->assertSame( '/fediverse/lookup', Fediverse::REST_ROUTE_LOOKUP );
	}

	/**
	 * Test filter constant.
	 *
	 * @return void
	 */
	public function testFilterConstants_AreCorrect() {
		$this->assertSame( 'gravatar_enhanced_fediverse_module_enabled', Fediverse::FILTER_FEDIVERSE_MODULE_ENABLED );
	}
}
