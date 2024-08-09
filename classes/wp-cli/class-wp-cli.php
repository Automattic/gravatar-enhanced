<?php

namespace Automattic\Gravatar\GravatarEnhanced\WpCli;

use Automattic\Gravatar\GravatarEnhanced\Proxy;
use WP_CLI_Command;
use WP_CLI;

/**
 * Gravatar WP Cli
 *
 * @phpstan-type ProxyArray array{
 *   flush?: bool
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
}

WP_CLI::add_command( 'gravatar', 'Automattic\Gravatar\GravatarEnhanced\WpCli\GravatarCli' );
