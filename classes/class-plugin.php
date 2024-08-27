<?php

namespace Automattic\Gravatar\GravatarEnhanced;

require_once __DIR__ . '/settings/trait-checkbox.php';
require_once __DIR__ . '/email/class-email.php';
require_once __DIR__ . '/hovercards/class-hovercards.php';
require_once __DIR__ . '/avatar/class-avatar.php';
require_once __DIR__ . '/proxy/class-proxy.php';
require_once __DIR__ . '/quick-editor/class-quick-editor.php';
require_once __DIR__ . '/analytics/class-analytics.php';

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
	 * @var Proxy\Proxy
	 */
	private $proxy;

	/**
	 * @var QuickEditor\QuickEditor
	 */
	private $quick_editor;

	/**
	 * @var Analytics\Analytics
	 */
	private $analytics;

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

		$this->proxy = new Proxy\Proxy();
		$this->proxy->init();

		$this->quick_editor = new QuickEditor\QuickEditor();
		$this->quick_editor->init();

		$this->analytics = new Analytics\Analytics();
		$this->analytics->init();
	}

	/**
	 * Uninstall the plugin
	 * @return void
	 */
	public function uninstall() {
		$this->email = new Email\EmailNotification();
		$this->email->uninstall();

		$this->hovercards = new Hovercards\Hovercards();
		$this->hovercards->uninstall();

		$this->proxy = new Proxy\Proxy();
		$this->proxy->uninstall();

		$this->avatar = new Avatar\Avatar();
		$this->avatar->uninstall();

		$this->quick_editor = new QuickEditor\QuickEditor();
		$this->quick_editor->uninstall();

		$this->analytics = new Analytics\Analytics();
		$this->analytics->uninstall();

		// Just in case, flush the cache
		wp_cache_flush();
	}
}
