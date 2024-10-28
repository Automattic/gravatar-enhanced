<?php

namespace Automattic\Gravatar\GravatarEnhanced\OEmbed;

class OEmbed {
	private const API_ENDPOINT = 'https://api.gravatar.com/v3/oembed';

	private const URL_FORMAT = '/^https?:\/\/((www|[a-z]{2}(-[A-Z]{2})?)\.)?gravatar\.com\/([a-zA-Z0-9]+)\/?$/';

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_oembed_provider' ] );
	}

	/**
	 * Register the oEmbed provider for Gravatar.
	 *
	 * @return void
	 */
	public function register_oembed_provider() {
		wp_oembed_add_provider( self::URL_FORMAT, self::API_ENDPOINT, true );
	}

	/**
	 * Unregister the oEmbed provider for Gravatar.
	 *
	 * @return void
	 */
	public function unregister_oembed_provider() {
		wp_oembed_remove_provider( self::URL_FORMAT );
	}

	/**
	 * @return void
	 */
	public function uninstall() {
		$this->unregister_oembed_provider();
	}
}
