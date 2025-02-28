<?php

namespace Automattic\Gravatar\GravatarEnhanced\Proxy;

require_once __DIR__ . '/class-proxy-cache.php';
require_once __DIR__ . '/class-hash.php';
require_once __DIR__ . '/class-proxy-options.php';
require_once __DIR__ . '/class-proxy-preferences.php';

use Automattic\Gravatar\GravatarEnhanced\Module;
use WP_Error;

class Proxy implements Module {
	/**
	 * @var Options
	 */
	private $options;

	const SCHEDULE_NAME = 'gravatar_enhanced_proxy_flush';

	const FILTER_PROXY_FLUSH_FREQUENCY = 'gravatar_proxy_flush_frequency';
	const FILTER_PROXY_MAX_DOWNLOAD_TIME = 'gravatar_proxy_max_download_time';
	const FILTER_PROXY_MAX_DOWNLOADS = 'gravatar_proxy_max_downloads';
	const FILTER_PROXY_SITE_ID = 'gravatar_proxy_site_id';

	/**
	 * Total number of downloads this request
	 * @var int
	 */
	private $total_downloads = 0;

	/**
	 * Max number of downloads this request
	 * @var int
	 */
	private $max_downloads = 20;

	/**
	 * Max time of downloads this request (seconds)
	 * @var int
	 */
	private $max_download_time = 5;

	/**
	 * Time when downloads first started
	 * @var int
	 */
	private $time_first_download = 0;

	/**
	 * @param Preferences $preferences
	 *
	 */
	public function __construct( $preferences ) {
		$this->options = $preferences->get_options();
	}

	/**
	 * @return void
	 */
	public function init() {
		register_deactivation_hook( GRAVATAR_ENHANCED_PLUGIN_FILE, [ $this, 'deactivate' ] );

		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			return;
		}

		add_action( 'admin_init', [ $this, 'admin_init' ] );

		if ( $this->options->type !== Options::TYPE_DISABLED ) {
			add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ], 11 );

			$this->max_download_time = apply_filters( self::FILTER_PROXY_MAX_DOWNLOAD_TIME, $this->max_download_time );
			$this->max_downloads = apply_filters( self::FILTER_PROXY_MAX_DOWNLOADS, $this->max_downloads );

			add_action( self::SCHEDULE_NAME, [ $this, 'flush_cache' ] );
		}
	}

	/**
	 * Main admin_init function used to hook into and register stuff and init plugin settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		if ( isset( $_POST['flush_cache'] ) && isset( $_POST['flush_cache_nonce'] ) && wp_verify_nonce( $_POST['flush_cache_nonce'], 'flush_cache' ) ) {
			$this->flush_cache();
		}

		$this->schedule_flush();
	}

	/**
	 * Flush the cache
	 *
	 * @return void
	 */
	public function flush_cache() {
		$proxy = new ProxyCache();
		$proxy->flush();
	}

	/**
	 * Plugin deactivation
	 * @return void
	 */
	public function deactivate() {
		wp_clear_scheduled_hook( self::SCHEDULE_NAME );
	}

	/**
	 * Schedule the cache flush
	 *
	 * @return void
	 */
	private function schedule_flush() {
		$schedule_time = wp_next_scheduled( self::SCHEDULE_NAME );
		$is_enabled = $this->options->type !== Options::TYPE_DISABLED;

		// Remove the schedule if proxy is disabled
		if ( $schedule_time !== false && ! $is_enabled ) {
			$this->deactivate();
		}

		// Enable the schedule if proxy is enabled
		if ( $schedule_time === false && $is_enabled ) {
			$frequency = apply_filters( self::FILTER_PROXY_FLUSH_FREQUENCY, 'weekly' );

			wp_schedule_event(
				time(),
				$frequency,
				self::SCHEDULE_NAME
			);
		}
	}

	/**
	 * Replace the avatar URL with a SHA256 encoded one
	 *
	 * @param string $url
	 * @return string
	 */
	public function get_avatar_url( $url ) {
		if ( $this->time_first_download === 0 ) {
			$this->time_first_download = time();
		}

		// Can we download anymore avatars?
		if ( ! $this->can_download() ) {
			return $url;
		}

		$hash = $this->get_hash_for_url( $url );
		$proxy = new ProxyCache();

		// Have we already cached this user?
		if ( ! $proxy->exists_locally( $hash ) ) {
			// No, so try and download it
			$result = $proxy->copy_avatar_locally( $hash );

			if ( $result instanceof WP_Error ) {
				// Failed. Just return the original URL
				return $url;
			}

			$this->total_downloads++;
		}

		// Return the proxied URL
		return $proxy->get_local_url( $hash );
	}

	/**
	 * Can we still download?
	 *
	 * @return bool
	 */
	private function can_download() {
		// Limit the number of downloads per page load
		if ( $this->total_downloads >= $this->max_downloads ) {
			return false;
		}

		// Limit the total time spent downloading. This is a rough calculation and isn't strictly the download time, but it's close enough
		if ( time() - $this->time_first_download >= $this->max_download_time ) {
			return false;
		}

		return true;
	}

	/**
	 * Get a hash object for the URL based on site settings
	 *
	 * @param string $url
	 * @return AvatarHash
	 */
	private function get_hash_for_url( $url ) {
		if ( $this->options->type === Options::TYPE_PRIVATE ) {
			$cache_id = apply_filters( self::FILTER_PROXY_SITE_ID, 'gravatar' );
			return new PrivateAvatarHash( $url, (string) $this->options->time, $cache_id );
		}

		return new LocalAvatarHash( $url );
	}

	/**
	 * @return void
	 */
	public function uninstall() {}
}
