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
 *   default: string,
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

class Avatar {
	/**
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ], 10, 3 );
		add_filter( 'pre_get_avatar_data', [ $this, 'pre_get_avatar_data' ], 10, 2 );
		add_filter( 'get_avatar', [ $this, 'get_avatar' ], 10, 6 );
	}

	/**
	 * Main admin_init function used to hook into and register stuff and init plugin settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		register_setting( 'discussion', 'gravatar_force_default_avatar' );
		register_setting( 'discussion', 'gravatar_hide_referrer' );

		add_settings_field(
			'gravatar_force_default_avatar',
			__( 'Force default', 'gravatar-enhanced' ),
			[ $this, 'display_checkbox_setting' ],
			'discussion',
			'avatars',
			array(
				'id' => 'gravatar_force_default_avatar',
				'label' => __( 'Force default avatar', 'gravatar-enhanced' ),
				'description' => __( 'Force the default avatar to be used instead of allowing custom user avatars.', 'gravatar-enhanced' ),
			)
		);

		add_settings_field(
			'gravatar_hide_referrer',
			__( 'Avatar referrer', 'gravatar-enhanced' ),
			[ $this, 'display_checkbox_setting' ],
			'discussion',
			'avatars',
			array(
				'id' => 'gravatar_hide_referrer',
				'label' => __( 'Hide avatar referrer', 'gravatar-enhanced' ),
				'description' => __( 'Prevent the referrer being sent when displaying avatars.', 'gravatar-enhanced' ),
			)
		);
	}

	/**
	 * Callback to handle output for gravatar checkbox settings
	 *
	 * @param array{id: string, description: string, label: string} $args
	 * @return void
	 */
	public function display_checkbox_setting( $args ) {
		$value = get_option( $args['id'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $args['id'] ); ?>">
				<input value="1" name="<?php echo esc_attr( $args['id'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" type="checkbox" <?php echo checked( 1, $value, false ); ?> />

				<?php echo $args['label']; ?>

				<?php if ( $args['description'] ) : ?>
					<br />
					<span class="description"><?php echo $args['description']; ?></span>
				<?php endif; ?>
			</label>
		</p>
		<?php
		do_action( "post_{$args['id']}", $args );
	}

	/**
	 * Add the `encoding` parameter to the avatar data.
	 *
	 * @param WPAvatar $args
	 * @param WPAvatarId $id_or_email
	 * @return WPAvatar
	 */
	public function pre_get_avatar_data( $args, $id_or_email ) {
		// Avatar encoding - default to SHA256
		$args['encoding'] = apply_filters( 'gravatar_hash_encoding', 'sha256' );

		// Referrer policy on the avatar image. Default to no policy
		$hide_referrer = get_option( 'gravatar_hide_referrer' );
		$args['referrerpolicy'] = apply_filters( 'gravatar_referrerpolicy', $hide_referrer ? 'no-referrer' : '' );

		// Make all avatars use the default style
		$force_default = get_option( 'gravatar_force_default_avatar', false );
		$force_default = apply_filters( 'gravatar_force_default_avatar', $force_default );
		if ( $force_default ) {
			$args['force_default'] = true;
		}

		// Make all avatars have an alt tag
		$force_alt = apply_filters( 'avatar_force_alt', true );
		if ( $force_alt && empty( $args['alt'] ) ) {
			$user = $this->get_user_details( $id_or_email, $args );

			/* translators: %s: user name */
			$args['alt'] = sprintf( __( "%s's avatar", 'gravatar-enhanced' ), $user['name'] );
		}

		return $args;
	}

	/**
	 * Modify the avatar img tag
	 *
	 * @param string $avatar
	 * @param WPAvatarId $id_or_email
	 * @param int $size
	 * @param string $default_value
	 * @param string $alt
	 * @param WPAvatar $args
	 * @return string
	 */
	public function get_avatar( $avatar, $id_or_email, $size, $default_value, $alt, $args ) {
		// Always use https
		$avatar = str_replace( 'http://', 'https://', $avatar );

		if ( ! isset( $args['referrerpolicy'] ) || $args['referrerpolicy'] === '' ) {
			return $avatar;
		}

		$avatar = str_replace( '<img', '<img referrerpolicy="' . esc_attr( $args['referrerpolicy'] ) . '"', $avatar );

		return $avatar;
	}

	/**
	 * Replace the avatar URL with a SHA256 encoded one
	 *
	 * @param string $url
	 * @param WPAvatarId $id_or_email
	 * @param WPAvatar $args
	 * @return string
	 */
	public function get_avatar_url( $url, $id_or_email, $args ) {
		if ( ! isset( $args['encoding'] ) || $args['encoding'] === 'md5' ) {
			return $url;
		}

		// No user details, no avatar.
		if ( $args['found_avatar'] !== true ) {
			return $url;
		}

		$user = $this->get_user_details( $id_or_email, $args );
		$new_url = preg_replace( '@avatar/([a-f0-9]+)@', 'avatar/' . $user['hash'], $url );
		return (string) $new_url;
	}

	/**
	 * A copy of parts of the core WP avatar get_avatar_data() function. Used to get the email hash from whatever $id_or_email is
	 *
	 * @param WPAvatarId $id_or_email
	 * @param WPAvatar $args
	 * @return array{hash: string, name: string}
	 */
	private function get_user_details( $id_or_email, $args ) {
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
			if ( str_contains( $id_or_email, '@md5.gravatar.com' ) ) {
				// MD5 hash.
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
				/** This filter is documented in wp-includes/link-template.php */
				return apply_filters( 'get_avatar_data', $args, $id_or_email );
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

		return [
			'hash' => $email_hash,
			'name' => $user_name ? $user_name : __( 'Unknown', 'gravatar-enhanced' ),
		];
	}
}
