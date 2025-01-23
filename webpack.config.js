const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const config = {
	...defaultConfig,

	entry: {
		'quick-editor': './src/quick-editor',
		block: './src/block',
		'block-view': './src/block/view.ts',
		'block-column': './src/block/child-blocks/column',
		'block-image': './src/block/child-blocks/image',
		'block-name': './src/block/child-blocks/name',
		'block-paragraph': './src/block/child-blocks/paragraph',
		'block-link': './src/block/child-blocks/link',
		hovercards: './src/hovercards',
		discussion: './src/discussion',
		'wc-my-account': './src/woocommerce/my-account.ts',
		'wc-admin-customers': './src/woocommerce/admin-customers.ts',
	},
};

module.exports = config;
