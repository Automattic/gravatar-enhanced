<?php

namespace Automattic\Gravatar\GravatarEnhanced\Email;

use Automattic\Gravatar\GravatarEnhanced\Options;
use GravatarEnhanced\Tests\Unit\TestCase;

require_once ROOT_DIR . '/classes/email/class-email.php';

class EmailNotificationTest extends TestCase {

	/**
	 * @var EmailNotification
	 */
	private $email_notifications;

	public function setUp(): void {
		parent::setUp();

		$options = new Options\SavedOptions( 'gravatar_enhanced_options', false );

		$this->email_notifications = new EmailNotification( new Preferences( $options ) );
	}

	public function testInit_WhenCalled_ThenAddsExpectedHooks() {
		// Act.
		$this->email_notifications->init();

		// Assert.
		$this->assertNotFalse(
			has_action(
				'wp_insert_comment',
				[ $this->email_notifications, 'plugin_init' ]
			),
			'plugin_init action is missing'
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
