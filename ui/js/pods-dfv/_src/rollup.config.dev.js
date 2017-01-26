import babel from 'rollup-plugin-babel';

export default {
	"entry"     : 'ui/js/pods-dfv/_src/pods-dfv.js',
	"dest"      : 'ui/js/pods-dfv/pods-dfv.min.js',
	"format"    : 'iife',
	"moduleName": 'PodsDFV',
	"plugins"   : [
		babel( {
			"babelrc": false, // Ignore the .babelrc file which is there for mocha tests
			"presets": [ 'es2015-rollup' ],
			"plugins": [
				"transform-html-import-to-string",
				"babel-root-import"
			]
		} )
	]
};