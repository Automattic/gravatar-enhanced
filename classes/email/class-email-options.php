<?php

namespace Automattic\Gravatar\GravatarEnhanced\Email;

/**
 * @phpstan-type EmailOptionsArray = array{
 *   enabled: bool,
 *   message: string,
 * }
 */
class Options {
	/** @var bool */
	public $enabled;

	/** @var string */
	public $message;

	/**
	 * @param bool $enabled
	 * @param string $message
	 */
	public function __construct( $enabled, $message ) {
		$this->enabled = $enabled;
		$this->message = str_replace( "\r\n", "\n", $message );
	}

	/**
	 * @return EmailOptionsArray
	 */
	public function to_array() {
		return [
			'enabled' => $this->enabled,
			'message' => $this->message,
		];
	}

	/**
	 * @param EmailOptionsArray $options
	 * @return Options
	 */
	public static function from_array( $options ) {
		return new Options(
			$options['enabled'],
			$options['message'],
		);
	}
}
