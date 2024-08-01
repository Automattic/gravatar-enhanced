<?php

namespace Automattic\Gravatar\GravatarEnhanced;

require_once __DIR__ . '/email/class-email.php';
require_once __DIR__ . '/hovercards/class-hovercards.php';

class Plugin {
	/**
	 * @var Email\EmailNotification
	 */
	private $email;

	/**
	 * @var Hovercards\Hovercards
	 */
	private $hovercards;

	/**
	 * Start the plugin
	 * @return void
	 */
	public function init() {
		$this->email = new Email\EmailNotification();
		$this->email->init();

		$this->hovercards = new Hovercards\Hovercards();
		$this->hovercards->init();
	}
}
