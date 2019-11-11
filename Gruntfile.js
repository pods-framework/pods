/*jshint node: true */
module.exports = function ( grunt ) {
	'use strict';

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require( 'load-grunt-tasks' )( grunt );

	grunt.loadNpmTasks( 'grunt-exec' );

	const release_branch_version = '2.8';

	const config = {
		pkg : grunt.file.readJSON( 'package.json' ),

		gittag       : {
			addtag : {
				options : {
					tag : '<%= pkg.version %>', message : 'Pods <%= pkg.version %>', force : true
				}
			}
		}, gitcommit : {
			commit : {
				options  : {
					message : 'Pods <%= pkg.version %>', noVerify : true, noStatus : false, allowEmpty : true
				}, files : {
					src : [
						'readme.txt',
						'init.php',
						'package.json',
						'README.md'
					]
				}
			}
		}, gitpush   : {
			push : {
				options : {
					tags : true, remote : 'origin', branch : 'master', force : true
				}
			}
		}, replace   : {
			version_readme_txt             : {
				src : ['readme.txt'], overwrite : true, replacements : [
					{
						from : /Stable tag: (.*)/, to : "Stable tag: <%= pkg.version %>"
					}
				]
			}, version_init_php            : {
				src : ['init.php'], overwrite : true, replacements : [
					{
						from : /Version: (.*)/, to : "Version: <%= pkg.version %>"
					},
					{
						from : /define\( 'PODS_VERSION', '([\.\d\w\-]*)' \);/,
						to   : "define( 'PODS_VERSION', '<%= pkg.version %>' );"
					}
				]
			}, branchfix_master_readme_md  : {
				src : ['README.md'], overwrite : true, replacements : [
					{
						from : /\?branch=(release\/|)([\.\d\w\-]*)/g, to : "?branch=master"
					},
					{
						from : /\?b=(release\/|)([\.\d\w\-]*)/g, to : "?b=master"
					},
					{
						from : /\/blob\/(release\/|)([\.\d\w\-]*)\//g, to : "/blob/master/"
					}
				]
			}, branchfix_master_init_php   : {
				src : ['init.php'], overwrite : true, replacements : [
					{
						from : /GitHub Branch: (release\/|)([\.\d\w\-]*)/, to : "GitHub Branch: master"
					}
				]
			}, branchfix_2x_readme_md      : {
				src : ['README.md'], overwrite : true, replacements : [
					{
						from : /\?branch=(release\/|)([\.\d\w\-]*)/g, to : "?branch=2.x"
					},
					{
						from : /\?b=(release\/|)([\.\d\w\-]*)/g, to : "?b=2.x"
					},
					{
						from : /\/blob\/(release\/|)([\.\d\w\-]*)\//g, to : "/blob/2.x/"
					}
				]
			}, branchfix_2x_init_php       : {
				src : ['init.php'], overwrite : true, replacements : [
					{
						from : /GitHub Branch: (release\/|)([\.\d\w\-]*)/, to : "GitHub Branch: 2.x"
					}
				]
			}, branchfix_release_readme_md : {
				src : ['README.md'], overwrite : true, replacements : [
					{
						from : /\?branch=(release\/|)([\.\d\w\-]*)/g, to : "?branch=release/" + release_branch_version
					},
					{
						from : /\?b=(release\/|)([\.\d\w\-]*)/g, to : "?b=release/" + release_branch_version
					},
					{
						from : /\/blob\/(release\/|)([\.\d\w\-]*)\//g,
						to   : "/blob/release/" + release_branch_version + "/"
					}
				]
			}, branchfix_release_init_php  : {
				src : ['init.php'], overwrite : true, replacements : [
					{
						from : /GitHub Branch: (release\/|)([\.\d\w\-]*)/,
						to   : "GitHub Branch: release/" + release_branch_version
					}
				]
			}
		}
	};

	// Project configuration.
	grunt.initConfig( config );

	// branch related tasks
	grunt.registerTask( 'branch_name_master', [
		'replace:branchfix_master_readme_md',
		'replace:branchfix_master_init_php'
	] );
	grunt.registerTask( 'branch_name_2x', [
		'replace:branchfix_2x_readme_md',
		'replace:branchfix_2x_init_php'
	] );
	grunt.registerTask( 'branch_name_release', [
		'replace:branchfix_release_readme_md',
		'replace:branchfix_release_init_php'
	] );

	// release tasks
	grunt.registerTask( 'version_number', [
		'replace:version_readme_txt',
		'replace:version_init_php'
	] );
	grunt.registerTask( 'pre_vcs', [
		'branch_name_master',
		'version_number'
	] );
	grunt.registerTask( 'do_git', [
		'gitcommit',
		'gittag',
		'gitpush'
	] );
	grunt.registerTask( 'release', [
		'pre_vcs',
		'do_git'
	] );
};
