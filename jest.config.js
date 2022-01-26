module.exports = {
	preset: '@wordpress/jest-preset-default',
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
