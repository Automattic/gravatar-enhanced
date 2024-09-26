<?php

namespace Automattic\Gravatar\GravatarEnhanced\Woocommerce;

class AccountDetailsAvatar {

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'woocommerce_before_account_navigation', [ $this, 'start_capture_page' ] );
		add_action( 'woocommerce_after_account_navigation', [ $this, 'end_capture_page' ] );
	}

	/**
	 * Start capturing the My Account page.
	 *
	 * @return void
	 */
	public function start_capture_page() {
		$this->enqueue_assets();
		ob_start();
	}

	/**
	 * End capturing the My Account page and render the avatar.
	 *
	 * @return void
	 */
	public function end_capture_page() {
		$content = ob_get_clean();

		if ( false === $content ) {
			return;
		}

		$pattern = '/(<nav\s[^>]*class="[^"]*woocommerce-MyAccount-navigation[^"]*"[^>]*>)/i';

		$content = preg_replace( $pattern, '$1' . $this->display_gravatar(), $content, 1 );

		echo $content;
	}

	/**
	 * @return void
	 */
	private function enqueue_assets() {
		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/woocommerce.asset.php';
		$assets     = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		$current_user        = wp_get_current_user();
		$user_email          = $current_user->user_email;
		$current_user_locale = get_user_locale( $current_user );

		// Gravatar only wants the first part of a locale, so we strip the country code.
		$current_user_locale = (string) preg_replace( '/_.*$/', '', $current_user_locale );

		$settings = [
			'email'  => $user_email,
			'locale' => 'en' === $current_user_locale ? '' : $current_user_locale,
		];

		wp_enqueue_script(
			'gravatar-enhanced-woocommerce',
			plugins_url( 'build/woocommerce.js', GRAVATAR_ENHANCED_PLUGIN_FILE ),
			$assets['dependencies'],
			$assets['version'],
			true
		);
		wp_localize_script( 'gravatar-enhanced-woocommerce', 'geWooCommerce', $settings );

		wp_enqueue_style(
			'gravatar-enhanced-woocommerce',
			plugins_url( 'build/style-woocommerce.css', GRAVATAR_ENHANCED_PLUGIN_FILE ),
			[],
			$assets['version']
		);
	}

	/**
	 * Render the avatar.
	 *
	 * @return string
	 */
	private function display_gravatar() {
		$current_user = wp_get_current_user();
		$user_email = $current_user->user_email;
		$avatar       = get_avatar(
			$user_email,
			120,
			'',
			'User Avatar',
			[ 'class' => 'woocommerce-account-gravatar__avatar' ]
		);
		$edit_text    = esc_html__( 'Change avatar', 'gravatar-enhanced' );
		$display_name = esc_html( $current_user->display_name );

		$html = <<<HTML
        
        <div class="woocommerce-account-gravatar">
            <div class="woocommerce-account-gravatar__avatar-wrapper">
                $avatar
                <div class="woocommerce-account-gravatar__edit-wrapper">
                    <a class="woocommerce-account-gravatar__edit">$edit_text</a>
                </div>
            </div>
            <span class="woocommerce-account-gravatar__display-name">$display_name</span>
        </div>
        HTML;

		return $html;
	}
}
