<?php

namespace Automattic\Gravatar\GravatarEnhanced\AuthorArchive;

use Automattic\Gravatar\GravatarEnhanced\Options as CoreOptions;

/**
 * @phpstan-import-type AuthorArchiveOptionsArray from Options
 * @phpstan-import-type OptionsArray from CoreOptions\SavedOptions
 */
class Preferences {
	const OPTION_NAME = 'author_archive';

	/**
	 * @var Options
	 */
	private $options;

	/**
	 * @param CoreOptions\SavedOptions $saved_options
	 * @param Options | null $new_options
	 */
	public function __construct( $saved_options, $new_options = null ) {
		/** @var AuthorArchiveOptionsArray */
		$options = array_merge(
			$this->get_default_options(),
			$saved_options->get_group( self::OPTION_NAME ),
			$new_options ? $new_options->to_array() : []
		);

		$this->options = Options::from_array( $options );
	}

	/**
	 * @return AuthorArchiveOptionsArray
	 */
	private function get_default_options() {
		return [
			'auto_show' => Options::AUTO_SHOW_OFF,
		];
	}

	/**
	 * @return Options
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * @return array<string,AuthorArchiveOptionsArray>
	 */
	public function get_as_preferences() {
		return [
			self::OPTION_NAME => $this->options->to_array(),
		];
	}
}
