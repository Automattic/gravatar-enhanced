const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const config = {
	...defaultConfig,

	entry: {
		'quick-editor': './src/quick-editor/index.ts',
		block: './src/block/index.js',
		'block-view': './src/block/view.js',
		hovercards: './src/hovercards/index.ts',
		discussion: './src/discussion/index.ts',
		'wc-account-details': './src/woocommerce/account-details.ts',
		'wc-admin-customers': './src/woocommerce/admin-customers.ts',
	},
};

module.exports = config;
