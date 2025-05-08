<?php

namespace Automattic\Gravatar\GravatarEnhanced\Analytics;

use Automattic\Gravatar\GravatarEnhanced\Options as CoreOptions;

/**
 * @phpstan-import-type AnalyticsOptionsArray from Options
 * @phpstan-import-type OptionsArray from CoreOptions\SavedOptions
 */
class Preferences {
	const OPTION_NAME = 'analytics';

	/**
	 * @var Options
	 */
	private $options;

	/**
	 * @param CoreOptions\SavedOptions $saved_options
	 * @param Options | null $new_options
	 */
	public function __construct( $saved_options, $new_options = null ) {
		/** @var AnalyticsOptionsArray */
		$options = array_merge(
			$this->get_default_options(),
			$saved_options->get_group( self::OPTION_NAME ),
			$new_options ? $new_options->to_array() : []
		);

		$this->options = Options::from_array( $options );
	}

	/**
	 * @return AnalyticsOptionsArray
	 */
	private function get_default_options() {
		return [
			'enabled' => false,
		];
	}

	/**
	 * @return Options
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * @return array<string,AnalyticsOptionsArray>
	 */
	public function get_as_preferences() {
		return [
			self::OPTION_NAME => $this->options->to_array(),
		];
	}
}
