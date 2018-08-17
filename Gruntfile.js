/*jshint node: true */
module.exports = function ( grunt ) {
	'use strict';

	//setup file list for copying/ not copying for SVN
	var files_list = [
		'**',
		'!.git/**',
		'!.idea/**',
		'!.sass-cache/**',
		'!bin/**',
		'!node_modules/**',
		'!build/**',
		'!sources/**',
		'!tests/**',
		'!vendor/**',
		'!.babelrc',
		'!.gitattributes',
		'!.gitignore',
		'!.gitmodules',
		'!.jshintrc',
		'!.scrutinizer.yml',
		'!.travis.yml',
		'!audit.sh',
		'!CODEOWNERS',
		'!composer.json',
		'!composer.lock',
		'!CONTRIBUTING.md',
		'!git-workflow.md',
		'!grunt-workflow.md',
		'!Gruntfile.js',
		'!package.json',
		'!package-lock.json',
		'!phpcs.ruleset.xml',
		'!phpcs.xml',
		'!phpcs.xml.dist',
		'!phpunit.xml.dist',
		'!README.md',
		'!phpcs-report-full.txt',
		'!report-full.txt',
		'!report-full-2.7.txt',
		'!report-full-after.txt',
		'!phpcs-report-source.txt',
		'!report-source.txt',
		'!report-source-2.7.txt',
		'!report-source-after.txt',
		'!rollup.config.js'
	];

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require( 'load-grunt-tasks' )( grunt );

	grunt.loadNpmTasks( 'grunt-exec' );

	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),

		clean: {
			post_build: [
				'build'
			]
		},

		copy: {
			svn_trunk: {
				expand : true,
				options: {
					mode: true
				},
				src    : files_list,
				dest   : 'build/<%= pkg.name %>/trunk/'
			}
		},

		gittag: {
			addtag: {
				options: {
					tag    : '2.x/<%= pkg.version %>',
					message: 'Pods <%= pkg.version %>'
				}
			}
		},

		gitcommit: {
			commit: {
				options: {
					message   : 'Pods <%= pkg.version %>',
					noVerify  : true,
					noStatus  : false,
					allowEmpty: true
				},
				files  : {
					src: [
						'readme.txt',
						'init.php',
						'package.json',
						'Gruntfile.js',
						'README.md'
					]
				}
			}
		},

		gitpush: {
			push: {
				options: {
					tags  : true,
					remote: 'origin',
					branch: 'master'
				}
			}
		},

		replace: {
			version_readme_txt          : {
				src         : [ 'readme.txt' ],
				overwrite   : true,
				replacements: [ {
					from: /Stable tag: (.*)/,
					to  : "Stable tag: <%= pkg.version %>"
				} ]

			},
			version_init_php            : {
				src         : [ 'init.php' ],
				overwrite   : true,
				replacements: [ {
					from: /Version: (.*)/,
					to  : "Version: <%= pkg.version %>"
				}, {
					from: /define\( 'PODS_VERSION', '([\.\d\w\-]*)' \);/,
					to  : "define( 'PODS_VERSION', '<%= pkg.version %>' );"
				} ]
			},
			branchfix_master_readme_md  : {
				src         : [ 'README.md' ],
				overwrite   : true,
				replacements: [ {
					from: /\?branch=(release\/|)([\.\d\w\-]*)/g,
					to  : "?branch=master"
				}, {
					from: /\?b=(release\/|)([\.\d\w\-]*)/g,
					to  : "?b=master"
				}, {
					from: /\/blob\/(release\/|)([\.\d\w\-]*)\//g,
					to  : "/blob/master/"
				} ]

			},
			branchfix_2x_readme_md      : {
				src         : [ 'README.md' ],
				overwrite   : true,
				replacements: [ {
					from: /\?branch=(release\/|)([\.\d\w\-]*)/g,
					to  : "?branch=2.x"
				}, {
					from: /\?b=(release\/|)([\.\d\w\-]*)/g,
					to  : "?b=2.x"
				}, {
					from: /\/blob\/(release\/|)([\.\d\w\-]*)\//g,
					to  : "/blob/2.x/"
				} ]

			},
			branchfix_release_readme_md : {
				src         : [ 'README.md' ],
				overwrite   : true,
				replacements: [ {
					from: /\?branch=(release\/|)([\.\d\w\-]*)/g,
					to  : "?branch=release/2.8"
				}, {
					from: /\?b=(release\/|)([\.\d\w\-]*)/g,
					to  : "?b=release/2.8"
				}, {
					from: /\/blob\/(release\/|)([\.\d\w\-]*)\//g,
					to  : "/blob/release/2.8/"
				} ]

			}
		},

		svn_checkout: {
			make_local: {
				repos: [
					{
						path: [ 'build' ],
						repo: 'http://plugins.svn.wordpress.org/<%= pkg.name %>'
					}
				]
			}
		},

		push_svn: {
			options: {
				remove: true
			},
			main   : {
				src : 'build/<%= pkg.name %>/trunk',
				dest: 'http://plugins.svn.wordpress.org/<%= pkg.name %>/trunk',
				tmp : 'build/push_svn/trunk'
			}
		},

		svn_copy: {
			options: {},
			files  : {
				// This is switched, dest = source and src = target, svn_copy code is wrong
				// See: https://github.com/ColmMcBarron/grunt-svn-copy/issues/1
				dest: 'http://plugins.svn.wordpress.org/<%= pkg.name %>/trunk',
				src : 'http://plugins.svn.wordpress.org/<%= pkg.name %>/tags/<%= pkg.version %> -m "<%= pkg.name %>/<%= pkg.version %>"'
			}
		},

		mkdir: {
			build: {
				options: {
					mode  : parseInt( '0755', 8 ),
					create: [ 'build' ]
				}
			}
		}

	} );

	// branch related tasks
	grunt.registerTask( 'branch_name_master', [ 'replace:branchfix_master_readme_md' ] );
	grunt.registerTask( 'branch_name_2x', [ 'replace:branchfix_2x_readme_md' ] );
	grunt.registerTask( 'branch_name_release', [ 'replace:branchfix_release_readme_md' ] );

	// release tasks
	grunt.registerTask( 'version_number', [ 'replace:version_readme_txt', 'replace:version_init_php' ] );
	grunt.registerTask( 'pre_vcs', [ 'branch_name_master', 'version_number', 'clean:post_build', 'mkdir:build' ] );
	grunt.registerTask( 'do_svn', [ 'svn_checkout', 'copy:svn_trunk', 'push_svn', 'svn_copy' ] );
	grunt.registerTask( 'do_git', [ 'gitcommit', 'gittag', 'gitpush' ] );
	grunt.registerTask( 'release', [ 'pre_vcs', 'do_svn', 'do_git', 'clean:post_build' ] );
};
