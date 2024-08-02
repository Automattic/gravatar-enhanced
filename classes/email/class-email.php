<?php

namespace Automattic\Gravatar\GravatarEnhanced\Email;

use WP_Http;
use WP_Comment;

class EmailNotification {
	const GRAVATAR_ENHANCED_SIGNUP_URL = 'https://gravatar.com/signup';
	const COMMENT_META_KEY = 'gravatar_invite_';

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'plugin_init' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'transition_comment_status', [ $this, 'transition_comment' ], 10, 3 );
		add_action( 'wp_insert_comment', [ $this, 'insert_comment' ], 10, 2 );
	}

	/**
	 * Remove the notifications options and comment meta
	 *
	 * @return void
	 */
	public function uninstall() {
		delete_option( 'gravatar_invitation_email' );
		delete_option( 'gravatar_invitation_message' );

		global $wpdb;

		$table = _get_meta_table( 'comment' );

		// phpcs:ignore
		$wpdb->query( "DELETE FROM {$table} WHERE meta_key LIKE '" . self::COMMENT_META_KEY . "%'" );
	}

	/**
	 * Main init function used to hook into and register stuff.
	 *
	 * @return void
	 */
	public function plugin_init() {
		if ( get_option( 'gravatar_invitation_email' ) ) {
			foreach ( array( 'post', 'page' ) as $post_type ) {
				add_post_type_support( $post_type, 'gravatar_invitation_email' );
			}
		}
	}

	/**
	 * Main admin_init function used to hook into and register stuff and init plugin settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		register_setting( 'discussion', 'gravatar_invitation_email' );
		add_settings_field(
			'gravatar_invitation_email',
			__( 'Invitation', 'gravatar-enhanced' ),
			[ $this, 'display_checkbox_setting' ],
			'discussion',
			'avatars',
			array(
				'id' => 'gravatar_invitation_email',
				'label' => __( 'Send Gravatar Invitations', 'gravatar-enhanced' ),
				'description' => __( 'Send a nice email to commenters without a Gravatar, inviting them to sign up for one!', 'gravatar-enhanced' ),
			)
		);

		register_setting( 'discussion', 'gravatar_invitation_message' );
		add_action( 'post_gravatar_invitation_email', [ $this, 'display_message_setting' ] );

		if ( ! get_option( 'gravatar_invitation_message' ) ) {
			update_option( 'gravatar_invitation_message', $this->get_invitation_message() );
		}
	}

	/**
	 * Checks to see if a given email has an associated gravatar.
	 *
	 * @since 0.1
	 * @param mixed $email
	 * @return bool
	 */
	private function has_gravatar( $email ) {
		if ( empty( $email ) ) {
			return false;
		}

		$email_hash = md5( strtolower( $email ) );

		$host = 'https://secure.gravatar.com';
		$url = sprintf( '%s/avatar/%s?d=404', $host, $email_hash );
		$request = new WP_Http();
		$result = $request->request( $url, array( 'method' => 'GET' ) );

		// If gravatar returns a 404, email doesn't have a gravatar attached
		// @phpstan-ignore-next-line
		if ( is_array( $result ) && isset( $result['response']['code'] ) && $result['response']['code'] == 404 ) {
			return false;
		}

		// For all other cases, let's assume we do
		return true;
	}

	/**
	 * Build the key we use to store comment notifications.
	 *
	 * @param mixed $email
	 * @return string
	 */
	private function get_notification_key( $email ) {
		return sprintf( self::COMMENT_META_KEY . '%s', md5( strtolower( $email ) ) );
	}

	/**
	 * The default invitation message
	 *
	 * @return string
	 */
	private function get_invitation_message() {
		$message = __(
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

		return stripslashes( $message );
	}


	/**
	 * Mark the commenter as notified.
	 *
	 * @param string $email
	 * @param WP_Comment $comment
	 * @return void
	 */
	private function set_notified_commenter( $email, $comment ) {
		update_metadata( 'comment', (int) $comment->comment_ID, $this->get_notification_key( $email ), 1 );
	}

	/**
	 * Check to see if we've notified the commenter already.
	 *
	 * @param string $email
	 * @return bool
	 */
	private function have_notified_commenter( $email ) {
		global $wpdb;
		$table = _get_meta_table( 'comment' );

		// phpcs:ignore
		return $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM {$table} WHERE meta_key = %s LIMIT 1", $this->get_notification_key( $email ) ) );
	}

	/**
	 * Send gravatar invitation to commenters if enabled, if they don't have a gravatar and we haven't notified them already.
	 *
	 * @since 0.1
	 * @param mixed $email
	 * @param mixed $comment
	 * @return void
	 */
	private function notify_commenter( $email, $comment ) {
		// Check that it's a comment and that we have an email address
		if ( ! in_array( $comment->comment_type, array( '', 'comment' ), true ) || ! $email ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );

		if ( is_null( $post ) || ! isset( $_SERVER['SERVER_NAME'] ) ) {
			return;
		}

		// Check that the post type supports gravatar invitations
		if ( ! post_type_supports( $post->post_type, 'gravatar_invitation_email' ) ) {
			return;
		}

		if ( ! $this->has_gravatar( $email ) && ! $this->have_notified_commenter( $email ) ) {
			if ( is_multisite() ) {
				$sitename = get_current_site()->site_name;
			} else {
				$sitename = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}

			/* translators: %s: site name */
			$subject = sprintf( __( '[%s] Gravatar Invitation' ), $sitename );
			$subject = apply_filters( 'gravatar_enhanced_invitation_subject', $subject, $comment );

			$message = stripslashes( get_option( 'gravatar_invitation_message' ) );

			if ( ! $message ) {
				$message = $this->get_invitation_message();
			}

			// Just in case we're missing the signup URL
			if ( strpos( $message, 'GRAVATAR_URL' ) === false ) {
				$message .= "\n\n" . __( 'Sign up now: ', 'gravatar-enhanced' ) . 'GRAVATAR_URL';
			}

			// TODO: Need a better way to handle these for i18n since this does not translate well.
			$message = str_replace( 'SITE_NAME', $sitename, $message );
			$message = str_replace( 'POST_NAME', $post->post_title, $message );
			$message = str_replace( 'COMMENTER_NAME', $comment->comment_author, $message );
			$message = str_replace( 'COMMENTER_EMAIL', $email, $message );
			$message = str_replace( 'COMMENTER_URL', $comment->comment_author_url, $message );
			$message = str_replace( 'GRAVATAR_URL', self::GRAVATAR_ENHANCED_SIGNUP_URL, $message );

			// Grab author of the post
			$post_author = get_userdata( (int) $post->post_author );

			// Set From header to SITE_NAME
			$wp_email = 'wordpress@' . preg_replace( '#^www\.#', '', strtolower( $_SERVER['SERVER_NAME'] ) );

			// If the post author has a valid email, set the reply to the email 'from' them.
			$reply_name = ! empty( $post_author->user_email ) ? $post_author->display_name : $sitename;
			$reply_email = ! empty( $post_author->user_email ) ? $post_author->user_email : get_option( 'admin_email' );

			$message_headers = array(
				'from' => sprintf( 'From: "%1$s" <%2$s>', $sitename, $wp_email ),
				'type' => sprintf( 'Content-Type: %1$s; charset="%2$s"', 'text/plain', get_option( 'blog_charset' ) ),
				'replyto' => sprintf( 'Reply-To: %1$s <%2$s>', $reply_name, $reply_email ),
			);

			// Pass through filters
			$message = apply_filters( 'gravatar_enhanced_invitation_message', $message, $comment );
			$message_headers = apply_filters( 'gravatar_enhanced_invitation_message_headers', $message_headers, $comment );
			$message_headers = implode( "\n", $message_headers );

			wp_mail( $email, $subject, $message, $message_headers );

			$this->set_notified_commenter( $email, $comment );
		}
	}

	/**
	 * Callback to handle output for gravatar checkbox settings
	 *
	 * @param array{id: string, description: string, label: string} $args
	 * @return void
	 */
	public function display_checkbox_setting( $args ) {
		$value = get_option( $args['id'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $args['id'] ); ?>">
				<input name="<?php echo esc_attr( $args['id'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" type="checkbox" value="1" <?php echo checked( 1, $value, false ); ?> />

				<?php echo $args['label']; ?>

				<?php if ( $args['description'] ) : ?>
					<br />
					<span class="description"><?php echo $args['description']; ?></span>
				<?php endif; ?>
			</label>
		</p>
		<?php
		do_action( "post_{$args['id']}", $args );
	}

	/**
	 * Adds a textbox to allow users to configure the invitation message
	 *
	 * @return void
	 */
	public function display_message_setting() {
		$value = get_option( 'gravatar_invitation_message' );
		?>
		<p>
			<label for="gravatar_invitation_message">
				<?php esc_html_e( 'Customize the invitation message:', 'gravatar-enhanced' ); ?>
			</label>
		</p>
		<p>
			<textarea
				id="gravatar_invitation_message"
				name="gravatar_invitation_message"
				rows="10"
				cols="50"
				class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
			<br />
			<label for="gravatar_invitation_message">
				<span class="description">
					<?php esc_html_e( 'Why not send your commenters a personalized message? You can use placeholders like COMMENTER_NAME, COMMENTER_EMAIL, COMMENTER_URL, SITE_URL, and POST_NAME. Make sure to include GRAVATAR_URL somewhere in the message!', 'gravatar-enhanced' ); ?>
				</span>
			</label>
		</p>
		<?php
	}

	/**
	 * Handle when new comments are created.
	 * We have to hook into wp_insert_comment too because it doesn't call transition_comment_status :(
	 *
	 * @param mixed $id
	 * @param mixed $comment
	 * @return void
	 */
	public function insert_comment( $id, $comment ) {
		$comment_status = $comment->comment_approved;

		// We only send emails for approved comments
		if ( empty( $comment_status ) || ! in_array( $comment_status, array( 1, '1', 'approved' ), true ) ) {
			return;
		}

		$this->notify_commenter( $comment->comment_author_email, $comment );
	}

	/**
	 * Handle when new comments are updated or approved.
	 *
	 * @param mixed $new_status
	 * @param mixed $old_status
	 * @param mixed $comment
	 * @return void
	 **/
	public function transition_comment( $new_status, $old_status, $comment ) {
		// We only send emails for approved comments
		if ( 'approved' !== $new_status || 'approved' === $old_status ) {
			return;
		}

		// Only send emails for comments less than a week old
		if ( get_comment_date( 'U', $comment->comment_ID ) < strtotime( apply_filters( 'gravatar_enhanced_invitation_time_limit', '-1 week' ) ) ) {
			return;
		}

		$this->notify_commenter( $comment->comment_author_email, $comment );
	}
}
