<?php

namespace Automattic\Gravatar\GravatarEnhanced\AuthorArchive;

use Automattic\Gravatar\GravatarEnhanced\Module;
use WP_Block;

require_once __DIR__ . '/class-author-archive-options.php';
require_once __DIR__ . '/class-author-archive-preferences.php';

class AuthorArchive implements Module {
	/**
	 * @var Options
	 */
	private $options;

	/**
	 * @param Preferences $preferences
	 */
	public function __construct( $preferences ) {
		$this->options = $preferences->get_options();
	}


	/**
	 * @return void
	 */
	public function init() {
		if ( $this->options->auto_show === Options::AUTO_SHOW_OFF ) {
			return;
		}

		add_filter( 'render_block_core/query', [ $this, 'render_block_core_query' ], 10, 2 );
	}

	/**
	 * Render block core query.
	 *
	 * @param string $block_content The block content.
	 * @param WP_Block $block The block.
	 * @return string
	 */
	public function render_block_core_query( $block_content, $block ) {
		if ( ! is_author() ) {
			return $block_content;
		}

		// Add card to end of content
		$blocks = parse_blocks( '<!-- wp:gravatar/block /-->' );
		$card = render_block( $blocks[0] );

		if ( $this->options->auto_show === Options::AUTO_SHOW_TOP ) {
			$block_content = $card . $block_content;
		} elseif ( $this->options->auto_show === Options::AUTO_SHOW_BOTTOM ) {
			$block_content .= $card;
		}

		return $block_content;
	}

	/**
	 * @return void
	 */
	public function uninstall() {}
}
