<?php

namespace Automattic\Gravatar\GravatarEnhanced\Patterns;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Patterns {
	/**
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_pattern_category' ] );
		add_action( 'init', [ $this, 'register_patterns' ] );
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
	 * Get grid patterns.
	 *
	 * @return array
	 */
	private function get_grid_patterns() {
		return [
			[
				'number' => 1,
				'name' => 'grid-pattern-1',
			],
		];
	}

	/**
	 * Register patterns.
	 *
	 * @return void
	 */
	public function register_patterns() {
		$common_keywords = [ 'gravatar', 'profile', 'profiles', 'pattern', 'layout' ];

		// Grid patterns.
		foreach ( $this->get_grid_patterns() as $pattern ) {
			$content = $this->get_pattern_content( $pattern['name'] );

			if ( ! $content ) {
				continue;
			}

			register_block_pattern(
				'gravatar-enhanced/' . $pattern['name'],
				[
					// translators: %d: Pattern number.
					'title' => sprintf( __( 'Grid Layout %d for Gravatar profiles', 'gravatar-enhanced' ), $pattern['number'] ),
					// translators: %d: Pattern number.
					'description' => sprintf( __( 'Grid layout %d to display Gravatar profiles.', 'gravatar-enhanced' ), $pattern['number'] ),
					'categories' => [ 'gravatar' ],
					'keywords' => array_merge( $common_keywords, [ 'grid' ] ),
					'content' => $content,
				]
			);
		}

		// TODO: Add more patterns...
	}

	/**
	 * Uninstall patterns.
	 *
	 * @return void
	 */
	public function uninstall() {
		unregister_block_pattern_category( 'gravatar' );

		$patterns = array_merge( $this->get_grid_patterns() );

		foreach ( $patterns as $pattern ) {
			unregister_block_pattern( 'gravatar-enhanced/' . $pattern['name'] );
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
