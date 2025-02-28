<?php

namespace Automattic\Gravatar\GravatarEnhanced\Patterns;

use Automattic\Gravatar\GravatarEnhanced\Module;

/**
 * How to add a new pattern:
 *
 * 1. Add a new entry in the `$patterns` array with its `type` and `number`
 * 2. Create a file in `classes/patterns` named `type-number.php` (e.g., `grid-pattern-1.php`) containing the pattern content
 * 3. Update the block pattern settings in `register_patterns` if needed
 */
class Patterns implements Module {
	/**
	 * Patterns.
	 *
	 * @var array<array<string, int|string>>
	 */
	private $patterns = [
		[
			'type' => 'grid-pattern',
			'number' => 1,
		],
		[
			'type' => 'grid-pattern',
			'number' => 2,
		],
		[
			'type' => 'grid-pattern',
			'number' => 3,
		],
		[
			'type' => 'grid-pattern',
			'number' => 4,
		],
		[
			'type' => 'grid-pattern',
			'number' => 5,
		],
	];

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_pattern_category' ] );
		add_action( 'init', [ $this, 'register_patterns' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_shared_style' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_view_style' ] );
		add_action( 'enqueue_block_assets', [ $this, 'enqueue_shared_style' ] );
		add_action( 'enqueue_block_assets', [ $this, 'enqueue_edit_style' ] );
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
				'description' => __( 'A curated collection of block patterns to showcase Gravatar profiles.', 'gravatar-enhanced' ),
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
			$keywords = [ 'gravatar', 'profile', 'user', 'user profile', 'pattern', 'layout' ];

			switch ( $pattern['type'] ) {
				case 'grid-pattern':
					// translators: %d: Pattern number.
					$title = sprintf( __( 'Gravatar profiles, grid layout #%d', 'gravatar-enhanced' ), $pattern['number'] );
					// translators: %d: Pattern number.
					$description = sprintf( __( 'Grid layout #%d for displaying Gravatar profiles.', 'gravatar-enhanced' ), $pattern['number'] );
					$keywords = array_merge( $keywords, [ 'grid', 'profiles', 'profile grid', 'user profiles', 'team', 'team showcase' ] );
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
	 * Enqueue the shared style.
	 *
	 * @return void
	 */
	public function enqueue_shared_style() {
		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/patterns-shared.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_style(
			'gravatar-enhanced-patterns-shared',
			plugins_url( 'build/patterns-shared.css', GRAVATAR_ENHANCED_PLUGIN_FILE ),
			[],
			$assets['version']
		);
	}

	/**
	 * Enqueue the view style.
	 *
	 * @return void
	 */
	public function enqueue_view_style() {
		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/patterns-view.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_style(
			'gravatar-enhanced-patterns-view',
			plugins_url( 'build/patterns-view.css', GRAVATAR_ENHANCED_PLUGIN_FILE ),
			[],
			$assets['version']
		);
	}

	/**
	 * Enqueue the edit style.
	 *
	 * @return void
	 */
	public function enqueue_edit_style() {
		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/patterns-edit.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_style(
			'gravatar-enhanced-patterns-edit',
			plugins_url( 'build/patterns-edit.css', GRAVATAR_ENHANCED_PLUGIN_FILE ),
			[],
			$assets['version']
		);
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
	 * @return string|false
	 */
	private function get_pattern_content( $pattern_name ) {
		$pattern_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/classes/patterns/' . $pattern_name . '.php';

		if ( ! file_exists( $pattern_file ) ) {
			return false;
		}

		return file_get_contents( $pattern_file );
	}
}
