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
				'description' => __( 'Patterns for Gravatar profiles.', 'gravatar-enhanced' ),
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
		$base_keywords = [ 'gravatar', 'profile', 'profiles', 'pattern', 'layout' ];

		// Grid patterns.
		foreach ( $this->get_grid_patterns() as $pattern ) {
			register_block_pattern(
				'gravatar-enhanced/' . $pattern['name'],
				[
					'title' => sprintf( __( 'Grid Pattern %d', 'gravatar-enhanced' ), $pattern['number'] ),
					'description' => sprintf( __( 'Grid layout %d for Gravatar profiles.', 'gravatar-enhanced' ), $pattern['number'] ),
					'categories' => [ 'gravatar' ],
					'keywords' => array_merge( $base_keywords, [ 'grid' ] ),
					'content' => $this->get_pattern_content( $pattern['name'] ),
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
	 * @return string
	 */
	private function get_pattern_content( $pattern_name ) {
		$pattern_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/classes/patterns/' . $pattern_name . '.php';

		if ( file_exists( $pattern_file ) ) {
			return file_get_contents( $pattern_file );
		}

		return '';
	}
}
