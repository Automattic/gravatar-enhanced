<?php

namespace Automattic\Gravatar\GravatarEnhanced\Patterns;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * How to add a new pattern:
 *
 * 1. Add a new entry in the `$patterns` array with its `type` and `number`
 * 2. Create a file in `classes/patterns` named `type-number.php` (e.g., `grid-pattern-1.php`) containing the pattern content
 * 3. Update the block pattern settings in `register_patterns` if needed
 */
class Patterns {
	/**
	 * Patterns.
	 *
	 * @var array
	 */
	private $patterns = [
		[
			'type' => 'grid-pattern',
			'number' => 1,
		],
		// TODO: Add more patterns...
	];

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_pattern_category' ] );
		add_action( 'init', [ $this, 'register_patterns' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_view_style' ] );
		add_action( 'admin_init', [ $this, 'enqueue_editor_style' ] );
		// TODO: Ensure the CSS file is enqueued in all the necessary places.
	}

	/**
	 * Register pattern category.
	 *
	 * @return void
	 */
	public function register_pattern_category() {
		register_block_pattern_category(
			'gravatar',
			[
				'label' => __( 'Gravatar', 'gravatar-enhanced' ),
				'description' => __( 'A collection of patterns for displaying Gravatar profiles.', 'gravatar-enhanced' ),
			]
		);
	}

	/**
	 * Register patterns.
	 *
	 * @return void
	 */
	public function register_patterns() {
		foreach ( $this->patterns as $pattern ) {
			$pattern_name = $pattern['type'] . '-' . $pattern['number'];
			$content = $this->get_pattern_content( $pattern_name );

			if ( ! $content ) {
				continue;
			}

			$title = '';
			$description = '';
			$keywords = [ 'gravatar', 'profile', 'profiles', 'pattern', 'layout' ];

			switch ( $pattern['type'] ) {
				case 'grid-pattern':
					// translators: %d: Pattern number.
					$title = sprintf( __( 'Gravatar profiles grid layout %d', 'gravatar-enhanced' ), $pattern['number'] );
					// translators: %d: Pattern number.
					$description = sprintf( __( 'Grid layout %d to display Gravatar profiles.', 'gravatar-enhanced' ), $pattern['number'] );
					$keywords[] = 'grid';
					break;
				// TODO: Add more types...
			}

			register_block_pattern(
				'gravatar-enhanced/' . $pattern_name,
				[
					'title' => $title,
					'description' => $description,
					'categories' => [ 'gravatar' ],
					'keywords' => $keywords,
					'content' => $content,
				]
			);
		}
	}

	/**
	 * Enqueue view styles for patterns.
	 *
	 * @return void
	 */
	public function enqueue_view_style() {
		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/patterns.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_style(
			'gravatar-enhanced-patterns',
			plugins_url( 'build/style-patterns.css', GRAVATAR_ENHANCED_PLUGIN_FILE ),
			[],
			$assets['version']
		);
	}

	/**
	 * Enqueue editor styles for patterns.
	 *
	 * @return void
	 */
	public function enqueue_editor_style() {
		add_editor_style( plugins_url( 'build/style-patterns.css', GRAVATAR_ENHANCED_PLUGIN_FILE ) );
	}

	/**
	 * Uninstall patterns.
	 *
	 * @return void
	 */
	public function uninstall() {
		unregister_block_pattern_category( 'gravatar' );

		foreach ( $this->patterns as $pattern ) {
			unregister_block_pattern( 'gravatar-enhanced/' . $pattern['type'] . '-' . $pattern['number'] );
		}
	}

	/**
	 * Get pattern content.
	 *
	 * @param string $pattern_name Pattern name.
	 * @return string|null
	 */
	private function get_pattern_content( $pattern_name ) {
		$pattern_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/classes/patterns/' . $pattern_name . '.php';

		if ( ! file_exists( $pattern_file ) ) {
			return null;
		}

		return file_get_contents( $pattern_file );
	}
}
