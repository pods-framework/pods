import babel from 'rollup-plugin-babel';
import uglify from 'rollup-plugin-uglify';

export default {
	entry     : 'ui/js/pods-dfv/_src/pods-dfv.js',
	dest      : 'ui/js/pods-dfv/pods-dfv.min.js',
	format    : 'iife',
	moduleName: 'PodsDFV',
	sourceMap : true,
	plugins   : [
		babel( {
			babelrc: false, // Ignore the .babelrc file which is there for mocha tests
			presets: [ 'es2015-rollup' ],
			plugins: [
				[ "module-resolver", {
					"alias": { "pods-dfv": "./ui/js/pods-dfv" }
				} ],
				"transform-html-import-to-string"
			]
		} ),
		uglify()
	]
};