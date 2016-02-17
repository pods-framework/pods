import string from 'rollup-plugin-string';
import babel from 'rollup-plugin-babel';
import uglify from 'rollup-plugin-uglify';

export default {
	entry     : 'ui/js/pods-ui-ready.js',
	dest      : 'ui/js/pods-ui-ready.min.js',
	format    : 'iife',
	moduleName: 'PodsUI',
	plugins   : [
		string( { extensions: [ '.html' ] } ),
		babel( { presets: [ 'es2015-rollup' ] } ),
		uglify()
	]
};