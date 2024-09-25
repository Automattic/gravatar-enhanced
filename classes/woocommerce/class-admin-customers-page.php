<?php

namespace Automattic\Gravatar\GravatarEnhanced\Woocommerce;

class AdminCustomersPage {
	public function init() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_scripts( $hook ) {
		// Check if we are on the WooCommerce Customers admin page
		if ( 'woocommerce_page_wc-admin' !== $hook ) {
			return;
		}

		if ( isset( $_GET['page'], $_GET['path'] ) && 'wc-admin' === $_GET['page'] && '/customers' === $_GET['path'] ) {
			// Get the asset file for dependencies and version
			$asset_file = dirname( GRAVATAR_ENHANCED_PLUGIN_FILE ) . '/build/customers.asset.php';
			$assets     = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => time() ];

			echo 'som tu';

			// Enqueue the JavaScript file
			wp_enqueue_script(
				'gravatar-enhanced-wc-admin-customers',
				plugins_url( 'build/wc-admin-customers.js', GRAVATAR_ENHANCED_PLUGIN_FILE ),
				$assets['dependencies'],
				$assets['version'],
				true
			);

			$settings = [
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'gravatar_enhanced_wc_admin_customers' ),
			];

			wp_localize_script( 'gravatar-enhanced-wc-admin-customers', 'geWcAdminCustomers', $settings );

			wp_enqueue_style(
				'gravatar-enhanced-wc-admin-customers',
				plugins_url( 'build/wc-admin-customers.css', GRAVATAR_ENHANCED_PLUGIN_FILE ),
				[],
				$assets['version']
			);
		}
	}
}
