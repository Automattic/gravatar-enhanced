<?php

namespace Automattic\Gravatar\GravatarEnhanced\Analytics;

/**
 * @phpstan-type AnalyticsOptionsArray = array{
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
	 * @return AnalyticsOptionsArray
	 */
	public function to_array() {
		return [
			'enabled' => $this->enabled,
		];
	}

	/**
	 * @param AnalyticsOptionsArray $options
	 * @return Options
	 */
	public static function from_array( $options ) {
		return new Options( $options['enabled'] );
	}
}
