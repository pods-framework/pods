import string from 'rollup-plugin-string';
import babel from 'rollup-plugin-babel';
import uglify from 'rollup-plugin-uglify';

export default {
	entry     : 'ui/fields-mv/_src/pods-mv-fields.js',
	dest      : 'ui/fields-mv/js/pods-mv-fields.min.js',
	format    : 'iife',
	moduleName: 'PodsMVFields',
	plugins   : [
		string( { extensions: [ '.html' ] } ),
		babel( {
			babelrc: false, // Ignore the .babelrc file which is there for mocha tests
			presets: [ 'es2015-rollup' ]
		} ),
		uglify()
	]
};