<?php

namespace Automattic\Gravatar\GravatarEnhanced\Hovercards;

class Hovercards {
	const GRAVATAR_ENHANCED_HOVERCARD_URL = 'https://unpkg.com/@gravatar-com/hovercards@0.8.0';
	const GRAVATAR_ENHANCED_HOVERCARD_STYLES_URL = 'https://unpkg.com/@gravatar-com/hovercards@0.8.0/dist/style.css';
	const GRAVATAR_ENHANCED_HOVERCARD_VERSION = 'e';

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_add_hovercards' ] );
	}

	/**
	 * Remove the hovercard options
	 *
	 * @return void
	 */
	public function uninstall() {
		delete_option( 'gravatar_hovercards' );
	}

	/**
	 * Main admin_init function used to hook into and register stuff and init plugin settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		register_setting( 'discussion', 'gravatar_hovercards' );

		add_settings_field(
			'gravatar_hovercards',
			__( 'Hovercards', 'gravatar-enhanced' ),
			[ $this, 'display_checkbox_setting' ],
			'discussion',
			'avatars',
			array(
				'id' => 'gravatar_hovercards',
				'label' => __( 'Enable Gravatar Hovercards', 'gravatar-enhanced' ),
				'description' => __( 'Gravatar Hovercards show information about a person: name, bio, pictures, and their contact info at other services they use on the web like Twitter, Facebook or LinkedIn. <a href="http://blog.gravatar.com/2010/10/06/gravatar-hovercards-on-wordpress-com/" title="Opens new window" target="_blank">Learn More &raquo;</a>', 'gravatar-enhanced' ),
			)
		);
	}

	/**
	 * Callback to handle output for gravatar checkbox settings
	 *
	 * @param array{id: string, description: string, label: string} $args
	 * @return void
	 */
	public function display_checkbox_setting( $args ) {
		$value = get_option( $args['id'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $args['id'] ); ?>">
				<input value="1" name="<?php echo esc_attr( $args['id'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" type="checkbox" <?php echo checked( 1, $value, false ); ?> />

				<?php echo $args['label']; ?>

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
		if ( get_option( 'gravatar_hovercards' ) ) {
			wp_enqueue_script( 'gravatar-enhanced-js', self::GRAVATAR_ENHANCED_HOVERCARD_URL, [], self::GRAVATAR_ENHANCED_HOVERCARD_VERSION, true );
			wp_enqueue_style( 'gravatar-enhanced-style', self::GRAVATAR_ENHANCED_HOVERCARD_STYLES_URL, [], self::GRAVATAR_ENHANCED_HOVERCARD_VERSION );
			wp_add_inline_script( 'gravatar-enhanced-js', 'document.addEventListener( \'DOMContentLoaded\', () => { if ( Gravatar.Hovercards ) { const hovercards = new Gravatar.Hovercards(); hovercards.attach( document.body ); } } );' );
		}
	}
}
