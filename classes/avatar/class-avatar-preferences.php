<?php

namespace Automattic\Gravatar\GravatarEnhanced\Avatar;

use Automattic\Gravatar\GravatarEnhanced\Options as CoreOptions;

/**
 * @phpstan-import-type AvatarOptionsArray from Options
 * @phpstan-import-type OptionsArray from CoreOptions\SavedOptions
 */
class Preferences {
	const OPTION_NAME = 'avatar';

	/**
	 * @var Options
	 */
	private $options;

	/**
	 * @param CoreOptions\SavedOptions $saved_options
	 * @param Options | null $new_options
	 */
	public function __construct( $saved_options, $new_options = null ) {
		/** @var AvatarOptionsArray */
		$options = array_merge(
			$this->get_default_options(),
			$saved_options->get_group( self::OPTION_NAME ),
			$new_options ? $new_options->to_array() : [],
		);

		$this->options = Options::from_array( $options );
	}

	/**
	 * @return AvatarOptionsArray
	 */
	private function get_default_options() {
		return [
			'force_default_avatar' => false,
			'force_alt' => true,
		];
	}

	/**
	 * @return Options
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * @return array<string,AvatarOptionsArray>
	 */
	public function get_as_preferences() {
		return [
			self::OPTION_NAME => $this->options->to_array(),
		];
	}
}
