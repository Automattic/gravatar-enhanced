const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const config = {
	...defaultConfig,

	entry: {
		'quick-editor': './src/quick-editor',
		block: './src/block',
		'block-view': './src/block/view.js',
		'new-block': './src/new-block/main',
		'block-column': './src/new-block/column',
		'block-image': './src/new-block/image',
		'block-name': './src/new-block/name',
		'block-paragraph': './src/new-block/paragraph',
		'block-link': './src/new-block/link',
		hovercards: './src/hovercards',
		discussion: './src/discussion',
	},
};

module.exports = config;
