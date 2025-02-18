<?php

namespace Automattic\Gravatar\GravatarEnhanced\Comments;

require_once __DIR__ . '/class-comments-options.php';
require_once __DIR__ . '/class-comments-preferences.php';

class Comments {
	const OPTION_COMMENTS = 'gravatar_comments';

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
		// Are we enabled?
		if ( ! $this->options->enabled ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
		add_action( 'comment_form_field_email', [ $this, 'comment_form_field_email' ] );
		add_action( 'comment_form_logged_in', [ $this, 'comment_form_logged_in' ] );
		add_filter( 'comment_form_fields', [ $this, 'comment_form_fields' ] );
	}

	/**
	 * Rearrange the order so the email comes before name
	 *
	 * @param array<string, string> $fields
	 * @return array<string, string>
	 */
	public function comment_form_fields( array $fields ): array {
		if ( isset( $fields['email'] ) && isset( $fields['author'] ) ) {
			$email_field = $fields['email'];
			unset( $fields['email'] );
			$reordered_fields = [];

			foreach ( $fields as $key => $value ) {
				if ( $key === 'author' ) {
					$reordered_fields['email'] = $email_field;
				}
				$reordered_fields[ $key ] = $value;
			}

			return $reordered_fields;
		}

		return $fields;
	}

	/**
	 * Enqueue a JS file.
	 *
	 * @return void
	 */
	public function wp_enqueue_scripts() {
		// Is this a commentable post?
		if ( ! is_singular() || ! comments_open() ) {
			return;
		}

		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/comments.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_script( 'gravatar-enhanced-comments', plugins_url( 'build/comments.js', GRAVATAR_ENHANCED_PLUGIN_FILE ), $assets['dependencies'], $assets['version'], true );

		wp_register_style( 'gravatar-enhanced-comments', plugins_url( 'build/style-comments.css', GRAVATAR_ENHANCED_PLUGIN_FILE ), [], $assets['version'] );
		wp_enqueue_style( 'gravatar-enhanced-comments' );

		$comment_data = [
			'locale' => 'en',
		];

		// Check if user is logged in
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();

			$current_user_locale = get_user_locale( $current_user );
			$current_user_locale = (string) preg_replace( '/_.*$/', '', $current_user_locale );
			$comment_data['email'] = $current_user->user_email;
			$comment_data['locale'] = 'en' === $current_user_locale ? '' : $current_user_locale;
		}

		wp_localize_script(
			'gravatar-enhanced-comments',
			'gravatarEnhancedComments',
			$comment_data
		);
	}

	/**
	 * Output the Gravatar-enhanced comments form field for logged-out user.
	 *
	 * @param string $field
	 * @return void
	 */
	public function comment_form_field_email( $field ) {
		echo $field;

		?>
		<div class="gravatar-enhanced-comments gravatar-enhanced-comments--hidden">
			<img src="" alt="<?php echo esc_attr( __( 'Gravatar profile', 'gravatar-enhanced' ) ); ?>" />

			<button type="button"><?php echo esc_html( __( 'Edit', 'gravatar-enhanced' ) ); ?></button>
		</div>
		<?php
	}

	/**
	 * Output the Gravatar-enhanced comments form field for logged-in user.
	 *
	 * @param string $text
	 * @return void
	 */
	public function comment_form_logged_in( $text ) {
		echo $text;

		?>
		<div class="gravatar-enhanced-comments">
			<?php echo get_avatar( wp_get_current_user()->user_email, 128 ); ?>

			<button type="button"><?php echo esc_html( __( 'Edit', 'gravatar-enhanced' ) ); ?></button>
		</div>
		<?php
	}
}
