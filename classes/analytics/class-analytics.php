<?php

namespace Automattic\Gravatar\GravatarEnhanced\Analytics;

use Automattic\Gravatar\GravatarEnhanced\Module;

require_once __DIR__ . '/class-analytics-options.php';
require_once __DIR__ . '/class-analytics-preferences.php';

class Analytics implements Module {
	const OPTION_ANALYTICS = 'gravatar_analytics';

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
	 * @return void
	 */
	public function maybe_add_analytics() {
		if ( ! $this->options->enabled ) {
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

	/**
	 * @return void
	 */
	public function uninstall() {}
}
