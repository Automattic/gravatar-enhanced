<?php

namespace Automattic\Gravatar\GravatarEnhanced\Block;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Block {
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
		register_block_type( $block_dir . '/blocks/column' );
		register_block_type( $block_dir . '/blocks/image' );
		register_block_type( $block_dir . '/blocks/name' );
		register_block_type( $block_dir . '/blocks/paragraph' );
		register_block_type( $block_dir . '/blocks/link' );
	}
}
