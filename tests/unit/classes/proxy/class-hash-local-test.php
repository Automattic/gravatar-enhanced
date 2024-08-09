<?php

namespace Automattic\Gravatar\GravatarEnhanced\Proxy;

use GravatarEnhanced\Tests\Unit\TestCase;

require_once ROOT_DIR . '/classes/proxy/class-hash.php';

class HashLocalTest extends TestCase {
	public function test_ReturnsSha256Hash() {
		// Arrange
		$url = 'https://domain.com/test/hash?s=64&d=identicon&other=param';
		$version = 1;
		$site_id = 2;

		// Act
		$hash = new PrivateAvatarHash( $url, $version, $site_id );

		// Assert.
		$this->assertEquals( 'eba5bf1538a4c7b8bf5489e940ea5d40ec7f18e5ede917ec41a436ac004bdb1e', $hash->get_local_hash() );
		$this->assertEquals( $url, $hash->get_remote_url() );
	}
}
