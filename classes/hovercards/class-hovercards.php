<?php

namespace Automattic\Gravatar\GravatarEnhanced\Hovercards;

use Automattic\Gravatar\GravatarEnhanced\Settings;

class Hovercards {
	use Settings\SettingsCheckbox;

	const GRAVATAR_ENHANCED_HOVERCARD_URL = 'https://unpkg.com/@gravatar-com/hovercards@0.8.0';
	const GRAVATAR_ENHANCED_HOVERCARD_STYLES_URL = 'https://unpkg.com/@gravatar-com/hovercards@0.8.0/dist/style.css';
	const GRAVATAR_ENHANCED_HOVERCARD_VERSION = 'e';

	/**
	 * @var string
	 */
	const OPTION_HOVERCARDS = 'gravatar_hovercards';

	/**
	 * @return void
	 */
	public function init() {
		// Delay loading until 'init' to access the Jetpack class and check if the module needs disabling.
		add_action( 'init', [ $this, 'maybe_load' ] );
	}

	/**
	 * Load the hovercards module if it's not disabled.
	 *
	 * @return void
	 */
	public function maybe_load() {
		// Bail if the module is disabled.
		if ( $this->is_module_disabled() ) {
			return;
		}
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_add_hovercards' ] );
	}

	/**
	 * Remove the hovercard options
	 *
	 * @return void
	 */
	public function uninstall() {
		delete_option( self::OPTION_HOVERCARDS );
	}

	/**
	 * Main admin_init function used to hook into and register stuff and init plugin settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		register_setting( 'discussion', self::OPTION_HOVERCARDS );

		add_settings_field(
			self::OPTION_HOVERCARDS,
			__( 'Hovercards', 'gravatar-enhanced' ),
			[ $this, 'display_checkbox_setting' ],
			'discussion',
			'avatars',
			array(
				'id' => self::OPTION_HOVERCARDS,
				'label' => __( 'Enable Gravatar Hovercards', 'gravatar-enhanced' ),
				'description' => __( 'Gravatar Hovercards show information about a person: name, bio, pictures, and their contact info at other services they use on the web like Twitter, Facebook or LinkedIn. <a href="http://blog.gravatar.com/2010/10/06/gravatar-hovercards-on-wordpress-com/" title="Opens new window" target="_blank">Learn More &raquo;</a>', 'gravatar-enhanced' ),
			)
		);
	}

	/**
	 * Initialise Gravatar Hovercards, if enabled.
	 *
	 * @return void
	 */
	public function maybe_add_hovercards() {
		if ( get_option( self::OPTION_HOVERCARDS ) ) {
			wp_enqueue_script( 'gravatar-enhanced-js', self::GRAVATAR_ENHANCED_HOVERCARD_URL, [], self::GRAVATAR_ENHANCED_HOVERCARD_VERSION, true );
			wp_enqueue_style( 'gravatar-enhanced-style', self::GRAVATAR_ENHANCED_HOVERCARD_STYLES_URL, [], self::GRAVATAR_ENHANCED_HOVERCARD_VERSION );
			wp_add_inline_script( 'gravatar-enhanced-js', 'document.addEventListener( \'DOMContentLoaded\', () => { if ( Gravatar.Hovercards ) { const hovercards = new Gravatar.Hovercards(); hovercards.attach( document.body ); } } );' );
		}
	}

	/**
	 * Check if the module is disabled, by either a filter or a detected incompatibility.
	 *
	 * @return bool
	 */
	private function is_module_disabled() {
		// Check if module is manually disabled by the filter.
		if ( ! apply_filters( 'gravatar_enhanced_hovercards_module_enabled', true ) ) {
			return true; // Disabled by filter.
		}

		// Check if Jetpack is active and has the Gravatar Hovercards module enabled.
		if ( class_exists( '\Jetpack' ) && \Jetpack::is_module_active( 'gravatar-hovercards' ) ) {
			return true; // Disabled due to Jetpack Gravatar Hovercards module.
		}

		return false;
	}
}
