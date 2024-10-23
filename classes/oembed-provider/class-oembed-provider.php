<?php

namespace Automattic\Gravatar\GravatarEnhanced\OEmbedProvider;

class OEmbedProvider {
	private const API_ENDPOINT = 'https://api.gravatar.com/v3/oembed';

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_oembed_provider' ] );
	}

	public function register_oembed_provider() {
		wp_oembed_add_provider( '#https?://((www|[a-z]{2}(-[A-Z]{2})?)\.)?gravatar\.com/([a-zA-Z0-9\-._]+)$#', self::API_ENDPOINT, true );
	}
}
