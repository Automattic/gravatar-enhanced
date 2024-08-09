<?php

namespace Automattic\Gravatar\GravatarEnhanced\Avatar;

use Automattic\Gravatar\GravatarEnhanced\Settings;

require_once __DIR__ . '/class-avatar-id.php';

/**
 * @phpstan-import-type WPAvatarId from Avatar
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
	const OPTION_FORCE_DEFAULT = 'gravatar_force_default_avatar';
	const OPTION_HIDE_REFERRER = 'gravatar_hide_referrer';

	use Settings\SettingsCheckbox;

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
		register_setting( 'discussion', self::OPTION_FORCE_DEFAULT );
		register_setting( 'discussion', self::OPTION_HIDE_REFERRER );

		add_settings_field(
			self::OPTION_FORCE_DEFAULT,
			__( 'Force default', 'gravatar-enhanced' ),
			[ $this, 'display_checkbox_setting' ],
			'discussion',
			'avatars',
			array(
				'id' => self::OPTION_FORCE_DEFAULT,
				'label' => __( 'Force default avatar', 'gravatar-enhanced' ),
				'description' => __( 'Force the default avatar to be used instead of allowing custom user avatars.', 'gravatar-enhanced' ),
			)
		);

		add_settings_field(
			self::OPTION_HIDE_REFERRER,
			__( 'Avatar referrer', 'gravatar-enhanced' ),
			[ $this, 'display_checkbox_setting' ],
			'discussion',
			'avatars',
			array(
				'id' => self::OPTION_HIDE_REFERRER,
				'label' => __( 'Hide avatar referrer', 'gravatar-enhanced' ),
				'description' => __( 'Prevent the referrer being sent when displaying avatars.', 'gravatar-enhanced' ),
			)
		);
	}

	/**
	 * Remove the avatar options
	 *
	 * @return void
	 */
	public function uninstall() {
		delete_option( 'gravatar_force_default_avatar' );
		delete_option( 'gravatar_hide_referrer' );
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
		$hide_referrer = get_option( self::OPTION_HIDE_REFERRER );
		$args['referrerpolicy'] = apply_filters( 'gravatar_referrerpolicy', $hide_referrer ? 'no-referrer' : '' );

		// Make all avatars use the default style
		$force_default = get_option( self::OPTION_FORCE_DEFAULT, false );
		$force_default = apply_filters( 'gravatar_force_default_avatar', $force_default );
		if ( $force_default ) {
			$args['force_default'] = true;
		}

		// Make all avatars have an alt tag
		$force_alt = apply_filters( 'avatar_force_alt', true );
		if ( $force_alt && empty( $args['alt'] ) ) {
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
		$url = str_replace( 'http://', 'https://', $url );

		if ( ! isset( $args['encoding'] ) || $args['encoding'] === 'md5' ) {
			return $url;
		}

		// No user details, no avatar.
		if ( $args['found_avatar'] !== true ) {
			return $url;
		}

		$user = new AvatarId( $id_or_email, $args );
		$new_url = preg_replace( '@avatar/([a-f0-9]+)@', 'avatar/' . $user->get_hash(), $url );
		return (string) $new_url;
	}
}
