import string from 'rollup-plugin-string';
import babel from 'rollup-plugin-babel';
import uglify from 'rollup-plugin-uglify';

export default {
	entry     : 'ui/fields-mv/_src/pods-fields-ready.js',
	dest      : 'ui/fields-mv/js/pods-fields-ready.min.js',
	format    : 'iife',
	moduleName: 'PodsUI',
	plugins   : [
		string( { extensions: [ '.html' ] } ),
		babel( { presets: [ 'es2015-rollup' ] } ),
		uglify()
	]
};