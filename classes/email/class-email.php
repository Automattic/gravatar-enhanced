<?php

namespace Automattic\Gravatar\GravatarEnhanced\Email;

use Automattic\Gravatar\GravatarEnhanced\Module;
use WP_Http;
use WP_Comment;
use WP_Post;

require_once __DIR__ . '/class-email-options.php';
require_once __DIR__ . '/class-email-preferences.php';

class EmailNotification implements Module {
	/**
	 * @var Preferences
	 */
	private $preferences;

	/**
	 * @var Options
	 */
	private $options;

	const GRAVATAR_ENHANCED_SIGNUP_URL = 'https://gravatar.com/connect/?email=';

	const COMMENT_META_KEY = 'gravatar_invite_';
	const POST_TYPE_FEATURE = 'gravatar_email';

	const FILTER_INVITATION_SUBJECT = 'gravatar_enhanced_invitation_subject';
	const FILTER_INVITATION_MESSAGE = 'gravatar_enhanced_invitation_message';
	const FILTER_INVITATION_MESSAGE_HEADERS = 'gravatar_enhanced_invitation_message_headers';
	const FILTER_INVITATION_TIME_LIMIT = 'gravatar_enhanced_invitation_time_limit';

	/**
	 * @param Preferences $preferences
	 */
	public function __construct( $preferences ) {
		$this->preferences = $preferences;
	}

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'wp_insert_comment', [ $this, 'plugin_init' ], 9 );
		add_action( 'transition_comment_status', [ $this, 'transition_comment' ], 10, 3 );
		add_action( 'wp_insert_comment', [ $this, 'insert_comment' ], 10, 2 );
	}

	/**
	 * Remove the notifications options and comment meta
	 *
	 * @return void
	 */
	public function uninstall() {
		global $wpdb;

		$table = _get_meta_table( 'comment' );

		// phpcs:ignore
		$wpdb->query( "DELETE FROM {$table} WHERE meta_key LIKE '" . self::COMMENT_META_KEY . "%'" );
	}

	/**
	 * Main init function. Fired by `comment_post` when a comment is posted - we're only interested in doing things then.
	 *
	 * @return void
	 */
	public function plugin_init() {
		$this->options = $this->preferences->get_options();

		if ( $this->options->enabled ) {
			foreach ( [ 'post', 'page' ] as $post_type ) {
				add_post_type_support( $post_type, self::POST_TYPE_FEATURE );
			}
		}
	}

	/**
	 * Checks to see if a given email has an associated gravatar.
	 *
	 * @since 0.1
	 * @param string $email
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
	 * @param string $email
	 * @return string
	 */
	private function get_notification_key( $email ) {
		return sprintf( self::COMMENT_META_KEY . '%s', md5( strtolower( $email ) ) );
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
	 * @param string $target_email
	 * @param WP_Comment $comment
	 * @return void
	 */
	private function notify_commenter( $target_email, $comment ) {
		// Check that it's a comment and that we have an email address
		if ( ! in_array( $comment->comment_type, array( '', 'comment' ), true ) || ! $target_email ) {
			return;
		}

		if ( ! $comment->comment_post_ID ) {
			return;
		}

		$post = get_post( (int) $comment->comment_post_ID );

		if ( is_null( $post ) ) {
			return;
		}

		// Check that the post type supports gravatar invitations
		if ( ! post_type_supports( $post->post_type, self::POST_TYPE_FEATURE ) ) {
			return;
		}

		if ( ! $this->has_gravatar( $target_email ) && ! $this->have_notified_commenter( $target_email ) ) {
			$this->send_invitation( $target_email, $post, $comment );
			$this->set_notified_commenter( $target_email, $comment );
		}
	}

	/**
	 * Send the invitation email to the commenter
	 *
	 * @param string $target_email
	 * @param WP_Post $post
	 * @param WP_Comment $comment
	 * @param string|null $server_name
	 * @return bool
	 */
	public function send_invitation( $target_email, $post, $comment, $server_name = null ) {
		$server_name = $server_name ?? $_SERVER['SERVER_NAME'] ?? null;
		if ( is_null( $server_name ) ) {
			return false;
		}

		if ( is_multisite() ) {
			$sitename = get_current_site()->site_name;
		} else {
			$sitename = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		/* translators: %s: site name */
		$subject = sprintf( __( '[%s] Gravatar invitation' ), $sitename );
		$subject = apply_filters( self::FILTER_INVITATION_SUBJECT, $subject, $comment );

		$message = stripslashes( $this->options->message );

		$gravatar_url = self::GRAVATAR_ENHANCED_SIGNUP_URL . rawurlencode( $target_email );

		// Just in case we're missing the signup URL
		if ( strpos( $message, 'GRAVATAR_URL' ) === false ) {
			$message .= "\n\n" . __( 'Sign up now: ', 'gravatar-enhanced' ) . 'GRAVATAR_URL';
		}

		$message = str_replace( 'SITE_NAME', $sitename, $message );
		$message = str_replace( 'POST_NAME', $post->post_title, $message );
		$message = str_replace( 'COMMENTER_NAME', $comment->comment_author, $message );
		$message = str_replace( 'COMMENTER_EMAIL', $target_email, $message );
		$message = str_replace( 'COMMENTER_URL', $comment->comment_author_url, $message );
		$message = str_replace( 'GRAVATAR_URL', $gravatar_url, $message );

		// Grab author of the post
		$post_author = get_userdata( (int) $post->post_author );

		// Set From header to SITE_NAME
		$wp_email = 'wordpress@' . preg_replace( '#^www\.#', '', strtolower( $server_name ) );

		// If the post author has a valid email, set the reply to the email 'from' them.
		$reply_name = ! empty( $post_author->user_email ) ? $post_author->display_name : $sitename;
		$reply_email = ! empty( $post_author->user_email ) ? $post_author->user_email : get_option( 'admin_email' );

		$message_headers = array(
			'from' => sprintf( 'From: "%1$s" <%2$s>', $sitename, $wp_email ),
			'type' => sprintf( 'Content-Type: %1$s; charset="%2$s"', 'text/plain', get_option( 'blog_charset' ) ),
			'replyto' => sprintf( 'Reply-To: %1$s <%2$s>', $reply_name, $reply_email ),
		);

		// Pass through filters
		$message = apply_filters( self::FILTER_INVITATION_MESSAGE, $message, $comment );
		$message_headers = apply_filters( self::FILTER_INVITATION_MESSAGE_HEADERS, $message_headers, $comment );
		$message_headers = implode( "\n", $message_headers );

		return wp_mail( $target_email, $subject, $message, $message_headers );
	}

	/**
	 * Handle when new comments are created.
	 * We have to hook into wp_insert_comment too because it doesn't call transition_comment_status :(
	 *
	 * @param string $id
	 * @param WP_Comment $comment
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
	 * @param string $new_status
	 * @param string $old_status
	 * @param WP_Comment $comment
	 * @return void
	 **/
	public function transition_comment( $new_status, $old_status, $comment ) {
		// We only send emails for approved comments
		if ( 'approved' !== $new_status || 'approved' === $old_status ) {
			return;
		}

		// Only send emails for comments less than a week old
		if ( get_comment_date( 'U', (int) $comment->comment_ID ) < strtotime( apply_filters( self::FILTER_INVITATION_TIME_LIMIT, '-1 week' ) ) ) {
			return;
		}

		$this->notify_commenter( $comment->comment_author_email, $comment );
	}
}
