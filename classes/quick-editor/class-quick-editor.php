<?php

namespace Automattic\Gravatar\GravatarEnhanced\QuickEditor;

use Automattic\Gravatar\GravatarEnhanced\Settings;

class QuickEditor {
	use Settings\SettingsCheckbox;

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Remove the options
	 *
	 * @return void
	 */
	public function uninstall() {
	}

	/**
	 * Main admin_init function used to hook into and register stuff and init plugin settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ], 15 );
			add_action( 'admin_enqueue_scripts', [ $this, 'add_quick_editor' ] );
			add_filter( 'user_profile_picture_description', [ $this, 'add_quick_editor_link' ] );
		}
	}

	/**
	 * Add a cache busting parameter to the Gravatar URL on the profile page. This ensures it is always up to date.
	 *
	 * @param string $url
	 * @return string
	 */
	public function get_avatar_url( $url ) {
		return add_query_arg( 't', time(), $url );
	}

	/**
	 * Replace the text link with a button
	 *
	 * @return string
	 */
	public function add_quick_editor_link() {
		return '<button type="button" class="button button-secondary" id="edit-gravatar">' . esc_html__( 'Change Gravatar', 'gravatar-enhanced' ) . '</button>';
	}

	/**
	 * Initialise editor, if enabled.
	 *
	 * @return void
	 */
	public function add_quick_editor() {
		$current_user = wp_get_current_user();

		$current_user_email = strtolower( $current_user->user_email );
		$current_user_locale = get_user_locale( $current_user );

		// Gravatar only wants the first part of a locale, so we strip the country code.
		$current_user_locale = (string) preg_replace( '/_.*$/', '', $current_user_locale );

		$settings = [
			'email' => $current_user_email,
			'locale' => $current_user_locale === 'en' ? '' : $current_user_locale,
			'hash' => hash( 'sha256', $current_user_email ),
		];

		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/quick-editor.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_script( 'gravatar-enhanced-qe', plugins_url( 'build/quick-editor.js', GRAVATAR_ENHANCED_PLUGIN_FILE ), $assets['dependencies'], $assets['version'], true );
		wp_localize_script( 'gravatar-enhanced-qe', 'geQuickEditor', $settings );

		// phpcs:ignore
		wp_register_style( 'gravatar-enhanced-qe', plugins_url( 'build/style-quick-editor.css', GRAVATAR_ENHANCED_PLUGIN_FILE ), [], $assets['version'] );
		wp_enqueue_style( 'gravatar-enhanced-qe' );
	}
}
