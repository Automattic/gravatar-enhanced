<?php

namespace Automattic\Gravatar\GravatarEnhanced\Email;

use GravatarEnhanced\Tests\Unit\TestCase;

require_once ROOT_DIR . '/classes/email/class-email.php';

class EmailNotificationTest extends TestCase {

	/**
	 * @var EmailNotification
	 */
	private $email_notifications;

	public function setUp(): void {
		parent::setUp();

		$this->email_notifications = new EmailNotification();
	}

	public function testInit_WhenCalled_ThenAddsExpectedHooks() {
		// Act.
		$this->email_notifications->init();

		// Assert.
		$this->assertNotFalse(
			has_action(
				'init',
				[ $this->email_notifications, 'plugin_init' ]
			),
			'plugin_init action is missing'
		);
		$this->assertNotFalse(
			has_action(
				'admin_init',
				[ $this->email_notifications, 'admin_init' ]
			),
			'admin_init action is missing'
		);
		$this->assertNotFalse(
			has_action(
				'transition_comment_status',
				[ $this->email_notifications, 'transition_comment' ]
			),
			'transition_comment action is missing'
		);
		$this->assertNotFalse(
			has_action(
				'wp_insert_comment',
				[ $this->email_notifications, 'insert_comment' ]
			),
			'insert_comment action is missing'
		);
	}
}