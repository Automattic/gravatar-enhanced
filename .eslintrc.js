module.exports = {
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	parserOptions: {
		requireConfigFile: false,
	},
	env: {
		browser: true,
	},
	plugins: [
		// other plugins...
		'prettier', // Enables eslint-plugin-prettier
	],
	rules: {
		'prettier/prettier': [ 'error', require( './.prettierrc.js' ) ], // Uses our .prettierrc.js config
		'no-unused-expressions': 'warn',
	},
	settings: {
		'import/resolver': {
			node: {
				extensions: [ '.js', '.jsx', '.ts', '.tsx' ],
			},
		},
	},
};
