<?php

namespace Automattic\Gravatar\GravatarEnhanced\Settings;

trait SettingsCheckbox {
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
}
