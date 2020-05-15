/*jshint node: true */
module.exports = function ( grunt ) {
	'use strict';

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require( 'load-grunt-tasks' )( grunt );

	grunt.loadNpmTasks( 'grunt-exec' );

	const config = {
		pkg : grunt.file.readJSON( 'package.json' ), replace : {
			version_readme_txt  : {
				src : ['readme.txt'], overwrite : true, replacements : [
					{
						from : /Stable tag: ([\.\d\w\-]*)/, to : "Stable tag: <%= pkg.version %>"
					}
				]
			}, version_init_php : {
				src : ['init.php'], overwrite : true, replacements : [
					{
						from : /Version: ([\.\d\w\-]*)/, to : "Version: <%= pkg.version %>"
					},
					{
						from : /define\( 'PODS_VERSION', '([\.\d\w\-]*)' \);/,
						to   : "define( 'PODS_VERSION', '<%= pkg.version %>' );"
					}
				]
			}
		}
	};

	// Project configuration.
	grunt.initConfig( config );

	// dev tasks
	grunt.registerTask( 'version_number', [
		'replace:version_readme_txt',
		'replace:version_init_php'
	] );
};
