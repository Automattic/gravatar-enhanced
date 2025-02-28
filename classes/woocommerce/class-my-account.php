<?php

namespace Automattic\Gravatar\GravatarEnhanced\Woocommerce;

use Automattic\Gravatar\GravatarEnhanced\Module;

class MyAccount implements Module {
	/**
	 * @var string
	 */
	const FILTER_GRAVATAR_WC_MY_ACCOUNT_MODULE_ENABLED = 'gravatar_enhanced_wc_my_account_module_enabled';

	/**
	 * Check if the module is disabled by a filter.
	 *
	 * @return bool
	 */
	private function is_module_disabled() {
		// Check if module is manually disabled by the filter.
		if ( ! apply_filters( self::FILTER_GRAVATAR_WC_MY_ACCOUNT_MODULE_ENABLED, true ) ) {
			return true; // Disabled by filter.
		}

		return false;
	}

	/**
	 * @return void
	 */
	public function init() {
		// Bail if the module is disabled.
		if ( $this->is_module_disabled() ) {
			return;
		}

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
		$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/wc-my-account.asset.php';
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
			'gravatar-enhanced-wc-my-account',
			plugins_url( 'build/wc-my-account.js', GRAVATAR_ENHANCED_PLUGIN_FILE ),
			$assets['dependencies'],
			$assets['version'],
			true
		);
		wp_localize_script( 'gravatar-enhanced-wc-my-account', 'geWcMyAccount', $settings );

		wp_enqueue_style(
			'gravatar-enhanced-wc-my-account',
			plugins_url( 'build/wc-my-account.css', GRAVATAR_ENHANCED_PLUGIN_FILE ),
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
		$user_email   = $current_user->user_email;
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

	/**
	 * @return void
	 */
	public function uninstall() {}
}
