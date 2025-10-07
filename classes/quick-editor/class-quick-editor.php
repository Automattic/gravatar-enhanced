<?php

namespace Automattic\Gravatar\GravatarEnhanced\QuickEditor;

use Automattic\Gravatar\GravatarEnhanced\Module;
use WP_User;

class QuickEditor implements Module {
	private const SECTION_ABOUT = 'about';
	private const SECTION_NAME = 'name';
	private const SECTION_CONTACT = 'contact';
	private const SECTION_BEFORE = 'before';
	private const SECTION_AFTER = 'after';

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
		add_action( 'admin_head-profile.php', [ $this, 'start_capture_page' ] );
		add_action( 'admin_footer-profile.php', [ $this, 'end_capture_page' ] );
		add_action( 'admin_head-user-edit.php', [ $this, 'start_capture_page' ] );
		add_action( 'admin_footer-user-edit.php', [ $this, 'end_capture_page' ] );

		add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ], 15 );
		add_filter( 'user_profile_picture_description', [ $this, 'user_profile_picture_description' ] );
	}

	/**
	 * @param string $description
	 * @return string
	 */
	public function user_profile_picture_description( $description ) {
		return sprintf(
			/* translators: URL */
			__( 'Gravatar powers your profile image - a globally recognized avatar. <a href="%s">Edit your Gravatar</a>.', 'gravatar-enhanced' ),
			'https://gravatar.com/profile/avatars'
		);
	}

	/**
	 * @return void
	 */
	public function start_capture_page() {
		$this->add_quick_editor();

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

		$sections = $this->grab_sections( $profile_page );
		if ( $sections === false ) {
			// Leave page alone
			echo $profile_page;
			return;
		}

		$sections[ self::SECTION_NAME ] = $this->modify_name_section( $sections[ self::SECTION_NAME ] );

		$new_order = [
			'user-profile-picture',
			'user-verified-services',
			'user-user-login-wrap',
			'user-first-name-wrap',
			'user-last-name-wrap',
			'user-nickname-wrap',
			'user-display-name-wrap',
			'user-description-wrap',
			'user-gravatar-card',
		];

		$about_yourself = $this->merge_about_and_name( $sections[ self::SECTION_ABOUT ], $sections[ self::SECTION_NAME ] );
		$about_yourself = $this->insert_row( $about_yourself, $this->get_verified_services_section() );
		$about_yourself = $this->insert_row( $about_yourself, $this->get_gravatar_card_section() );
		$about_yourself = $this->reorder_section( $about_yourself, $new_order );

		// Now form the page back
		echo implode(
			'',
			[
				$sections[ self::SECTION_BEFORE ],
				$about_yourself,
				$sections[ self::SECTION_CONTACT ],
				$sections[ self::SECTION_AFTER ],
			]
		);
	}

	/**
	 * @param string $text
	 * @param string $row
	 * @return string
	 */
	private function insert_row( $text, $row ) {
		return (string) preg_replace( '@</table>$@s', $row . '</table>', $text, 1 );
	}

	/**
	 * @return string
	 */
	private function get_verified_services_section() {
		$verified_services = esc_html( __( 'Verified Services', 'gravatar-enhanced' ) );
		$description = sprintf(
			/* translators: URL */
			__( 'Let people know where they can find you online. <a href="%s">Edit your verified accounts.</a>', 'gravatar-enhanced' ),
			'https://gravatar.com/profile/verified-accounts'
		);

		return <<<HTML
<tr class="user-verified-services">
	<th>$verified_services</th>
	<td>
		<div id="gravatar-verified-services" class="gravatar-profile__loading"></div>
		<p class="description">$description</p>
	</td>
</tr>
HTML;
	}

	/**
	 * @return string
	 */
	private function get_gravatar_card_section() {
		$current_user = $this->get_user();
		if ( ! $current_user ) {
			return '';
		}

		$title = esc_html( __( 'Gravatar Card', 'gravatar-enhanced' ) );
		$description = __( 'Your Gravatar card is shown in comments and profile blocks. Gravatar profiles travel with you across millions of sites, showing your photo, bio, and links - your digital business card.', 'gravatar-enhanced' );
		$edit = esc_html( __( 'Edit your Gravatar card', 'gravatar-enhanced' ) );
		$avatar_url = get_avatar_url( $current_user->ID );
		$alt = esc_attr( $current_user->display_name );

		return <<<HTML
<tr class="user-gravatar-card">
	<th>$title</th>
	<td>
		<div class="gravatar-hovercard-container">
			<div class="gravatar-hovercard gravatar-profile__loading">
				<div class="gravatar-hovercard">
					<div class="gravatar-hovercard__inner">
						<div class="gravatar-hovercard__header">
							<img class="gravatar-hovercard__avatar" src="$avatar_url" width="104" height="104" alt="$alt" />
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
		<p class="description">$description</p>
		<p><button type="button" class="button button-secondary">$edit</button></p>
	</td>
</tr>
HTML;
	}

	/**
	 * Reorder table rows
	 * @param string $section
	 * @param string[] $order
	 * @return string
	 */
	private function reorder_section( $section, $order ) {
		// Split into rows
		if ( preg_match_all( '@<tr class="(.*?)">(.*?)</tr>@s', $section, $matches ) > 0 ) {
			$new_section = '';

			foreach ( $order as $class_name ) {
				foreach ( $matches[1] as $pos => $section_class ) {
					if ( $class_name === $section_class ) {
						$content = $matches[2][ $pos ];

						$new_section .= "<tr class=\"$section_class\">$content</tr>";
						break;
					}
				}
			}

			return (string) preg_replace( '@<tr.*</tr>@s', $new_section, $section );
		}

		return $section;
	}

	/**
	 * @param string $about
	 * @param string $name
	 * @return string
	 */
	private function merge_about_and_name( $about, $name ) {
		return (string) preg_replace( '@</table>\s*<table.*?>@s', '', $about . $name );
	}

	/**
	 * @param string $name_section
	 * @return string
	 */
	private function modify_name_section( $name_section ) {
		return str_replace( '<h2>' . __( 'Name' ) . '</h2>', '', $name_section );
	}

	/**
	 * @param string $profile_page
	 * @return array{'about': string, 'name': string, 'contact': string, 'before': string, 'after': string}|false
	 */
	private function grab_sections( $profile_page ) {
		$about_yourself_title = preg_quote( __( 'About Yourself' ), '@' );
		$name_title = preg_quote( __( 'Name' ), '@' );
		$contact_info_title = preg_quote( __( 'Contact Info' ), '@' );

		$content = [
			self::SECTION_ABOUT => '',
			self::SECTION_NAME => '',
			self::SECTION_CONTACT => '',
			self::SECTION_BEFORE => '',
			self::SECTION_AFTER => '',
		];
		$sections = [
			self::SECTION_ABOUT => "@<h2>$about_yourself_title</h2>(.*?)</table>@s",
			self::SECTION_NAME => "@<h2>$name_title</h2>(.*?)</table>@s",
			self::SECTION_CONTACT => "@<h2>$contact_info_title</h2>(.*?)</table>@s",
			self::SECTION_BEFORE => '@^(.*?<form.*?(?=<h2>))@s',
		];

		// Grab section content into $content
		foreach ( $sections as $title => $regex ) {
			if ( preg_match( $regex, $profile_page, $matches ) === 0 || ! isset( $matches[0] ) ) {
				return false;
			}

			$content[ $title ] = $matches[0];
		}

		// Now remove the sections
		foreach ( $sections as $title => $regex ) {
			$profile_page = (string) preg_replace( $regex, '', $profile_page, 1 );
		}

		// And add the rest of the content
		$content[ self::SECTION_AFTER ] = $profile_page;

		return $content;
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
		$current_user_locale = $this->get_gravatar_locale( get_user_locale( $current_user ) );

		$settings = [
			'email' => $current_user_email,
			'locale' => $current_user_locale,
			'hash' => hash( 'sha256', $current_user_email ),
			'avatar' => get_avatar_url( $current_user_email ),
			'canEdit' => defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ? true : false,
			'text' => [
				'createButton' => __( 'Claim your free profile', 'gravatar-enhanced' ),
				'updateButton' => __( 'Edit profile', 'gravatar-enhanced' ),
				'viewButton' => __( 'View profile', 'gravatar-enhanced' ),
				'errorTitle' => __( 'Profile failed', 'gravatar-enhanced' ),
				'errorDescription' => __( 'There was an error loading this profile. Please try again later.', 'gravatar-enhanced' ),
				'unknownTitle' => __( 'Your name', 'gravatar-enhanced' ),
				'unknownDescription' => __( 'This site uses Gravatar for managing avatars and profiles.', 'gravatar-enhanced' ),
				'otherUnknownTitle' => __( 'Profile name', 'gravatar-enhanced' ),
				'otherUnknownDescription' => __( 'This site uses Gravatar for managing avatars and profiles.', 'gravatar-enhanced' ),
			],
		];

		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/quick-editor.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_script( 'gravatar-enhanced-qe', plugins_url( 'build/quick-editor.js', GRAVATAR_ENHANCED_PLUGIN_FILE ), $assets['dependencies'], $assets['version'], true );
		wp_localize_script( 'gravatar-enhanced-qe', 'geQuickEditor', $settings );

		wp_register_style( 'gravatar-enhanced-qe', plugins_url( 'build/style-quick-editor.css', GRAVATAR_ENHANCED_PLUGIN_FILE ), [], $assets['version'] );
		wp_enqueue_style( 'gravatar-enhanced-qe' );

		// We always want hovercards loaded on the profile page
		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/hovercards.asset.php';
		$assets = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		wp_enqueue_script( 'gravatar-enhanced-hovercards', plugins_url( 'build/hovercards.js', GRAVATAR_ENHANCED_PLUGIN_FILE ), $assets['dependencies'], $assets['version'], true );
		wp_register_style( 'gravatar-enhanced-hovercards', plugins_url( 'build/style-hovercards.css', GRAVATAR_ENHANCED_PLUGIN_FILE ), [], $assets['version'] );
		wp_enqueue_style( 'gravatar-enhanced-hovercards' );
	}

	/**
	 * Get the locale for the user.
	 *
	 * @param string $locale
	 * @return string
	 */
	private function get_gravatar_locale( $locale ) {
		$current_user_locale = strtolower( $locale );

		// Gravatar only wants the first part of a locale, so we strip the country code unless it's one of the exceptions
		$exceptions = [
			'zh_tw',
			'fr_ca',
		];

		if ( in_array( $current_user_locale, $exceptions, true ) ) {
			return str_replace( '_', '-', $current_user_locale );
		}

		$current_user_locale = (string) preg_replace( '/[_-].*$/', '', $current_user_locale );
		$current_user_locale = str_replace( 'zh', 'cn', $current_user_locale );
		$current_user_locale = str_replace( 'en', '', $current_user_locale );

		return $current_user_locale;
	}

	/**
	 * @return false|WP_User
	 */
	private function get_user() {
		$user_id = ! empty( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : wp_get_current_user()->ID;

		return get_userdata( $user_id );
	}

	/**
	 * @return void
	 */
	public function uninstall() {}
}
