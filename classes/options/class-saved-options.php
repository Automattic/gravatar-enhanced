<?php

namespace Automattic\Gravatar\GravatarEnhanced\Options;

use Automattic\Gravatar\GravatarEnhanced\Avatar;
use Automattic\Gravatar\GravatarEnhanced\Proxy;
use Automattic\Gravatar\GravatarEnhanced\Analytics;
use Automattic\Gravatar\GravatarEnhanced\Email;
use Automattic\Gravatar\GravatarEnhanced\Comments;

/**
 * @phpstan-import-type AvatarOptionsArray from Avatar\Options
 * @phpstan-import-type ProxyOptionsArray from Proxy\Options
 * @phpstan-import-type AnalyticsOptionsArray from Analytics\Options
 * @phpstan-import-type EmailOptionsArray from Email\Options
 * @phpstan-import-type CommentsOptionsArray from Comments\Options
 * @phpstan-type Preferences Comments\Preferences | Avatar\Preferences | Proxy\Preferences | Email\Preferences | Analytics\Preferences
 *
 * @phpstan-type OptionsArray = array{
 *   avatar?: AvatarOptionsArray,
 *   proxy?: ProxyOptionsArray,
 *   analytics?: AnalyticsOptionsArray,
 *   email?: EmailOptionsArray,
 *   version?: string,
 *   comments?: CommentsOptionsArray,
 * }
 */
class SavedOptions {
	/**
	 * @var OptionsArray
	 */
	private $options = [];

	/**
	 * @var bool
	 */
	private $auto_load;

	/**
	 * @var string
	 */
	private $option_name;

	const OPTION_VERSION = 'version';

	/**
	 * @param string $option_name
	 * @param bool $auto_load
	 */
	public function __construct( $option_name, $auto_load ) {
		$this->option_name = $option_name;
		$this->auto_load = $auto_load;

		if ( $auto_load ) {
			$this->load();
		}
	}

	/**
	 * Load options
	 *
	 * @return void
	 */
	public function load() {
		$this->options = get_option( $this->option_name, [] );
	}

	/**
	 * @param string $group
	 * @return AvatarOptionsArray | ProxyOptionsArray | AnalyticsOptionsArray | EmailOptionsArray | array{}
	 */
	public function get_group( $group ) {
		if ( isset( $this->options[ $group ] ) && is_array( $this->options[ $group ] ) ) {
			return $this->options[ $group ];
		}

		return [];
	}

	/**
	 * @return void
	 */
	public function uninstall() {
		delete_option( $this->option_name );
	}

	/**
	 * Update the options. We only save the difference compared to the defaults.
	 *
	 * @param Preferences $preferences
	 * @return void
	 */
	public function update( $preferences ) {
		/** @var OptionsArray */
		$options = $preferences->get_as_preferences();

		$this->options = array_merge(
			$this->options,
			$options,
		);
	}

	/**
	 * @return void
	 */
	public function save() {
		// Save to database
		$options = array_merge(
			[
				self::OPTION_VERSION => GRAVATAR_ENHANCED_VERSION,
			],
			$this->options,
		);

		update_option( $this->option_name, $options, $this->auto_load );
	}
}
