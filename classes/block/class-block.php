<?php

namespace Automattic\Gravatar\GravatarEnhanced\Block;

use Automattic\Gravatar\GravatarEnhanced\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Block implements Module {
	/**
	 * Keep the editor placeholder limited to the text fields rendered before a
	 * profile has loaded. Block attributes are supplied by post authors, so URL
	 * and nested profile fields must not be forwarded to the browser.
	 *
	 * @param mixed $profile The saved placeholder profile attribute.
	 * @return array<string, string>
	 */
	public static function sanitize_placeholder_profile( $profile ) {
		if ( ! is_array( $profile ) ) {
			return [];
		}

		$allowed_fields = [
			'display_name',
			'job_title',
			'company',
			'location',
			'description',
		];
		$sanitized = [];

		foreach ( $allowed_fields as $field ) {
			if ( ! isset( $profile[ $field ] ) || ! is_scalar( $profile[ $field ] ) ) {
				continue;
			}

			$sanitized[ $field ] = sanitize_text_field( (string) $profile[ $field ] );
		}

		return $sanitized;
	}

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'create_block_gravatar_block_block_init' ] );
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 *
	 * @return void
	 */
	public function create_block_gravatar_block_block_init() {
		$block_dir = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/block';

		register_block_type( $block_dir . '/' );
		register_block_type( $block_dir . '/editor-blocks/group' );
		register_block_type( $block_dir . '/editor-blocks/image' );
		register_block_type( $block_dir . '/editor-blocks/name' );
		register_block_type( $block_dir . '/editor-blocks/paragraph' );
		register_block_type( $block_dir . '/editor-blocks/link' );
	}

	public function uninstall() {}
}
