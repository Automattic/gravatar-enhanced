<?php

namespace Automattic\Gravatar\GravatarEnhanced\Woocommerce;

class AccountDetailsAvatar {
	private $current_user;
	private $user_email;
	private $display_name;
	private $current_user_locale;

	public function init() {
		add_action( 'woocommerce_before_account_navigation', [ $this, 'start_capture_page' ] );
		add_action( 'woocommerce_after_account_navigation', [ $this, 'end_capture_page' ] );
	}

	private function get_user_data() {
		if ( ! isset( $this->current_user ) ) {
			$this->current_user        = wp_get_current_user();
			$this->user_email          = $this->current_user->user_email;
			$this->display_name        = esc_html( $this->current_user->display_name );
			$this->current_user_locale = get_user_locale( $this->current_user );

			// Gravatar only wants the first part of a locale, so we strip the country code.
			$this->current_user_locale = (string) preg_replace( '/_.*$/', '', $this->current_user_locale );
		}
	}

	public function start_capture_page() {
		$this->enqueue_assets();
		ob_start();
	}

	public function end_capture_page() {
		$content = ob_get_clean();

		if ( false === $content ) {
			return;
		}

		$pattern = '/(<nav\s[^>]*class="[^"]*woocommerce-MyAccount-navigation[^"]*"[^>]*>)/i';

		$content = preg_replace( $pattern, '$1' . $this->display_gravatar(), $content, 1 );

		echo $content;
	}

	private function enqueue_assets() {
		$this->get_user_data();

		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/woocommerce.asset.php';
		$assets     = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

		$settings = [
			'email'  => $this->user_email,
			'locale' => 'en' === $this->current_user_locale ? '' : $this->current_user_locale,
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

	private function display_gravatar() {
		$this->get_user_data();

		$avatar    = get_avatar( $this->user_email, 100, '', 'User Avatar', [ 'class' => 'woocommerce-account-gravatar__avatar' ] );
		$edit_text = esc_html__( 'Change avatar', 'gravatar-enhanced' );

		$html = <<<HTML
        <div class="woocommerce-account-gravatar">
            <div class="woocommerce-account-gravatar__avatar-wrapper">
                $avatar
                <div class="woocommerce-account-gravatar__edit-wrapper">
                    <a class="woocommerce-account-gravatar__edit">$edit_text</a>
                </div>
            </div>
            <span class="woocommerce-account-gravatar__display-name">$this->display_name</span>
        </div>
        HTML;

		return $html;
	}
}
