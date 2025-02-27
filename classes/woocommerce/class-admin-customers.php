<?php

namespace Automattic\Gravatar\GravatarEnhanced\Woocommerce;

use Automattic\Gravatar\GravatarEnhanced\Module;

class AdminCustomers implements Module {
	/**
	 * @var string
	 */
	const FILTER_GRAVATAR_WC_ADMIN_CUSTOMERS_MODULE_ENABLED = 'gravatar_enhanced_wc_admin_customers_module_enabled';

	/**
	 * Check if the module is disabled by a filter.
	 *
	 * @return bool
	 */
	private function is_module_disabled() {
		// Check if module is manually disabled by the filter.
		if ( ! apply_filters( self::FILTER_GRAVATAR_WC_ADMIN_CUSTOMERS_MODULE_ENABLED, true ) ) {
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

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * @param string $hook The current admin page hook.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		// Bail if not on the WooCommerce admin page
		if ( 'woocommerce_page_wc-admin' !== $hook ) {
			return;
		}

		// Check if we are on the WooCommerce Customers admin page
		if ( isset( $_GET['page'], $_GET['path'] ) && 'wc-admin' === $_GET['page'] && '/customers' === $_GET['path'] ) {
			// Get the asset file for dependencies and version
			$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/wc-admin-customers.asset.php';
			$assets     = file_exists( $asset_file ) ? require $asset_file : [
				'dependencies' => [],
				'version'      => time(),
			];

			wp_enqueue_script(
				'gravatar-enhanced-wc-admin-customers',
				plugins_url( 'build/wc-admin-customers.js', GRAVATAR_ENHANCED_PLUGIN_FILE ),
				$assets['dependencies'],
				$assets['version'],
				true
			);

			wp_enqueue_style(
				'gravatar-enhanced-wc-admin-customers',
				plugins_url( 'build/wc-admin-customers.css', GRAVATAR_ENHANCED_PLUGIN_FILE ),
				[],
				$assets['version']
			);
		}
	}

	/**
	 * @return void
	 */
	public function uninstall() {}
}
