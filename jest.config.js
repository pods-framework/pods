module.exports = {
	preset: '@wordpress/jest-preset-default',

	// Transform the ESM / ESM-only packages that our dependencies (e.g.
	// @wordpress/*, uuid, marked and sanitize-html) pull in, since jest runs
	// them through CommonJS. The "(?:.*/)?" prefix lets these match at any
	// node_modules nesting depth.
	transformIgnorePatterns: [
		'node_modules/(?!(?:.*/)?(@wordpress|is-plain-obj|uuid|htmlparser2|domhandler|domelementtype|domutils|dom-serializer|entities|parse-srcset|is-plain-object|marked|parsel-js|quill|parchment|quill-delta|lodash-es)/)',
	],

	// The preset only transforms .js/.jsx/.ts/.tsx; some @wordpress packages now
	// ship ESM-only .mjs entry points that must also be transformed.
	transform: {
		'\\.[jt]sx?$': require.resolve( 'babel-jest' ),
		'\\.mjs$': require.resolve( 'babel-jest' ),
	},

	roots: [
		'<rootDir>/ui/js/dfv/src/',
		'<rootDir>/ui/js/blocks/src/',
	],

	// The preset already registers its own setup-test-framework.js via
	// setupFilesAfterEnv, so we only add our own WordPress globals here.
	setupFilesAfterEnv: [
		'<rootDir>/jest-setup-wordpress-globals.js',
	],

	testMatch: [
		'**/test/*.js',
	],

	errorOnDeprecated: false,
};
