module.exports = {
	preset: '@wordpress/jest-preset-default',

	// This can be be removed with a future version of @wordpress/jest-preset-default,
	// at the time of adding this (Aug 2027) it hadn't made it to a released version of
	// the preset yet:
	// (see https://github.com/WordPress/gutenberg/pull/43271)
	transformIgnorePatterns: [ 'node_modules/(?!(is-plain-obj))' ],

	roots: [
		'<rootDir>/ui/js/dfv/src/',
		'<rootDir>/ui/js/blocks/src/',
	],

	setupFilesAfterEnv: [
		require.resolve(
			'@wordpress/jest-preset-default/scripts/setup-test-framework.js'
		),
		'<rootDir>/jest-setup-wordpress-globals.js',
	],

	testMatch: [
		'**/test/*.js',
	],
};
