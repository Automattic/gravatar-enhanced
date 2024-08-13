<?php

namespace Automattic\Gravatar\GravatarEnhanced\QuickEditor;

use Automattic\Gravatar\GravatarEnhanced\Settings;

class QuickEditor {
	use Settings\SettingsCheckbox;

	const QUICK_EDITOR_VERSION = '0.6.0';

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
		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ], 15 );
			add_action( 'admin_enqueue_scripts', [ $this, 'maybe_add_quick_editor' ] );
			add_filter( 'user_profile_picture_description', [ $this, 'add_quick_editor_link' ] );
		}
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
		return '<button type="button" class="button button-secondary" id="edit-gravatar">' . esc_html__( 'Change Gravatar', 'gravatar-enhanced' ) . '</button>';
	}

	/**
	 * Initialise editor, if enabled.
	 *
	 * @return void
	 */
	public function maybe_add_quick_editor() {
		$current_user = wp_get_current_user();
		$current_user_email = esc_js( $current_user->user_email );
		$current_user_locale = get_user_locale( $current_user );
		$update_delay = 2000;

		// Gravatar only wants the first part of a locale, so we strip the country code.
		$current_user_locale = (string) preg_replace( '/_.*$/', '', $current_user_locale );

		// English is the default
		$current_user_locale = $current_user_locale === 'en' ? '' : $current_user_locale;
		$current_user_locale = esc_js( $current_user_locale );

		$js = <<<JS
document.addEventListener( 'DOMContentLoaded', () => {
	const img = document.querySelector( '.user-profile-picture img.avatar' );
	const editButton = document.getElementById( 'edit-gravatar' );
	if ( ! img || typeof Gravatar === 'undefined' || typeof Gravatar.GravatarQuickEditorCore === 'undefined' ) {
		return;
	}

	const imgSrcset = img.getAttribute( 'srcset' ).replace( /&t=[0-9]+/, '' );
	const quickEditor = new Gravatar.GravatarQuickEditorCore( {
		email: '$current_user_email',
		scope: [ 'avatars' ],
		locale: '$current_user_locale',
		onProfileUpdated: ( type ) => {
			if ( type !== 'avatar_updated' ) {
				return;
			}

			const avatarURL = new URL( img.src );

			avatarURL.searchParams.set( 't', new Date().getTime().toString() );
			img.classList.add( 'avatar-loading' );

			setTimeout( () => {
				img.src = avatarURL.toString();
				img.srcset = imgSrcset.replace( / /, '&t=' + new Date().getTime() + ' ' );
				img.classList.remove( 'avatar-loading' );
			}, $update_delay );
		},
	} );

	editButton.addEventListener( 'click', () => {
		quickEditor.open();
	} );
} );
JS;

		$css = <<<CSS
@keyframes pulse {
	0% { opacity: 1; }
	50% { opacity: 0.2; }
	100% { opacity: 1; }
}

.user-profile-picture img.avatar.avatar-loading {
	animation: pulse 2s infinite ease-in-out;
}
CSS;

		wp_enqueue_script( 'gravatar-enhanced-qe', plugins_url( 'quick-editor.js', GRAVATAR_ENHANCED_PLUGIN_FILE ), [], self::QUICK_EDITOR_VERSION, true );
		wp_add_inline_script( 'gravatar-enhanced-qe', $js );

		// phpcs:ignore
		wp_register_style( 'gravatar-enhanced-qe', false );
		wp_add_inline_style( 'gravatar-enhanced-qe', $css );
		wp_enqueue_style( 'gravatar-enhanced-qe' );
	}
}
