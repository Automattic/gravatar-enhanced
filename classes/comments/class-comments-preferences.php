<?php

namespace Automattic\Gravatar\GravatarEnhanced\Comments;

use Automattic\Gravatar\GravatarEnhanced\Options as CoreOptions;

/**
 * @phpstan-import-type CommentsOptionsArray from Options
 * @phpstan-import-type OptionsArray from CoreOptions\SavedOptions
 */
class Preferences {
	const OPTION_NAME = 'comments';

	/**
	 * @var Options
	 */
	private $options;

	/**
	 * @param CoreOptions\SavedOptions $saved_options
	 * @param Options | null $new_options
	 */
	public function __construct( $saved_options, $new_options = null ) {
		/** @var CommentsOptionsArray */
		$options = array_merge(
			$this->get_default_options(),
			$saved_options->get_group( self::OPTION_NAME ),
			$new_options ? $new_options->to_array() : []
		);

		$this->options = Options::from_array( $options );
	}

	/**
	 * @return CommentsOptionsArray
	 */
	private function get_default_options() {
		return [
			'enabled' => true,
		];
	}

	/**
	 * @return Options
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * @return array<string,CommentsOptionsArray>
	 */
	public function get_as_preferences() {
		return [
			self::OPTION_NAME => $this->options->to_array(),
		];
	}
}
