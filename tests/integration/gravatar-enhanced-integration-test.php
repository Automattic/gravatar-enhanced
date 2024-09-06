<?php

use Automattic\Gravatar\GravatarEnhanced\Options;
use Automattic\Gravatar\GravatarEnhanced\Email;

/**
 * Gravatar Enhanced integration test.
 */
class GravatarEnhancedIntegrationTest extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		reset_phpmailer_instance();
	}

	public function testInvitationEmailOption_WhenDefault_ThenIsDisabled() {
		// Arrange.
		$option = get_option( 'gravatar_invitation_email' );

		// Assert.
		$this->assertFalse( $option, 'Gravatar invitation email option expected to be disabled by default' );
	}

	public function testNewComment_WhenGravatarInvitationOptionIsNotEnabled_ThenNoEmailIsSent() {
		// Arrange.
		$options = new Options\SavedOptions( 'gravatar_enhanced_options', false );

		( new Email\EmailNotification( new Email\Preferences( $options ) ) )->plugin_init(); // We need to call email init again manually.
		$post = $this->factory()->post->create_and_get(
			[
				'post_type' => 'post',
			]
		);

		// Act.
		$this->factory()->comment->create_post_comments( $post->ID, 1, [ 'comment_author_email' => 'some@mail.com' ] );

		// Assert.
		$sent = tests_retrieve_phpmailer_instance()->get_sent( 0 );
		$this->assertFalse( $sent, 'Email was not supposed to be sent' );
	}

	public function testNewComment_WhenGravatarInvitationOptionIsEnabled_ThenSendsEmail() {
		// Arrange.
		$email = [
			'enabled' => true,
			'message' => '',
		];
		$options = new Options\SavedOptions( 'gravatar_enhanced_options', false );

		( new Email\EmailNotification( new Email\Preferences( $options, Email\Options::from_array( $email ) ) ) )->plugin_init(); // We need to call email init again manually.
		$post = $this->factory()->post->create_and_get(
			[
				'post_type' => 'post',
			]
		);

		// Act.
		[ $comment_id ] = $this->factory()->comment->create_post_comments( $post->ID, 1, [ 'comment_author_email' => 'some@mail.com' ] );
		$comment = get_comment( $comment_id );

		// Assert.
		$sent = tests_retrieve_phpmailer_instance()->get_sent( 0 );
		$this->assertEquals( 'some@mail.com', $sent->to[0][0], 'Email is sent to author of the comment' );
		$this->assertStringContainsString( 'Gravatar invitation', $sent->subject, 'Email subject contains "Gravatar invitation"' );
		$this->assertStringContainsString( $comment->comment_author, $sent->body, 'Email body contains comment author name' );
		$this->assertStringContainsString( 'https://gravatar.com/connect/?email=some%40mail.com', $sent->body, 'Email body contains Gravatar signup link' );
	}
}
