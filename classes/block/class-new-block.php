<?php

namespace Automattic\Gravatar\GravatarEnhanced\Block;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class NewBlock {
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
		$block_dir = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/new-block';

		register_block_type( $block_dir . '/main' );
		register_block_type( $block_dir . '/column' );
		register_block_type( $block_dir . '/image' );
		register_block_type( $block_dir . '/name' );
		register_block_type( $block_dir . '/paragraph' );
		register_block_type( $block_dir . '/link' );
	}
}
