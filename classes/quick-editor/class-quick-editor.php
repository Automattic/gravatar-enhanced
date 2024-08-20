<?php

namespace Automattic\Gravatar\GravatarEnhanced\QuickEditor;

use Automattic\Gravatar\GravatarEnhanced\Settings;
use WP_User;

class QuickEditor {
	use Settings\SettingsCheckbox;

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Remove the options
	 *
	 * @return void
	 */
	public function uninstall() {
	}

	/**
	 * Main admin_init function used to hook into and register stuff and init plugin settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		add_action( 'admin_head-profile.php', [ $this, 'start_capture_page' ] );
		add_action( 'admin_footer-profile.php', [ $this, 'end_capture_page' ] );
		add_action( 'admin_head-user-edit.php', [ $this, 'start_capture_page' ] );
		add_action( 'admin_footer-user-edit.php', [ $this, 'end_capture_page' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'add_quick_editor' ] );
		add_filter( 'user_profile_picture_description', [ $this, 'add_quick_editor_link' ] );
		add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ], 15 );
	}

	/**
	 * @return void
	 */
	public function start_capture_page() {
		ob_start();
	}

	/**
	 * Do all of the bad stuff
	 *
	 * @return void
	 */
	public function end_capture_page() {
		$profile_page = ob_get_contents();
		ob_end_clean();

		if ( $profile_page === false ) {
			return;
		}

		// Move user information to top
		preg_match( '@<h2>(.*?)</table>@s', $profile_page, $profile_details );
		preg_match( '@<tr class="user-description-wrap.*?</tr>@s', $profile_page, $user_description );

		// Remove the personal options
		$profile_page = (string) preg_replace( '@<h2>.*?</table>@s', '', $profile_page, 1 );

		// Remove the bio
		$profile_page = (string) preg_replace( '@<tr class="user-description-wrap.*?</tr>@s', '', $profile_page, 1 );

		// Add personal options before application password
		$profile_page = (string) preg_replace( '@<div class="application-passwords@', $profile_details[0] . '<div class="application-password', $profile_page, 1 );

		// Add bio back
		$signup = esc_html( __( 'When linked with your Gravatar account, you will be able to pull this information from your profile or sync the two accounts later.', 'gravatar-enhanced' ) );
		$after_bio = '<p class="description gravatar-signup-bio gravatar-profile__hidden">' . $signup . '</p>';
		$before_bio = '';

		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			$sync_text = esc_html__( 'Share the same story everywhere', 'gravatar-enhanced' );
			$sync_description = esc_html__( 'Would you like to import your description from your Gravatar profile?', 'gravatar-enhanced' );
			$sync_button = esc_html__( 'Sync from Gravatar', 'gravatar-enhanced' );

			$before_bio = <<<HTML
<div id="gravatar-profile-sync__description" class="gravatar-profile__hidden">
	<h4>$sync_text</h4>
	<p>$sync_description</p>
	<p><button type="button" class="button button-secondary">$sync_button</button></p>
</div>
HTML;
		}

		$bio = str_replace( '<textarea', $before_bio . '<textarea', $user_description[0] );
		$bio = str_replace( '</p></td>', '</p>' . $after_bio . '</td>', $bio );
		$replacement = 'user-profile-picture$1</tr>' . $bio;

		$profile_page = (string) preg_replace( '@user-profile-picture(.*?)</tr>@s', $replacement, $profile_page, 1 );

		echo $profile_page;
	}

	/**
	 * Add a cache busting parameter to the Gravatar URL on the profile page. This ensures it is always up to date.
	 *
	 * @param string $url
	 * @return string
	 */
	public function get_avatar_url( $url ) {
		return add_query_arg( 't', time(), $url );
	}

	/**
	 * Replace the text link with a button
	 *
	 * @return string
	 */
	public function add_quick_editor_link() {
		$current_user = $this->get_user();

		if ( ! $current_user ) {
			return '';
		}

		$profile_details = esc_html( $current_user->user_email );
		$what_is_gravatar = esc_html__( 'What is Gravatar?', 'gravatar-enhanced' );
		$mail_icon = plugins_url( 'resources/mail.svg', GRAVATAR_ENHANCED_PLUGIN_FILE );
		$gravatar_icon = plugins_url( 'resources/gravatar.svg', GRAVATAR_ENHANCED_PLUGIN_FILE );
		$avatar_url = get_avatar_url( $current_user->ID );

		$html = <<<HTML
</p>
<div class="gravatar-account">
	<div>
		<img src="$mail_icon" alt="" />
		$profile_details
	</div>
	<div>
		<a target="_blank" href="https://gravatar.com">$what_is_gravatar</a>

		<img src="$gravatar_icon" alt="" />
	</div>
</div>

<div class="gravatar-hovercard-container">
	<div class="gravatar-hovercard gravatar-profile__loading">
		<div class="gravatar-hovercard">
			<div class="gravatar-hovercard__inner">
				<div class="gravatar-hovercard__header">
					<img class="gravatar-hovercard__avatar" src="$avatar_url" width="72" height="72" />
					<h4 class="gravatar-hovercard__name"></h4>
				</div>
				<div class="gravatar-hovercard__body">
					<p class="gravatar-hovercard__description"></p>
				</div>
				<div class="gravatar-hovercard__footer">
					<div class="gravatar-hovercard__social-links">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

HTML;

		return $html;
	}

	/**
	 * Initialise editor, if enabled.
	 *
	 * @return void
	 */
	public function add_quick_editor() {
		$current_user = $this->get_user();

		if ( ! $current_user ) {
			return;
		}

		$current_user_email = strtolower( $current_user->user_email );
		$current_user_locale = get_user_locale( $current_user );

		// Gravatar only wants the first part of a locale, so we strip the country code.
		$current_user_locale = (string) preg_replace( '/_.*$/', '', $current_user_locale );

		$settings = [
			'email' => $current_user_email,
			'locale' => $current_user_locale === 'en' ? '' : $current_user_locale,
			'hash' => hash( 'sha256', $current_user_email ),
			'avatar' => get_avatar_url( $current_user_email ),
			'canEdit' => defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ? true : false,
			'text' => [
				'createButton' => __( 'Claim your free profile', 'gravatar-enhanced' ),
				'updateButton' => __( 'Edit profile', 'gravatar-enhanced' ),
				'viewButton' => __( 'View profile', 'gravatar-enhanced' ),
				'errorTitle' => __( 'Failed to load profile', 'gravatar-enhanced' ),
				'errorDescription' => __( 'There was an error loading this profile. Please try again later.', 'gravatar-enhanced' ),
				'unknownTitle' => __( 'Your name', 'gravatar-enhanced' ),
				'unknownDescription' => __( 'This site uses Gravatar for managing avatars and profiles. To update your avatar, simply claim your profile today.', 'gravatar-enhanced' ),
				'otherUnknownTitle' => __( 'Profile name', 'gravatar-enhanced' ),
				'otherUnknownDescription' => __( 'This site uses Gravatar for managing avatars and profiles. The user will need to claim their profile to add details here.', 'gravatar-enhanced' ),
			],
		];

		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/quick-editor.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_script( 'gravatar-enhanced-qe', plugins_url( 'build/quick-editor.js', GRAVATAR_ENHANCED_PLUGIN_FILE ), $assets['dependencies'], $assets['version'], true );
		wp_localize_script( 'gravatar-enhanced-qe', 'geQuickEditor', $settings );

		wp_register_style( 'gravatar-enhanced-qe', plugins_url( 'build/style-quick-editor.css', GRAVATAR_ENHANCED_PLUGIN_FILE ), [], $assets['version'] );
		wp_enqueue_style( 'gravatar-enhanced-qe' );
	}

	/**
	 * @return false|WP_User
	 */
	private function get_user() {
		$user_id = ! empty( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : wp_get_current_user()->ID;

		return get_userdata( $user_id );
	}
}
