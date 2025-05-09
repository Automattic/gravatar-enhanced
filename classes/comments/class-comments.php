<?php

namespace Automattic\Gravatar\GravatarEnhanced\Comments;

use Automattic\Gravatar\GravatarEnhanced\Module;

require_once __DIR__ . '/class-comments-options.php';
require_once __DIR__ . '/class-comments-preferences.php';

class Comments implements Module {
	const OPTION_COMMENTS = 'gravatar_comments';

	/**
	 * @var Options
	 */
	private $options;

	/**
	 * @param Preferences $preferences
	 */
	public function __construct( $preferences ) {
		$this->options = $preferences->get_options();
	}

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'maybe_load' ] );
	}

	/**
	 * Load the module if it's not disabled.
	 *
	 * @return void
	 */
	public function maybe_load() {
		// Bail if the module is disabled.
		if ( ! $this->is_module_enabled() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
		add_action( 'comment_form_field_email', [ $this, 'comment_form_field_email' ] );
		add_filter( 'comment_form_fields', [ $this, 'comment_form_fields' ] );
	}

	/**
	 * @return void
	 */
	public function uninstall() {
	}

	/**
	 * Check if the module is disabled, by either a filter or a detected incompatibility.
	 *
	 * @return bool
	 */
	private function is_module_enabled() {
		// Check if Jetpack is active and has the comments module enabled.
		if ( class_exists( '\Jetpack' ) && \Jetpack::is_module_active( 'comments' ) ) {
			return false; // Disabled due to Jetpack comments module.
		}

		if ( get_template() === 'twentyeleven' ) {
			return false; // Disabled due to Twenty Eleven theme.
		}

		return $this->options->enabled;
	}

	/**
	 * Rearrange the order so the email comes before name
	 *
	 * @param array<string, string> $fields
	 * @return array<string, string>
	 */
	public function comment_form_fields( array $fields ): array {
		if ( isset( $fields['email'] ) && isset( $fields['author'] ) ) {
			$email_field = $fields['email'];
			unset( $fields['email'] );
			$reordered_fields = [];

			foreach ( $fields as $key => $value ) {
				if ( $key === 'author' ) {
					$reordered_fields['email'] = $email_field;
				}
				$reordered_fields[ $key ] = $value;
			}

			return $reordered_fields;
		}

		return $fields;
	}

	/**
	 * Enqueue a JS file.
	 *
	 * @return void
	 */
	public function wp_enqueue_scripts() {
		// Is this a commentable post?
		if ( ! is_singular() || ! comments_open() ) {
			return;
		}

		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/comments.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_script( 'gravatar-enhanced-comments', plugins_url( 'build/comments.js', GRAVATAR_ENHANCED_PLUGIN_FILE ), $assets['dependencies'], $assets['version'], true );

		$theme = wp_get_theme();
		$override_file = __DIR__ . '/theme-override/' . $theme->get_template() . '.css';
		if ( file_exists( $override_file ) ) {
			wp_register_style( 'gravatar-enhanced-comments-override', plugins_url( 'classes/comments/theme-override/' . $theme->get_template() . '.css', GRAVATAR_ENHANCED_PLUGIN_FILE ), [], $assets['version'] );
			wp_enqueue_style( 'gravatar-enhanced-comments-override' );
		}

		wp_register_style( 'gravatar-enhanced-comments', plugins_url( 'build/style-comments.css', GRAVATAR_ENHANCED_PLUGIN_FILE ), [], $assets['version'] );
		wp_enqueue_style( 'gravatar-enhanced-comments' );

		$comment_data = [
			'locale' => $this->get_gravatar_locale( get_locale() ),
		];

		// Check if user is logged in
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();

			$comment_data['email'] = $current_user->user_email;
			$comment_data['locale'] = $this->get_gravatar_locale( get_user_locale( $current_user ) );
		}

		wp_localize_script(
			'gravatar-enhanced-comments',
			'gravatarEnhancedComments',
			$comment_data
		);
	}

	/**
	 * Get the locale for the user.
	 *
	 * @param string $locale
	 * @return string
	 */
	private function get_gravatar_locale( $locale ) {
		$current_user_locale = strtolower( $locale );

		// Gravatar only wants the first part of a locale, so we strip the country code unless it's one of the exceptions
		$exceptions = [
			'zh_tw',
			'fr_ca',
		];

		if ( in_array( $current_user_locale, $exceptions, true ) ) {
			return str_replace( '_', '-', $current_user_locale );
		}

		$current_user_locale = (string) preg_replace( '/[_-].*$/', '', $current_user_locale );
		$current_user_locale = str_replace( 'zh', 'cn', $current_user_locale );
		$current_user_locale = str_replace( 'en', '', $current_user_locale );

		return $current_user_locale;
	}

	/**
	 * Output the Gravatar-enhanced comments form field for logged-out user.
	 *
	 * @param string $field
	 * @return void
	 */
	public function comment_form_field_email( $field ) {
		$grav_field = '<span class="gravatar-enhanced-profile">';
		$grav_field .= '<img src="" alt="' . esc_attr( __( 'Gravatar profile', 'gravatar-enhanced' ) ) . '" />';
		$grav_field .= '</span>';

		$field = preg_replace( '@</(\w+)>$@', $grav_field . '</$1>', $field );

		echo $field;
	}
}
