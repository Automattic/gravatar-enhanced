<?php

namespace Automattic\Gravatar\GravatarEnhanced\Avatar;

use Automattic\Gravatar\GravatarEnhanced\Module;

require_once __DIR__ . '/class-avatar-id.php';
require_once __DIR__ . '/class-avatar-options.php';
require_once __DIR__ . '/class-avatar-preferences.php';

/**
 * @phpstan-import-type WPAvatarId from AvatarId
 * @phpstan-import-type WPAvatar from AvatarId
 */
class Avatar implements Module {
	const FILTER_HASH_ENCODING = 'gravatar_hash_encoding';
	const FILTER_REFERRER_POLICY = 'gravatar_referrer_policy';

	/**
	 * @var Options
	 */
	private $options;

	/**
	 * @param Preferences $preferences
	 */
	public function __construct( Preferences $preferences ) {
		$this->options = $preferences->get_options();
	}

	/**
	 * @return void
	 */
	public function init() {
		add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ], 10, 3 );
		add_filter( 'pre_get_avatar_data', [ $this, 'pre_get_avatar_data' ], 10, 2 );
		add_filter( 'get_avatar', [ $this, 'get_avatar' ], 10, 6 );
		add_filter( 'avatar_defaults', [ $this, 'avatar_defaults' ] );
	}

	/**
	 * @param array<string, string> $defaults
	 * @return array<string, string>
	 */
	public function avatar_defaults( $defaults ) {
		$defaults = array_merge(
			[
				'initials' => __( 'Initials (Generated, beta)', 'gravatar-enhanced' ),
				'color'    => __( 'Color (Generated, beta)', 'gravatar-enhanced' ),
			],
			$defaults
		);
		unset( $defaults['blank'] );

		$defaults['blank'] = __( 'Blank (Default)', 'gravatar-enhanced' );
		return $defaults;
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
		$args['encoding'] = apply_filters( self::FILTER_HASH_ENCODING, 'sha256' );

		// Referrer policy on the avatar image. Default to no policy
		$args['referrerpolicy'] = apply_filters( self::FILTER_REFERRER_POLICY, true ) ? 'no-referrer' : '';

		// Make all avatars use the default style
		if ( $this->options->force_default_avatar ) {
			$args['force_default'] = true;
		}

		// Make all avatars have an alt tag
		if ( $this->options->force_alt && empty( $args['alt'] ) ) {
			$avatar_id = new AvatarId( $id_or_email, $args );

			/* translators: %s: user name */
			$args['alt'] = sprintf( __( "%s's avatar", 'gravatar-enhanced' ), $avatar_id->get_name() );
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
		// Always use https
		if ( is_ssl() ) {
			$url = str_replace( 'http://', 'https://', $url );
		}

		if ( ! isset( $args['encoding'] ) || $args['encoding'] === 'md5' ) {
			return $url;
		}

		// No user details, no avatar.
		if ( $args['found_avatar'] !== true ) {
			return $url;
		}

		// If the default is set to 'initials' and we have a comment, use the comment author name
		if ( isset( $args['default'] ) && $args['default'] === 'initials' && $id_or_email instanceof \WP_Comment ) {
			$url .= '&name=' . rawurlencode( $id_or_email->comment_author );
		}

		$user = new AvatarId( $id_or_email, $args );
		$new_url = preg_replace( '@avatar/([a-f0-9]+)@', 'avatar/' . $user->get_hash(), $url );
		return (string) $new_url;
	}

	/**
	 * @return void
	 */
	public function uninstall() {}
}
