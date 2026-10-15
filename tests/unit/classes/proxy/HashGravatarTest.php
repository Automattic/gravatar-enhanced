<?php

namespace Automattic\Gravatar\GravatarEnhanced\Proxy;

use GravatarEnhanced\Tests\Unit\TestCase;

require_once ROOT_DIR . '/classes/proxy/class-hash.php';

class HashGravatarTest extends TestCase {
	public function test_WithEmptyString_ReturnsEmpty() {
		// Act
		$hash = new LocalAvatarHash( '' );

		// Assert
		$this->assertEquals( '', $hash->get_local_hash() );
		$this->assertEquals( '', $hash->get_remote_url() );
	}

	public function test_WithNoPath_ReturnsUrl() {
		// Arrange
		$url = 'https://domain.com/';

		// Act
		$hash = new LocalAvatarHash( $url );

		// Assert.
		$this->assertEquals( '', $hash->get_local_hash() );
		$this->assertEquals( $url, $hash->get_remote_url() );
	}

	public function test_WithPath_ReturnsPath() {
		// Arrange
		$url = 'https://domain.com/test/hash';

		// Act
		$hash = new LocalAvatarHash( $url );

		// Assert.
		$this->assertEquals( 'hash', $hash->get_local_hash() );
		$this->assertEquals( $url, $hash->get_remote_url() );
	}

	public function test_WithPathAndParams_ReturnsPathWithSerializedParams() {
		// Arrange
		$url = 'https://domain.com/test/hash?s=64&d=identicon&other=param';
		\Brain\Monkey\Functions\expect( 'sanitize_file_name' )->once()->with( '64' )->andReturn( '64' );
		\Brain\Monkey\Functions\expect( 'sanitize_file_name' )->once()->with( 'identicon' )->andReturn( 'identicon' );

		// Act
		$hash = new LocalAvatarHash( $url );

		// Assert.
		$this->assertEquals( 'hash-s-64-d-identicon', $hash->get_local_hash() );
		$this->assertEquals( $url, $hash->get_remote_url() );
	}
}
