<?php

namespace Automattic\Gravatar\GravatarEnhanced\Proxy;

/**
 * Converts a Gravatar URL into a local hash. The Gravatar hash is used as the base, and the query parameters are serialized into the filename.
 */
class LocalAvatarHash extends AvatarHash {
	/**
	 * Creates the local hash for the Gravatar URL. This is the Gravatar hash, with the query parameters serialized into the filename.
	 * This ensures that various sizes and default images are cached separately.
	 *
	 * @param string $gravatar_url
	 *
	 * @return void
	 */
	public function __construct( $gravatar_url ) {
		$url = parse_url( $gravatar_url );

		if ( ! empty( $url['path'] ) ) {
			$path = explode( '/', $url['path'] );

			$this->local_hash = $path[ count( $path ) - 1 ];

			// Serialize the query parameters to the filename
			if ( ! empty( $url['query'] ) ) {
				parse_str( $url['query'], $query );
				$parts = [];

				if ( isset( $query['s'] ) && is_string( $query['s'] ) ) {
					$parts[] = 's-' . sanitize_file_name( $query['s'] );
				}

				if ( isset( $query['d'] ) && is_string( $query['d'] ) ) {
					$parts[] = 'd-' . sanitize_file_name( $query['d'] );
				}

				if ( count( $parts ) > 0 ) {
					$this->local_hash .= '-' . implode( '-', $parts );
				}
			}
		}

		$this->remote_url = $gravatar_url;
	}
}
