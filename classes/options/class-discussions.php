<?php

// phpcs:ignoreFile WordPress.Security.NonceVerification.NoNonceVerification
namespace Automattic\Gravatar\GravatarEnhanced\Options;

use Automattic\Gravatar\GravatarEnhanced\Proxy;
use Automattic\Gravatar\GravatarEnhanced\Email;
use Automattic\Gravatar\GravatarEnhanced\Avatar;
use Automattic\Gravatar\GravatarEnhanced\Analytics;
use Automattic\Gravatar\GravatarEnhanced\Comments;

require_once __DIR__ . '/class-saved-options.php';
require_once __DIR__ . '/class-migrate.php';

/**
 * @phpstan-import-type ProxyOptionsType from Proxy\Options
 */
class DiscussionsPage {
	/**
	 * @var SavedOptions
	 */
	private $auto_options;

	/**
	 * @var SavedOptions
	 */
	private $lazy_options;

	/**
	 * @var string[]
	 */
	private $enabled_modules;

	/**
	 * @param SavedOptions $auto_options
	 * @param SavedOptions $lazy_options
	 * @param string[] $enabled_modules The enabled modules, as defined in `class-plugin.php`.
	 */
	public function __construct( $auto_options, $lazy_options, $enabled_modules ) {
		$this->auto_options = $auto_options;
		$this->lazy_options = $lazy_options;
		$this->enabled_modules = $enabled_modules;
	}

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Main admin_init function used to hook into and register stuff and init plugin settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		add_action( 'load-options.php', [ $this, 'save_settings' ] );

		if ( in_array( 'avatar', $this->enabled_modules, true ) ) {
			add_settings_field(
				'avatar-options',
				__( 'Avatar Options', 'gravatar-enhanced' ),
				[ $this, 'display_avatar_settings' ],
				'discussion',
				'avatars'
			);
		}

		if ( in_array( 'email', $this->enabled_modules, true ) ) {
			add_settings_field(
				'email-options',
				__( 'Invitation', 'gravatar-enhanced' ),
				[ $this, 'display_email_settings' ],
				'discussion',
				'avatars'
			);
		}

		if ( in_array( 'proxy', $this->enabled_modules, true ) ) {
			add_settings_field(
				'proxy-options',
				__( 'Avatar Proxy', 'gravatar-enhanced' ),
				[ $this, 'display_proxy_settings' ],
				'discussion',
				'avatars'
			);
		}

		if ( in_array( 'analytics', $this->enabled_modules, true ) ) {
			add_settings_field(
				'analytics-options',
				__( 'Anonymous Analytics', 'gravatar-enhanced' ),
				[ $this, 'display_analytics_settings' ],
				'discussion',
				'avatars'
			);
		}

		if ( in_array( 'comments', $this->enabled_modules, true ) ) {
			add_settings_field(
				'comment-options',
				__( 'Gravatar Comments', 'gravatar-enhanced' ),
				[ $this, 'display_comment_settings' ],
				'discussion',
				'avatars'
			);
		}
	}

	/**
	 * Callback to show avatar options
	 *
	 * @return void
	 */
	public function display_avatar_settings() {
		$preferences = new Avatar\Preferences( $this->auto_options );
		$avatar = $preferences->get_options();

		?>
		<fieldset>
			<label for="gravatar_force_default">
				<input type="checkbox" id="gravatar_force_default" name="gravatar_force_default" <?php checked( $avatar->force_default_avatar ); ?> />

				<?php esc_html_e( 'Force default avatar to be used instead of allowing custom user avatars', 'gravatar-enhanced' ); ?>
			</label>

			<br>

			<label for="gravatar_force_alt">
				<input type="checkbox" id="gravatar_force_alt" name="gravatar_force_alt" <?php checked( $avatar->force_alt ); ?> />

				<?php esc_html_e( 'Use the user name as an image alt tag', 'gravatar-enhanced' ); ?>
			</label>

			<p class="description">
				<?php _e( 'You can find details about these options on the <a href="https://support.gravatar.com/gravatar-enhanced-wordpress-plugin/">support page</a>.', 'gravatar-enhanced' ); ?>
			</p>

			<?php wp_nonce_field( 'gravatar-options', '_gravatar_options_nonce', false ); ?>
		</fieldset>
		<?php
	}

	/**
	 * @return void
	 */
	public function display_email_settings() {
		$preferences = new Email\Preferences( $this->lazy_options );
		$email = $preferences->get_options();

		?>
		<fieldset>
			<label for="gravatar_email">
				<input type="checkbox" id="gravatar_email" name="gravatar_email" <?php checked( $email->enabled ); ?> />

				<?php esc_html_e( 'Send a nice email to commenters without a Gravatar, inviting them to sign up for one!', 'gravatar-enhanced' ); ?>
			</label>
			<p>
				<label for="gravatar_email_message">
					<?php esc_html_e( 'Customize the invitation message:', 'gravatar-enhanced' ); ?>
				</label>
			</p>
			<p>
				<textarea
					id="gravatar_email_message"
					name="gravatar_email_message"
					rows="10"
					cols="50"
					class="large-text"><?php echo esc_textarea( $email->message ); ?></textarea>
				<br />
				<label for="gravatar_email_message">
					<span class="description">
						<?php _e( 'Use these placeholders: <code>COMMENTER_NAME</code>, <code>COMMENTER_EMAIL</code>, <code>COMMENTER_URL</code>, <code>SITE_URL</code>, <code>POST_NAME</code>, and <code>GRAVATAR_URL</code>.', 'gravatar-enhanced' ); ?>
					</span>
				</label>
			</p>
		</fieldset>
		<?php
	}

	/**
	 * @return void
	 */
	public function display_proxy_settings() {
		$preferences = new Proxy\Preferences( $this->auto_options );
		$proxy = $preferences->get_options();

		$proxy_cache = new Proxy\ProxyCache();
		$entries = $proxy_cache->get_entries();
		$can_cache = $proxy_cache->can_cache();

		/* translators: %d: number of entries */
		$label = _n( 'The proxy cache contains %d entry.', 'The proxy cache contains %d entries.', count( $entries ), 'gravatar-enhanced' );
		?>
		<fieldset>
			<p class="description"><?php esc_html_e( 'Proxy avatars locally. This may be needed for sites with privacy concerns, although it could affect performance and disables hovercards.', 'gravatar-enhanced' ); ?></p>
			<p>
				<select name="gravatar_proxy">
					<option value="<?php echo esc_attr( Proxy\Options::TYPE_DISABLED ); ?>" <?php selected( Proxy\Options::TYPE_DISABLED, $proxy->type ); ?>>
						<?php esc_html_e( 'Do not proxy avatars', 'gravatar-enhanced' ); ?>
					</option>
					<option value="<?php echo esc_attr( Proxy\Options::TYPE_LOCAL ); ?>" <?php selected( Proxy\Options::TYPE_LOCAL, $proxy->type ); ?>>
						<?php esc_html_e( 'Proxy and mirror Gravatar URLs', 'gravatar-enhanced' ); ?>
					</option>
					<option value="<?php echo esc_attr( Proxy\Options::TYPE_PRIVATE ); ?>"<?php selected( Proxy\Options::TYPE_PRIVATE, $proxy->type ); ?>>
						<?php esc_html_e( 'Proxy with unique URLs', 'gravatar-enhanced' ); ?>
					</option>
				</select>
			</p>
			<?php if ( $proxy->type !== Proxy\Options::TYPE_DISABLED ) : ?>
				<p>
					<?php echo esc_html( sprintf( $label, count( $entries ) ) ); ?>
					<input type="submit" class="button button-secondary" value="<?php esc_attr_e( 'Flush cache', 'gravatar-enhanced' ); ?>" name="flush_cache" />
					<?php wp_nonce_field( 'flush_cache', 'flush_cache_nonce' ); ?>
				</p>
			<?php endif; ?>

			<?php if ( $proxy->type !== Proxy\Options::TYPE_DISABLED && ! $can_cache ) : ?>
				<div class="notice notice-error settings-error is-dismissible">
					<p><?php esc_html_e( 'Avatar proxy failed to create cache directory. Please check your server configuration.', 'gravatar-enhanced' ); ?></p>
				</div>
			<?php endif; ?>
		</fieldset>
		<?php
	}

	/**
	 * @return void
	 */
	public function display_analytics_settings() {
		$preferences = new Analytics\Preferences( $this->auto_options );
		$analytics = $preferences->get_options();
		?>
		<fieldset>
			<label for="gravatar_analytics">
				<input type="checkbox" id="gravatar_analytics" name="gravatar_analytics" <?php checked( $analytics->enabled ); ?> />

				<?php esc_html_e( 'Help us make Gravatar better by allowing us to collect anonymous usage tracking of the features used.', 'gravatar-enhanced' ); ?>

				<?php _e( 'You can find details about this on the <a href="https://support.gravatar.com/gravatar-enhanced-wordpress-plugin/">support page</a>.', 'gravatar-enhanced' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * @return void
	 */
	public function display_comment_settings() {
		$preferences = new Comments\Preferences( $this->auto_options );
		$comments = $preferences->get_options();

		$disabled = false;
		if ( class_exists( '\Jetpack' ) && \Jetpack::is_module_active( 'comments' ) ) {
			$disabled = true;
		}

		// It doesn't work on this theme
		if ( get_template() === 'twentyeleven' ) {
			$disabled = true;
		}

		?>
		<fieldset>
			<label for="gravatar_comments">
				<input
					type="checkbox"
					id="gravatar_comments"
					name="gravatar_comments"
					<?php checked( $disabled ? false : $comments->enabled ); ?>
					<?php echo $disabled ? 'disabled' : ''; ?>
				/>

				<?php if ( $disabled ) : ?>
					<?php esc_html_e( 'Show Gravatar in the comment form (disabled due to Jetpack comments being used, or an incompatible theme).', 'gravatar-enhanced' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Show Gravatar in the comment form.', 'gravatar-enhanced' ); ?>
				<?php endif; ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * @return void
	 */
	public function save_settings() {
		if ( ! isset( $_POST['_gravatar_options_nonce'] ) || ! wp_verify_nonce( $_POST['_gravatar_options_nonce'], 'gravatar-options' ) ) {
			return;
		}

		// Handle auto options.
		if ( in_array( 'avatar', $this->enabled_modules, true ) ) {
			$avatar_preferences = $this->get_avatar_preferences();
			$this->auto_options->update( $avatar_preferences );
		}

		if ( in_array( 'proxy', $this->enabled_modules, true ) ) {
			$proxy_preferences = $this->get_proxy_preferences();
			$this->auto_options->update( $proxy_preferences );
		}

		if ( in_array( 'analytics', $this->enabled_modules, true ) ) {
			$analytics_preferences = $this->get_analytics_preferences();
			$this->auto_options->update( $analytics_preferences );
		}

		if ( in_array( 'comments', $this->enabled_modules, true ) ) {
			$comment_preferences = $this->get_comment_preferences();
			$this->auto_options->update( $comment_preferences );
		}

		$this->auto_options->save();

		// Handle lazy options.
		if ( in_array( 'email', $this->enabled_modules, true ) ) {
			$email_preferences = $this->get_email_preferences();
			$this->lazy_options->update( $email_preferences );
		}

		$this->lazy_options->save();
	}

	/**
	 * @return Comments\Preferences
	 */
	private function get_comment_preferences() {
		$options = [
			'enabled' => isset( $_POST['gravatar_comments'] ),
		];

		$new_options = Comments\Options::from_array( $options );

		return new Comments\Preferences( $this->auto_options, $new_options );
	}

	/**
	 * @return Avatar\Preferences
	 */
	private function get_avatar_preferences() {
		$options = [
			'force_default_avatar' => isset( $_POST['gravatar_force_default'] ),
			'force_alt' => isset( $_POST['gravatar_force_alt'] ),
		];

		$new_options = Avatar\Options::from_array( $options );

		return new Avatar\Preferences( $this->auto_options, $new_options );
	}

	/**
	 * @return Proxy\Preferences
	 */
	private function get_proxy_preferences() {
		// Sanitize proxy type
		$proxy_type = Proxy\Options::TYPE_DISABLED;
		if ( isset( $_POST['gravatar_proxy'] ) && in_array( $_POST['gravatar_proxy'], [ Proxy\Options::TYPE_LOCAL, Proxy\Options::TYPE_PRIVATE ], true ) ) {
			/** @var ProxyOptionsType */
			$proxy_type = sanitize_text_field( $_POST['gravatar_proxy'] );
		}

		$options = [
			'type' => $proxy_type,
		];

		$new_options = Proxy\Options::from_array( $options );

		return new Proxy\Preferences( $this->auto_options, $new_options );
	}

	/**
	 * @return Email\Preferences
	 */
	private function get_email_preferences() {
		// Sanitize email options
		$options = [
			'enabled' => isset( $_POST['gravatar_email'] ),
			'message' => trim( sanitize_textarea_field( wp_unslash( isset( $_POST['gravatar_email_message'] ) ? $_POST['gravatar_email_message'] : '' ) ) ),
		];
		$options['message'] = str_replace( "\r\n", "\n", $options['message'] );

		$new_options = Email\Options::from_array( $options );

		return new Email\Preferences( $this->lazy_options, $new_options );
	}

	/**
	 * @return Analytics\Preferences
	 */
	private function get_analytics_preferences() {
		// Sanitize analytic options
		$options = [
			'enabled' => isset( $_POST['gravatar_analytics'] ),
		];
		$new_options = Analytics\Options::from_array( $options );

		return new Analytics\Preferences( $this->auto_options, $new_options );
	}
}
