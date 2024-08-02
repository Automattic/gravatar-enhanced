<?php

namespace Automattic\Gravatar\GravatarEnhanced;

require_once __DIR__ . '/email/class-email.php';
require_once __DIR__ . '/hovercards/class-hovercards.php';
require_once __DIR__ . '/avatar/class-avatar.php';

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
	 * @var Avatar\Avatar
	 */
	private $avatar;

	/**
	 * Start the plugin
	 * @return void
	 */
	public function init() {
		$this->email = new Email\EmailNotification();
		$this->email->init();

		$this->hovercards = new Hovercards\Hovercards();
		$this->hovercards->init();

		$this->avatar = new Avatar\Avatar();
		$this->avatar->init();
	}
}
