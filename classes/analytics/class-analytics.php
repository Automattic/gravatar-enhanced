<?php

namespace Automattic\Gravatar\GravatarEnhanced\Analytics;

use Automattic\Gravatar\GravatarEnhanced\Settings;

class Analytics {
	use Settings\SettingsCheckbox;

	const OPTION_ANALYTICS = 'gravatar_analytics';

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'admin_init' ], 5 );
		add_action( 'admin_head-options-discussion.php', [ $this, 'add_discussion' ] );

		// Enable analytics on these pages
		$pages = [
			'profile.php',
			'options-discussion.php',
		];

		foreach ( $pages as $page ) {
			add_action( 'admin_print_scripts-' . $page, [ $this, 'maybe_add_analytics' ] );
		}
	}

	/**
	 * @return void
	 */
	public function add_discussion() {
		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/discussion.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_script( 'gravatar-enhanced-discussion', plugins_url( 'build/discussion.js', GRAVATAR_ENHANCED_PLUGIN_FILE ), $assets['dependencies'], $assets['version'], true );
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
		register_setting( 'discussion', self::OPTION_ANALYTICS );

		add_settings_field(
			self::OPTION_ANALYTICS,
			__( 'Enable usage tracking', 'gravatar-enhanced' ),
			[ $this, 'display_checkbox_setting' ],
			'discussion',
			'avatars',
			array(
				'id' => self::OPTION_ANALYTICS,
				'label' => __( 'Enable anonymous analytics', 'gravatar-enhanced' ),
				'description' => __( 'Help us make Gravatar better by allowing us to collect anonymous usage tracking of the features used.', 'gravatar-enhanced' ),
			)
		);
	}

	/**
	 * @return void
	 */
	public function maybe_add_analytics() {
		if ( ! get_option( self::OPTION_ANALYTICS, false ) ) {
			return;
		}

		$current_user = wp_get_current_user();
		$current_user_locale = str_replace( '_', '-', get_user_locale( $current_user ) );

		// phpcs:ignore
		$js = '<script defer src="//stats.wp.com/w.js" crossorigin="anonymous"></script>';
		$js .= <<<SCRIPT
	<script>
		window._deferredTracksEvents = window._deferredTracksEvents || [];

		window.gravatar = window.gravatar || {};
		window._deferredTracksEvents.push(
			[
				'storeContext',
				{
					'blog_id': '0',
					'blog_tz': '0',
					'user_lang': navigator?.language,
					'blog_lang': '$current_user_locale',
					'user_id': '0',
				}
			]
		);

		window.gravatar.recordTrackEvent = function ( name, properties = {} ) {
			window._deferredTracksEvents.push( [ 'recordEvent', name, properties ] );
		};
	</script>
	SCRIPT;

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$js = <<<SCRIPT
				<script>
					window.gravatar = window.gravatar || {};
					window.gravatar.recordTrackEvent = function ( name, properties = {} ) {
						console.log( 'Track Event: ' + name, properties );
					};
				</script>
			SCRIPT;
		}

		// phpcs:ignore
		echo $js;
	}
}
