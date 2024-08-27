<?php

namespace Automattic\Gravatar\GravatarEnhanced\Proxy;

use WP_Error;
use WP_Filesystem_Base;

class ProxyCache {
	const FILTER_PROXY_URL_BASE = 'gravatar_proxy_url_base';
	const FILTER_PROXY_DIRECTORY = 'gravatar_proxy_directory';
	const FILTER_PROXY_AVATAR = 'gravatar_proxy_avatar';

	/**
	 * Check if the user has already been cached locally
	 *
	 * @param AvatarHash $hash
	 * @return bool
	 */
	public function exists_locally( $hash ) {
		$local_filename = $this->get_proxied_filename( $hash );

		return file_exists( $local_filename );
	}

	/**
	 * Download the avatar for a user
	 *
	 * @param AvatarHash $hash
	 * @return true | WP_Error
	 */
	public function copy_avatar_locally( $hash ) {
		// Set the user agent for even more privacy
		$args = [
			'user-agent' => 'Gravatar Proxy',
		];

		// Get the file from Gravatar
		$request = wp_safe_remote_get( $hash->get_remote_url(), $args );

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
			return new WP_Error( 'download-failed', 'Failed to download from Gravatar' );
		}

		$body = wp_remote_retrieve_body( $request );
		if ( ! $body ) {
			return new WP_Error( 'download-failed', 'Failed to download from Gravatar' );
		}

		$proxy_filename = $this->get_proxied_filename( $hash );

		// Make sure the directory exists
		wp_mkdir_p( dirname( $proxy_filename ) );

		// Allow others to modify the image itself
		$body = apply_filters( self::FILTER_PROXY_AVATAR, $body );

		// Store the file
		$result = file_put_contents( $proxy_filename, $body );
		if ( $result === false ) {
			return new WP_Error( 'write-failed', 'Failed to write to local file' );
		}

		return true;
	}

	/**
	 * Remove all cached avatars
	 *
	 * @return bool
	 */
	public function flush() {
		$wp_filesystem = $this->get_filesystem();

		// Delete the directory and everything in it
		return $wp_filesystem->delete( $this->get_proxied_directory(), true, 'd' );
	}

	/**
	 * Return an array of entries (file paths) that have been cached in the proxy
	 *
	 * @return string[]
	 */
	public function get_entries() {
		$wp_filesystem = $this->get_filesystem();

		$entries = $wp_filesystem->dirlist( $this->get_proxied_directory() );
		if ( $entries === false ) {
			return [];
		}

		return array_map(
			function ( $entry ) {
				return $this->get_proxied_directory() . $entry['name'];
			},
			$entries
		);
	}

	/**
	 * Return the URL for a proxied avatar
	 *
	 * @param AvatarHash $hash
	 * @return string
	 */
	public function get_local_url( $hash ) {
		return apply_filters( self::FILTER_PROXY_URL_BASE, content_url( 'uploads/gravatar/' ) ) . $hash->get_local_hash() . '.png';
	}

	/**
	 * Return the file location for a proxied avatar
	 *
	 * @param AvatarHash $hash
	 * @return string
	 */
	private function get_proxied_filename( $hash ) {
		return $this->get_proxied_directory() . $hash->get_local_hash() . '.png';
	}

	/**
	 * Return the directory for proxied avatars
	 *
	 * @return string
	 */
	private function get_proxied_directory() {
		return apply_filters( self::FILTER_PROXY_DIRECTORY, WP_CONTENT_DIR . '/uploads/gravatar/' );
	}

	/**
	 * @return WP_Filesystem_Base
	 */
	private function get_filesystem() {
		global $wp_filesystem;

		// If the filesystem has not been instantiated yet, do it here.
		if ( ! $wp_filesystem ) {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			}
			WP_Filesystem();
		}

		return $wp_filesystem;
	}
}