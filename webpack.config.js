const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const config = {
	...defaultConfig,

	entry: {
		'quick-editor': './src/quick-editor',
		block: './src/block',
		'block-view': './src/block/view.ts',
		'block-column': './src/block/editor-blocks/column',
		'block-image': './src/block/editor-blocks/image',
		'block-name': './src/block/editor-blocks/name',
		'block-paragraph': './src/block/editor-blocks/paragraph',
		'block-link': './src/block/editor-blocks/link',
		hovercards: './src/hovercards',
		discussion: './src/discussion',
		'wc-my-account': './src/woocommerce/my-account.ts',
		'wc-admin-customers': './src/woocommerce/admin-customers.ts',
	},
};

module.exports = config;
