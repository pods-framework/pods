/*jshint node: true */
module.exports = function( grunt ) {
	'use strict';

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require( 'load-grunt-tasks' )( grunt );

	grunt.loadNpmTasks( 'grunt-exec' );

	const pkg = grunt.file.readJSON( 'package.json' );
	const version_number = grunt.option( 'ver' ) || pkg.version;
	const config = {
		replace: {
			version_readme_txt: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag:(\s+)([\.\d\w\-]*)/,
						to: 'Stable tag:$1' + version_number,
					},
				],
			},
			version_init_php: {
				src: [ 'init.php' ],
				overwrite: true,
				replacements: [
					{
						from: /Version:(\s+)([\.\d\w\-]*)/,
						to: 'Version:$1' + version_number,
					},
					{
						from: /define\( '([\w_]+)_VERSION', '([\.\d\w\-]*)' \);/,
						to: "define( '$1_VERSION', '" + version_number + "' );",
					},
				],
			},
			version_package: {
				src: [ 'package.json' ],
				overwrite: true,
				replacements: [
					{
						from: /"version": "([\.\d\w\-]*)"/,
						to: '"version": "' + version_number + '"',
					},
				],
			},
		},
	};

	// Project configuration.
	grunt.initConfig( config );

	// dev tasks
	grunt.registerTask( 'version_number', [
		'replace:version_package',
		'replace:version_readme_txt',
		'replace:version_init_php',
	] );
};
