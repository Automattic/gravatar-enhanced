const globals = require( 'globals' );
const wordpress = require( '@wordpress/eslint-plugin' );

module.exports = [
	{
		ignores: [ 'build/**', 'release/**', 'dist/**', 'vendor/**' ],
	},
	...wordpress.configs.recommended,
	{
		languageOptions: {
			globals: {
				...globals.browser,
			},
		},
		rules: {
			'no-unused-expressions': 'warn',
		},
	},
];
