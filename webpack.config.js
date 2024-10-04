const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const config = {
	...defaultConfig,

	entry: {
		'quick-editor': './src/quick-editor',
		block: './src/block',
		'block-view': './src/block/view.js',
		'new-block': './src/new-block',
		'new-block-view': './src/new-block/view.ts',
		'block-column': './src/new-block/blocks/column',
		'block-image': './src/new-block/blocks/image',
		'block-name': './src/new-block/blocks/name',
		'block-paragraph': './src/new-block/blocks/paragraph',
		'block-link': './src/new-block/blocks/link',
		'style-block-shared': './src/new-block/shared.scss',
		'style-block-edit': './src/new-block/edit.scss',
		'style-block-view': './src/new-block/view.scss',
		hovercards: './src/hovercards',
		discussion: './src/discussion',
		'wc-my-account': './src/woocommerce/my-account.ts',
	},
};

module.exports = config;
