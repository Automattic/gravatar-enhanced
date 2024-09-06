<?php

namespace Automattic\Gravatar\GravatarEnhanced\WpCli;

use Automattic\Gravatar\GravatarEnhanced\Proxy;
use Automattic\Gravatar\GravatarEnhanced\Email;
use Automattic\Gravatar\GravatarEnhanced\Options;
use Automattic\Gravatar\GravatarEnhanced\Plugin;
use WP_CLI_Command;
use WP_CLI;
use WP_Comment;

/**
 * Gravatar WP Cli
 *
 * @phpstan-type ProxyArray array{
 *   flush?: bool
 * }
 *
 * @phpstan-type NotifyArray array{
 *   commenter_name?: string,
 *   commenter_url?: string
 * }
 */
class GravatarCli extends WP_CLI_Command {
	/**
	 * View a list of all the proxied avatars
	 *
	 * ## OPTIONS
	 *
	 * [--flush]
	 * : Delete all proxied avatars
	 *
	 * @param string[] $args
	 * @param ProxyArray $assoc_args
	 * @return void
	 */
	public function proxy( $args, $assoc_args ) {
		$proxy = new Proxy\ProxyCache();
		$entries = $proxy->get_entries();

		if ( ! empty( $assoc_args['flush'] ) ) {
			$result = $proxy->flush();

			if ( $result ) {
				WP_Cli::line( sprintf( '%d entries have been flushed from the proxy cache', count( $entries ) ) );
			} else {
				WP_CLI::error( 'Failed to flush the proxy cache' );
			}
		} else {
			array_walk(
				$entries,
				function ( $entry ) {
					WP_CLI::line( $entry );
				}
			);
		}
	}

	/**
	 * Send an email notification to an address
	 *
	 * ## OPTIONS
	 *
	 * <email>
	 * : The email address to send the notification to
	 *
	 * <post_ID>
	 * : The post ID
	 *
	 * [--commenter_name]
	 * : The commenter name
	 *
	 * [--commenter_url]
	 * : The commenter URL
	 *
	 * @param string[] $args
	 * @param NotifyArray $assoc_args
	 * @return void
	 */
	public function notify( $args, $assoc_args ) {
		$email = $args[0];
		$post_id = $args[1];

		$post = get_post( (int) $post_id );
		if ( ! $post ) {
			WP_CLI::error( 'Post not found' );
		}

		/** @var WP_Comment */
		$comment = (object) [
			'comment_author' => $assoc_args['commenter_name'] ?? 'Someone',
			'comment_author_url' => $assoc_args['commenter_url'] ?? 'https://example.com',
		];

		$options = new Options\SavedOptions( Plugin::OPTION_NAME_LAZY, false );
		$notify = new Email\EmailNotification( new Email\Preferences( $options ) );
		$result = $notify->send_invitation( $email, $post, $comment, 'wp.cli' );

		if ( $result ) {
			WP_Cli::line( 'Email has been sent' );
		} else {
			WP_CLI::error( 'Failed to send the email' );
		}
	}
}

WP_CLI::add_command( 'gravatar', 'Automattic\Gravatar\GravatarEnhanced\WpCli\GravatarCli' );
