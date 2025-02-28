<?php

namespace Automattic\Gravatar\GravatarEnhanced\Hovercards;

use Automattic\Gravatar\GravatarEnhanced\Module;

class Hovercards implements Module {
	/**
	 * @var string
	 */
	const OPTION_HOVERCARDS = 'gravatar_hovercards';

	/**
	 * @var string
	 */
	const FILTER_GRAVATAR_HOVERCARDS_MODULE_ENABLED = 'gravatar_enhanced_hovercards_module_enabled';

	/**
	 * @return void
	 */
	public function init() {
		// Delay loading until 'init' to access the Jetpack class and check if the module needs disabling.
		add_action( 'init', [ $this, 'maybe_load' ] );
	}

	/**
	 * Load the hovercards module if it's not disabled.
	 *
	 * @return void
	 */
	public function maybe_load() {
		// Bail if the module is disabled.
		if ( $this->is_module_disabled() ) {
			return;
		}
		add_action( 'admin_init', [ $this, 'maybe_register_enabling_setting' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_add_hovercards' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'maybe_add_hovercards' ] );
	}

	/**
	 * Remove the hovercard options
	 *
	 * @return void
	 */
	public function uninstall() {
		delete_option( self::OPTION_HOVERCARDS );
	}

	/**
	 * Maybe register the enabling setting for the hovercards.
	 * By default, hovercards are enabled and can only be disabled by a filter.
	 * We check the legacy option to make sure we don't enable it for people who explicitly disabled it.
	 *
	 * @return void
	 */
	public function maybe_register_enabling_setting() {
		if ( ! $this->is_hovercards_option_enabled() ) {
			register_setting( 'discussion', self::OPTION_HOVERCARDS );

			add_settings_field(
				self::OPTION_HOVERCARDS,
				__( 'Hovercards', 'gravatar-enhanced' ),
				[ $this, 'display_checkbox_setting' ],
				'discussion',
				'avatars',
				array(
					'id' => self::OPTION_HOVERCARDS,
					'label' => __( 'Enable Gravatar Hovercards', 'gravatar-enhanced' ),
					'description' => __( 'Gravatar Hovercards are now enabled by default. <strong>Once enabled this setting will disappear.</strong>', 'gravatar-enhanced' ),
				)
			);
		}
	}

	/**
	 * Callback to handle output for gravatar checkbox settings
	 *
	 * @param array{id: string, description: string, label: string} $args
	 * @return void
	 */
	public function display_checkbox_setting( $args ) {
		// description contains HTML so is not escaped
		$value = get_option( $args['id'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $args['id'] ); ?>">
				<input value="1" name="<?php echo esc_attr( $args['id'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" type="checkbox" <?php echo checked( 1, $value, false ); ?> />

				<?php echo esc_html( $args['label'] ); ?>

				<?php if ( $args['description'] ) : ?>
					<br />
					<span class="description"><?php echo $args['description']; ?></span>
				<?php endif; ?>
			</label>
		</p>
		<?php
		do_action( "post_{$args['id']}", $args );
	}

	/**
	 * Initialise Gravatar Hovercards, if enabled.
	 *
	 * @return void
	 */
	public function maybe_add_hovercards() {
		if ( $this->is_hovercards_option_enabled() ) {
			$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/hovercards.asset.php';
			$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

			wp_enqueue_script( 'gravatar-enhanced-hovercards', plugins_url( 'build/hovercards.js', GRAVATAR_ENHANCED_PLUGIN_FILE ), $assets['dependencies'], $assets['version'], true );
			wp_register_style( 'gravatar-enhanced-hovercards', plugins_url( 'build/style-hovercards.css', GRAVATAR_ENHANCED_PLUGIN_FILE ), [], $assets['version'] );
			wp_enqueue_style( 'gravatar-enhanced-hovercards' );
		}
	}

	/**
	 * Check if the module is disabled, by either a filter or a detected incompatibility.
	 *
	 * @return bool
	 */
	private function is_module_disabled() {
		// Check if module is manually disabled by the filter.
		if ( ! apply_filters( self::FILTER_GRAVATAR_HOVERCARDS_MODULE_ENABLED, true ) ) {
			return true; // Disabled by filter.
		}

		// Check if Jetpack is active and has the Gravatar Hovercards module enabled.
		if ( class_exists( '\Jetpack' ) && \Jetpack::is_module_active( 'gravatar-hovercards' ) ) {
			return true; // Disabled due to Jetpack Gravatar Hovercards module.
		}

		return false;
	}

	/**
	 * Check if Hovercards option is enabled or not.
	 * By default, it is enabled, but we check the legacy option for backwards compatibility.
	 *
	 * @return bool
	 */
	private function is_hovercards_option_enabled() {
		// @deprecated since 0.3.0 – use `gravatar_enhanced_hovercards_module_enabled` filter to disable hovercards.
		return boolval( get_option( self::OPTION_HOVERCARDS, true ) );
	}
}
