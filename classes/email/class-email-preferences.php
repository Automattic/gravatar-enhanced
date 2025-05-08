<?php

namespace Automattic\Gravatar\GravatarEnhanced\Email;

use Automattic\Gravatar\GravatarEnhanced\Options as CoreOptions;

/**
 * @phpstan-import-type EmailOptionsArray from Options
 * @phpstan-import-type OptionsArray from CoreOptions\SavedOptions
 */
class Preferences {
	const OPTION_NAME = 'email';

	/**
	 * @var CoreOptions\SavedOptions
	 */
	private $core_options;

	/**
	 * @var Options | null
	 */
	private $options = null;

	/**
	 * @param CoreOptions\SavedOptions $saved_options
	 * @param Options | null $new_options
	 */
	public function __construct( $saved_options, $new_options = null ) {
		$this->core_options = $saved_options;

		if ( ! is_null( $new_options ) ) {
			$this->load( $new_options );
		}
	}

	/**
	 * @param Options | null $new_options
	 * @return void
	 */
	private function load( $new_options = null ) {
		$this->core_options->load();

		/** @var EmailOptionsArray */
		$options = array_merge(
			$this->get_default_options(),
			$this->core_options->get_group( self::OPTION_NAME ),
			$new_options ? $new_options->to_array() : [],
		);

		if ( $options['message'] === '' ) {
			$options['message'] = $this->get_default_options()['message'];
		}

		$this->options = Options::from_array( $options );
	}

	/**
	 * @return EmailOptionsArray
	 */
	private function get_default_options() {
		$default_message = __(
			'Hi COMMENTER_NAME,

Thanks for your comment on "POST_NAME"!

I noticed that you didn\'t have your own picture or profile next to your comment. Setting it up on Gravatar is quick and easy — just click below:

GRAVATAR_URL

* What’s Gravatar? *

It\'s a free profile and avatar image used on thousands of sites. With one setup, you\'re good to go across the web!

Hope to see you back on SITE_NAME soon.

-- SITE_NAME',
			'gravatar-enhanced'
		);

		// Sanitize this in the same way as on the discussions page so when we compare against the default it will match
		$default_message = sanitize_textarea_field( wp_unslash( $default_message ) );
		$default_message = str_replace( "\r\n", "\n", $default_message );

		return [
			'enabled' => false,
			'message' => $default_message,
		];
	}

	/**
	 * @return Options
	 */
	public function get_options() {
		if ( is_null( $this->options ) ) {
			$this->load();
		}

		/** @var Options */
		return $this->options;
	}

	/**
	 * @return array<string,EmailOptionsArray>
	 */
	public function get_as_preferences() {
		$options = $this->get_options()->to_array();
		$defaults = $this->get_default_options();

		// If the message is the empty string then reset it to the default
		if ( $options['message'] === $defaults['message'] ) {
			$options['message'] = '';
		}

		return [
			self::OPTION_NAME => $options,
		];
	}
}
