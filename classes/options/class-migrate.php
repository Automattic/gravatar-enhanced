<?php

namespace Automattic\Gravatar\GravatarEnhanced\Options;

use Automattic\Gravatar\GravatarEnhanced\Email;

class Migrate {
	/**
	 * Maybe migrate old options, or install some new ones. After this has run once it shouldn't run again.
	 *
	 * @param SavedOptions $default_options
	 * @param string $option_name
	 * @return void
	 */
	public function maybe_migrate( $default_options, $option_name ) {
		// Get new option
		$options = get_option( $option_name, null );

		if ( is_null( $options ) ) {
			// Option doesn't exist. Look for old options
			$message = get_option( 'gravatar_invitation_message', null );

			if ( ! is_null( $message ) ) {
				// Old site. Migrate options
				$email = get_option( 'gravatar_invitation_email', false );

				$this->migrate_old_options( $default_options, $email, $message );
				$this->remove_old_options();
			}
		}
	}

	/**
	 * Migrate the email and hovercard settings
	 *
	 * @param SavedOptions $default_options
	 * @param string $email
	 * @param string $message
	 * @return void
	 */
	private function migrate_old_options( $default_options, $email, $message ) {
		$message = trim( sanitize_textarea_field( wp_unslash( $message ) ) );

		// Remove the default 'old' message
		if ( $message === $this->get_old_email_message() ) {
			$message = '';
		}

		$email = Email\Options::from_array(
			[
				'enabled' => $email === '1' ? true : false,
				'message' => $message,
			]
		);

		$preferences = new Email\Preferences( $default_options, $email );
		$default_options->update( $preferences );
		$default_options->save();
	}

	/**
	 * The old email message. We use this to check if the message is the default
	 *
	 * @return string
	 */
	private function get_old_email_message() {
		return __(
			'Hi COMMENTER_NAME!

Thanks for your comment on "POST_NAME"!

I noticed that you didn\'t have your own picture or profile next to your comment. Why not set one up using Gravatar? Click the link below to get started:

GRAVATAR_URL

*What\'s a Gravatar?*

Your Gravatar (a Globally Recognized Avatar) is an image that follows you from site to site appearing beside your name when you do things like comment or post on a blog. Avatars help identify your posts on blogs and web forums, so why not on any site?

Thanks for visiting and come back soon!

-- The Team @ SITE_NAME',
			'gravatar-enhanced'
		);
	}

	/**
	 * Delete the old options
	 *
	 * @return void
	 */
	private function remove_old_options() {
		delete_option( 'gravatar_invitation_email' );
		delete_option( 'gravatar_invitation_message' );
	}
}
