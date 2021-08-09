import includePaths from 'rollup-plugin-includepaths';
import replace from 'rollup-plugin-replace';
import nodeResolve from 'rollup-plugin-node-resolve';
import commonjs from 'rollup-plugin-commonjs';
import html from 'rollup-plugin-html';
import babel from 'rollup-plugin-babel';
import { uglify } from 'rollup-plugin-uglify';

const includePathOptions = {
	include: {},
	paths: [ 'ui/js' ],
	external: [],
	extensions: [ '.js', '.html' ]
};

export default {
	input: 'ui/js/pods-dfv/_src/pods-dfv.js',
	output: {
		file: 'ui/js/pods-dfv/pods-dfv.min.js',
		format: 'iife',
		name: 'PodsDFV', // One single object added to the global namespace
		globals: {
			'jquery': 'jQuery',
			'underscore': '_',
			'backbone': 'Backbone',
			'backbone.marionette': 'Marionette'
		}
	},
	external: [
		'jquery',
		'underscore',
		'backbone',
		'backbone.marionette'
	],
	plugins: [
		includePaths( includePathOptions ),
		html(),
		replace( {
			// Needed for React, see https://github.com/rollup/rollup/issues/487#issuecomment-177596512
			'process.env.NODE_ENV': JSON.stringify( 'production' )
		} ),
		nodeResolve(),
		commonjs( {
			include: 'node_modules/**'
		} ),
		babel( {
			babelrc: false, // Ignore the .babelrc file which is there for mocha tests
			ignore: [ 'node_modules/**' ],
			presets: [
				[ 'env', { modules: false } ],
				[ 'react' ]
			],
			plugins: [
				'transform-object-rest-spread',
				'external-helpers'
			]
		} ),
		uglify()
	]
};
