<?php

namespace Automattic\Gravatar\GravatarEnhanced\Avatar;

/**
 * @phpstan-type AvatarOptionsArray = array{
 *   force_default_avatar: bool,
 *   force_alt: bool,
 * }
 */
class Options {
	/** @var bool */
	public $force_alt;

	/** @var bool */
	public $force_default_avatar;

	/**
	 * @param bool $force_default_avatar
	 * @param bool $force_alt
	 */
	public function __construct( $force_default_avatar, $force_alt ) {
		$this->force_default_avatar = $force_default_avatar;
		$this->force_alt = $force_alt;
	}

	/**
	 * @return AvatarOptionsArray
	 */
	public function to_array() {
		return [
			'force_default_avatar' => $this->force_default_avatar,
			'force_alt' => $this->force_alt,
		];
	}

	/**
	 * @param AvatarOptionsArray $options
	 * @return Options
	 */
	public static function from_array( $options ) {
		return new Options(
			boolval( $options['force_default_avatar'] ),
			boolval( $options['force_alt'] )
		);
	}
}
