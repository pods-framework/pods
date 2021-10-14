/*jshint node: true */
module.exports = function( grunt ) {
	'use strict';

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require( 'load-grunt-tasks' )( grunt );

	grunt.loadNpmTasks( 'grunt-exec' );

	const config = {
		pkg: grunt.file.readJSON( 'package.json' ),
		replace: {
			version_readme_txt: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag: ([\.\d\w\-]*)/,
						to() {
							return 'Stable tag: ' + grunt.option( 'ver' ) || pkg.version;
						},
					},
				],
			},
			version_init_php: {
				src: [ 'init.php' ],
				overwrite: true,
				replacements: [
					{
						from: /Version:\s+([\.\d\w\-]*)/,
						to() {
							return 'Version: ' + grunt.option( 'ver' ) || pkg.version;
						},
					},
					{
						from: /define\( 'PODS_VERSION', '([\.\d\w\-]*)' \);/,
						to() {
							return "define( 'PODS_VERSION', '" + ( grunt.option( 'ver' ) || pkg.version ) + "' );";
						},
					},
				],
			},
			version_package: {
				src: [ 'package.json' ],
				overwrite: true,
				replacements: [
					{
						from: /"version": "([\.\d\w\-]*)"/,
						to() {
							return '"version": "' + ( grunt.option( 'ver' ) || pkg.version ) + '"';
						},
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
