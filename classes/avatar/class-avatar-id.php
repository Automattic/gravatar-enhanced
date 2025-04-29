<?php

namespace Automattic\Gravatar\GravatarEnhanced\Avatar;

use WP_User;
use WP_Post;
use WP_Comment;

/**
 * @phpstan-type WPAvatarId string|int|WP_User|WP_Post|WP_Comment
 * @phpstan-type WPAvatar array{
 *   size: number,
 *   height: number,
 *   width: number,
 *   default?: string,
 *   force_default: boolean,
 *   rating: string,
 *   scheme: string,
 *   url: string,
 *   alt: string,
 *   found_avatar: boolean,
 *   encoding?: string,
 *   referrerpolicy?: string
 * }
 */
class AvatarId {
	private string $email_hash;
	private string $name;

	/**
	 * A copy of parts of the core WP avatar get_avatar_data() function. Used to get the email hash from whatever $id_or_email is
	 *
	 * @param WPAvatarId $id_or_email
	 * @param WPAvatar $args
	 * @return void
	 */
	public function __construct( $id_or_email, $args ) {
		if ( is_object( $id_or_email ) && isset( $id_or_email->comment_ID ) && $id_or_email instanceof WP_Comment ) {
			$id_or_email = get_comment( $id_or_email );
		}

		$user = false;
		$user_name = '';
		$email = false;
		$email_hash = '';

		// Process the user identifier.
		if ( is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'id', absint( $id_or_email ) );
		} elseif ( is_string( $id_or_email ) ) {
			if ( str_contains( $id_or_email, '@md5.gravatar.com' ) || str_contains( $id_or_email, '@sha256.gravatar.com' ) ) {
				// MD5 or SHA256 hash.
				list( $email_hash ) = explode( '@', $id_or_email );
			} else {
				// Email address.
				$email = $id_or_email;
			}
		} elseif ( $id_or_email instanceof WP_User ) {
			// User object.
			$user = $id_or_email;
		} elseif ( $id_or_email instanceof WP_Post ) {
			// Post object.
			$user = get_user_by( 'id', (int) $id_or_email->post_author );
		} elseif ( $id_or_email instanceof WP_Comment ) {
			if ( ! is_avatar_comment_type( get_comment_type( $id_or_email ) ) ) {
				$args['url'] = false;
			}

			if ( ! empty( $id_or_email->user_id ) ) {
				$user = get_user_by( 'id', (int) $id_or_email->user_id );
			}
			// @phpstan-ignore-next-line
			if ( ( ! $user || is_wp_error( $user ) ) && ! empty( $id_or_email->comment_author_email ) ) {
				$email = $id_or_email->comment_author_email;
				$user_name = $id_or_email->comment_author;
			}
		}

		if ( $user instanceof WP_User ) {
			$user_name = $user->display_name;
		}

		if ( ! $email_hash ) {
			if ( $user ) {
				$email = $user->user_email;
			}

			if ( $email ) {
				$email_hash = hash( 'sha256', strtolower( trim( $email ) ) );
			}
		}

		$this->email_hash = $email_hash;
		$this->name = $user_name ? $user_name : __( 'Unknown', 'gravatar-enhanced' );
	}

	/**
	 * Get the email hash
	 *
	 * @return string
	 */
	public function get_hash() {
		return $this->email_hash;
	}

	/**
	 * Get the name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}
}
