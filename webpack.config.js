const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const config = {
	...defaultConfig,

	entry: {
		'quick-editor': './src/quick-editor/index.ts',
		hovercards: './src/hovercards/index.ts',
		discussion: './src/discussion/index.ts',
	},
};

module.exports = config;
