<?php

namespace Automattic\Gravatar\GravatarEnhanced\Proxy;

require_once __DIR__ . '/class-hash-private.php';
require_once __DIR__ . '/class-hash-local.php';

/**
 * Base class to convert a remote Gravatar URL to a local hash.
 */
abstract class AvatarHash {
	/**
	 * Our local hash value
	 * @var string
	 */
	protected $local_hash;

	/**
	 * The original remote URL
	 * @var string
	 */
	protected $remote_url;

	/**
	 * @return string
	 */
	public function get_local_hash() {
		return $this->local_hash;
	}

	/**
	 * @return string
	 */
	public function get_remote_url() {
		return $this->remote_url;
	}
}
