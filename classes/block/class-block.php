<?php

namespace Automattic\Gravatar\GravatarEnhanced\Block;

use Automattic\Gravatar\GravatarEnhanced\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Block implements Module {
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
