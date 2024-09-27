<?php

namespace Automattic\Gravatar\GravatarEnhanced\Woocommerce;

class AdminCustomersPage {
	/**
	 * @return void
	 */
	public function init() {
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
				'version'      => time()
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
}
