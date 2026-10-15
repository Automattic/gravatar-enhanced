<?php

namespace Automattic\Gravatar\GravatarEnhanced\Options;

use GravatarEnhanced\Tests\Unit\TestCase;
use Automattic\Gravatar\GravatarEnhanced\Options;

class SavedOptionsTest extends TestCase {
	public function testLoad_WhenAutoLoad() {
		// Setup
		\Brain\Monkey\Functions\expect( 'get_option' )->once()->with( 'some', [] );

		// Act
		new Options\SavedOptions( 'some', true );
	}

	public function testNotLoad_WhenNotAutoLoad() {
		// Setup
		\Brain\Monkey\Functions\expect( 'get_option' )->never();

		// Act.
		new Options\SavedOptions( 'some', false );
	}
}
