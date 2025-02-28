<?php

namespace Automattic\Gravatar\GravatarEnhanced\OEmbed;

use Automattic\Gravatar\GravatarEnhanced\Module;

class OEmbed implements Module {
	private const API_ENDPOINT = 'https://api.gravatar.com/v3/oembed';

	/**
	 * @return void
	 */
	public function init() {
		$this->register_oembed_provider();
	}

	/**
	 * Register the oEmbed provider for Gravatar.
	 *
	 * @return void
	 */
	public function register_oembed_provider() {
		wp_oembed_add_provider( '/^https?:\/\/((www|[a-z]{2}(-[A-Z]{2})?)\.)?gravatar\.com\/([a-zA-Z0-9]+)\/?$/', self::API_ENDPOINT, true );
	}

	/**
	 * @return void
	 */
	public function uninstall() {}
}
