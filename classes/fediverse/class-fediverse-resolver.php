<?php
/**
 * Fediverse profile resolver.
 *
 * @package Automattic\Gravatar\GravatarEnhanced
 */

namespace Automattic\Gravatar\GravatarEnhanced\Fediverse;

use WP_Error;

/**
 * Resolves Fediverse profile URLs via WebFinger and ActivityPub.
 */
class Fediverse_Resolver {
	const TIMEOUT = 5;

	const WEBFINGER_PATH = '/.well-known/webfinger';

	const ACTOR_REL = 'self';

	const ACTIVITYPUB_TYPE = 'application/activity+json';

	/**
	 * Resolve a Fediverse profile URL to normalized data.
	 *
	 * @param string $url The profile URL.
	 * @return array{display_name: string, summary: string, avatar_url: string, profile_url: string, follow_url: string}|WP_Error
	 */
	public function resolve( $url ) {
		$parsed = wp_parse_url( $url );
		if ( ! $parsed || empty( $parsed['host'] ) ) {
			return new WP_Error( 'fediverse_invalid_url', __( 'Invalid URL: missing host.', 'gravatar-enhanced' ), array( 'status' => 400 ) );
		}

		$host = $parsed['host'];
		$path = isset( $parsed['path'] ) ? $parsed['path'] : '';

		if ( ! empty( $path ) && '@' === $path[0] ) {
			$acct = $this->acct_from_at_url( $url, $host, $path );
		} elseif ( ! empty( $path ) && str_contains( $path, '/@' ) ) {
			$acct = $this->acct_from_web_url( $host, $path );
		} else {
			$acct = $this->acct_from_url( $host, $path );
		}

		if ( empty( $acct ) ) {
			return new WP_Error( 'fediverse_unsupported', __( 'Could not extract Fediverse handle from URL.', 'gravatar-enhanced' ), array( 'status' => 400 ) );
		}

		$webfinger_url = $this->build_webfinger_url( $host, $acct );
		$response      = $this->http_get( $webfinger_url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return new WP_Error( 'fediverse_webfinger_empty', __( 'WebFinger response was empty.', 'gravatar-enhanced' ), array( 'status' => 502 ) );
		}

		$data = json_decode( $body, true );
		if ( empty( $data ) || ! is_array( $data ) ) {
			return new WP_Error( 'fediverse_webfinger_invalid', __( 'WebFinger response was invalid.', 'gravatar-enhanced' ), array( 'status' => 502 ) );
		}

		$actor_url = $this->find_actor_link( $data );
		if ( empty( $actor_url ) ) {
			return new WP_Error( 'fediverse_no_actor', __( 'No ActivityPub actor found for this profile.', 'gravatar-enhanced' ), array( 'status' => 404 ) );
		}

		$actor = $this->fetch_actor( $actor_url );

		if ( is_wp_error( $actor ) ) {
			return $actor;
		}

		return $this->normalize_profile( $actor, $url );
	}

	/**
	 * Extract acct: from a Mastodon-style @ URL.
	 *
	 * @param string $url The profile URL.
	 * @param string $host The host.
	 * @param string $path The path.
	 * @return string
	 */
	private function acct_from_at_url( $url, $host, $path ) {
		$path = trim( $path, '/' );

		$path  = preg_replace( '/^@/', '', $path );
		$parts = explode( '/', $path ?? '' );
		$path  = $parts[0];

		return '@' . $path . '@' . $host;
	}

	/**
	 * Extract acct: from a web-style URL (e.g. /@user or /users/user).
	 *
	 * @param string $host The host.
	 * @param string $path The path.
	 * @return string
	 */
	private function acct_from_web_url( $host, $path ) {
		$path = trim( $path, '/' );

		if ( preg_match( '/^@([^\/]+)/', $path, $matches ) ) {
			return '@' . $matches[1] . '@' . $host;
		}

		if ( preg_match( '/^users\/(.+)$/', $path, $matches ) ) {
			return '@' . $matches[1] . '@' . $host;
		}

		return '';
	}

	/**
	 * Extract acct: from a generic URL.
	 *
	 * @param string $host The host.
	 * @param string $path The path.
	 * @return string
	 */
	private function acct_from_url( $host, $path ) {
		$path = trim( $path, '/' );

		if ( empty( $path ) ) {
			return '';
		}

		$segments = explode( '/', $path );
		$username = end( $segments );

		return '@' . $username . '@' . $host;
	}

	/**
	 * Build the WebFinger URL.
	 *
	 * @param string $host The host.
	 * @param string $acct The account identifier.
	 * @return string
	 */
	private function build_webfinger_url( $host, $acct ) {
		$resource = 'acct:' . ltrim( $acct, '@' );

		return 'https://' . $host . self::WEBFINGER_PATH . '?resource=' . rawurlencode( $resource );
	}

	/**
	 * Find the ActivityPub actor link from WebFinger data.
	 *
	 * @param array<string, mixed> $webfinger The WebFinger response data.
	 * @return string
	 */
	private function find_actor_link( $webfinger ) {
		if ( empty( $webfinger['links'] ) || ! is_array( $webfinger['links'] ) ) {
			return '';
		}

		foreach ( $webfinger['links'] as $link ) {
			if ( empty( $link['rel'] ) || empty( $link['href'] ) ) {
				continue;
			}

			if ( self::ACTOR_REL === $link['rel'] && ! empty( $link['type'] ) && self::ACTIVITYPUB_TYPE === $link['type'] ) {
				return esc_url_raw( $link['href'] );
			}
		}

		foreach ( $webfinger['links'] as $link ) {
			if ( empty( $link['rel'] ) || empty( $link['href'] ) ) {
				continue;
			}

			if ( self::ACTOR_REL === $link['rel'] ) {
				return esc_url_raw( $link['href'] );
			}
		}

		return '';
	}

	/**
	 * Fetch the ActivityPub actor JSON.
	 *
	 * @param string $actor_url The actor URL.
	 * @return array<string, mixed>|WP_Error
	 */
	private function fetch_actor( $actor_url ) {
		$response = wp_remote_get(
			$actor_url,
			array(
				'timeout' => self::TIMEOUT,
				'headers' => array(
					'Accept' => 'application/activity+json, application/ld+json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return new WP_Error( 'fediverse_actor_error', __( 'Failed to fetch ActivityPub actor.', 'gravatar-enhanced' ), array( 'status' => 502 ) );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return new WP_Error( 'fediverse_actor_empty', __( 'ActivityPub actor response was empty.', 'gravatar-enhanced' ), array( 'status' => 502 ) );
		}

		$data = json_decode( $body, true );
		if ( empty( $data ) || ! is_array( $data ) ) {
			return new WP_Error( 'fediverse_actor_invalid', __( 'ActivityPub actor response was invalid.', 'gravatar-enhanced' ), array( 'status' => 502 ) );
		}

		return $data;
	}

	/**
	 * Normalize the ActivityPub actor data into a profile array.
	 *
	 * @param array<string, mixed> $actor The actor data.
	 * @param string               $original_url The original requested URL.
	 * @return array{display_name: string, summary: string, avatar_url: string, profile_url: string, follow_url: string}
	 */
	private function normalize_profile( $actor, $original_url ) {
		$display_name = '';
		if ( ! empty( $actor['name'] ) ) {
			$display_name = sanitize_text_field( $actor['name'] );
		} elseif ( ! empty( $actor['preferredUsername'] ) ) {
			$display_name = sanitize_text_field( $actor['preferredUsername'] );
		}

		$summary = '';
		if ( ! empty( $actor['summary'] ) ) {
			$summary = wp_kses_post( $actor['summary'] );
		}

		$avatar_url = '';
		if ( ! empty( $actor['icon']['url'] ) ) {
			$avatar_url = esc_url_raw( $actor['icon']['url'] );
		}

		$profile_url = '';
		if ( ! empty( $actor['url'] ) ) {
			$profile_url = esc_url_raw( $actor['url'] );
		} else {
			$profile_url = esc_url_raw( $original_url );
		}

		$follow_url = '';
		if ( ! empty( $actor['id'] ) ) {
			$follow_url = esc_url_raw( $actor['id'] );
		}

		return array(
			'display_name' => $display_name,
			'summary'      => $summary,
			'avatar_url'   => $avatar_url,
			'profile_url'  => $profile_url,
			'follow_url'   => $follow_url,
		);
	}

	/**
	 * Make an HTTP GET request.
	 *
	 * @param string $url The URL to fetch.
	 * @return array<string, mixed>|WP_Error
	 */
	private function http_get( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => self::TIMEOUT,
				'headers' => array(
					'Accept' => 'application/jrd+json, application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return new WP_Error( 'fediverse_http_error', __( 'HTTP error during lookup.', 'gravatar-enhanced' ), array( 'status' => 502 ) );
		}

		return $response;
	}
}
