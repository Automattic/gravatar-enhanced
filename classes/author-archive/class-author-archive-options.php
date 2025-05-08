<?php

namespace Automattic\Gravatar\GravatarEnhanced\AuthorArchive;

/**
 * @phpstan-type AuthorArchiveAutoShow 'off'|'top'|'bottom'
 * @phpstan-type AuthorArchiveOptionsArray array{
 *   auto_show: AuthorArchiveAutoShow,
 * }
 */
class Options {
	const AUTO_SHOW_OFF = 'off';
	const AUTO_SHOW_TOP = 'top';
	const AUTO_SHOW_BOTTOM = 'bottom';

	/** @var AuthorArchiveAutoShow */
	public $auto_show;

	/**
	 * @param AuthorArchiveAutoShow $auto_show
	 */
	public function __construct( $auto_show ) {
		$this->auto_show = $auto_show;
	}

	/**
	 * @return AuthorArchiveOptionsArray
	 */
	public function to_array() {
		return [
			'auto_show' => $this->auto_show,
		];
	}

	/**
	 * @param AuthorArchiveOptionsArray $options
	 * @return Options
	 */
	public static function from_array( $options ) {
		return new Options( $options['auto_show'] );
	}
}
