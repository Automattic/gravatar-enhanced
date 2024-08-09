<?php

namespace Automattic\Gravatar\GravatarEnhanced\Proxy;

/**
 * Converts a Gravatar URL into a private hash for local caching. There is no reverse lookup.
 */
class PrivateAvatarHash extends AvatarHash {
	/**
	 * Creates the private local hash for the Gravatar URL. This is a SHA256 of the entire Gravatar URL (including all parameters), along with a version
	 * number (the time), and a site ID.
	 *
	 * The version number is set to the first time the plugin is used. The site ID can be overridden by the user should they want to make it even more unique.
	 *
	 * @param string $gravatar_url Gravatar URL with all parameters
	 * @param string $cache_version Cache version. Change to flush the cache quickly
	 * @param string $cache_id Unique ID for the cache
	 *
	 * @return void
	 */
	public function __construct( $gravatar_url, $cache_version, $cache_id ) {
		$parts = [
			$gravatar_url,
			$cache_version,
			$cache_id,
		];

		$this->local_hash = hash( 'sha256', implode( '-', $parts ) );
		$this->remote_url = $gravatar_url;
	}
}
