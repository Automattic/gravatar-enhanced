<?php

namespace Automattic\Gravatar\GravatarEnhanced\Comments;

/**
 * @phpstan-type CommentsOptionsArray = array{
 *   enabled: bool,
 *   fediverse_hovercards: bool,
 * }
 */
class Options {
	/** @var bool */
	public $enabled;

	/** @var bool */
	public $fediverse_hovercards;

	/**
	 * @param bool $enabled
	 * @param bool $fediverse_hovercards
	 */
	public function __construct( $enabled, $fediverse_hovercards ) {
		$this->enabled             = $enabled;
		$this->fediverse_hovercards = $fediverse_hovercards;
	}

	/**
	 * @return CommentsOptionsArray
	 */
	public function to_array() {
		return [
			'enabled'              => $this->enabled,
			'fediverse_hovercards' => $this->fediverse_hovercards,
		];
	}

	/**
	 * @param CommentsOptionsArray $options
	 * @return Options
	 */
	public static function from_array( $options ) {
		return new Options(
			$options['enabled'],
			$options['fediverse_hovercards']
		);
	}
}
