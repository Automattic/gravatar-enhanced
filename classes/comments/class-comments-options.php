<?php

namespace Automattic\Gravatar\GravatarEnhanced\Comments;

/**
 * @phpstan-type CommentsOptionsArray = array{
 *   enabled: bool,
 * }
 */
class Options {
	/** @var bool */
	public $enabled;

	/**
	 * @param bool $enabled
	 */
	public function __construct( $enabled ) {
		$this->enabled = $enabled;
	}

	/**
	 * @return CommentsOptionsArray
	 */
	public function to_array() {
		return [
			'enabled' => $this->enabled,
		];
	}

	/**
	 * @param CommentsOptionsArray $options
	 * @return Options
	 */
	public static function from_array( $options ) {
		return new Options( $options['enabled'] );
	}
}
