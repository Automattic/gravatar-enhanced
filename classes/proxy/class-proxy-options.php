<?php

namespace Automattic\Gravatar\GravatarEnhanced\Proxy;

/**
 * @phpstan-type ProxyOptionsType = 'disabled' | 'local' | 'private'
 * @phpstan-type ProxyOptionsArray = array{
 *   type: ProxyOptionsType,
 *   time?: int,
 * }
 */
class Options {
	const TYPE_DISABLED = 'disabled';
	const TYPE_LOCAL = 'local';
	const TYPE_PRIVATE = 'private';

	/** @var ProxyOptionsType */
	public $type;

	/** @var int */
	public $time;

	/**
	 * @param ProxyOptionsType $type
	 * @param int $time
	 */
	public function __construct( $type, $time ) {
		$this->type = $type;
		$this->time = $time;
	}

	/**
	 * @return ProxyOptionsArray
	 */
	public function to_array() {
		return [
			'type' => $this->type,
			'time' => $this->time,
		];
	}

	/**
	 * @param ProxyOptionsArray $options
	 * @return Options
	 */
	public static function from_array( $options ) {
		return new Options(
			$options['type'],
			$options['time'] ?? 0,
		);
	}
}
