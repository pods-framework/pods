/*jshint node: true */
module.exports = function ( grunt ) {
	'use strict';

	var babel = require( 'rollup-plugin-babel' );
	var uglify = require( 'rollup-plugin-uglify' );

	//setup file list for copying/ not copying for SVN
	var files_list = [
		'**',
		'!.git/**',
		'!.sass-cache/**',
		'!bin/**',
		'!node_modules/**',
		'!build/**',
		'!sources/**',
		'!tests/**',
		'!vendor/**',
		'!.gitattributes',
		'!.gitignore',
		'!.gitmodules',
		'!.travis.yml',
		'!composer.json',
		'!composer.lock',
		'!CONTRIBUTING.md',
		'!Gruntfile.js',
		'!git-workflow.md',
		'!grunt-workflow.md',
		'!package.json',
		'!phpcs.ruleset.xml',
		'!phpunit.xml.dist',
		'!README.md'
	];

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require( 'load-grunt-tasks' )( grunt );

	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),

		rollup: {
			options: {
				sourceMap : true,
				format    : 'iife',
				moduleName: 'PodsUI',
				plugins   : [
					babel( { presets: [ "es2015-rollup" ] } ),
					uglify()
				]
			},
			files: {
				src : 'ui/js/pods-ui-ready.js',
				dest: 'ui/js/pods-ui-ready.min.js'
			}
		},

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
					src: [ 'readme.txt', 'init.php', 'package.json', 'Gruntfile.js', 'languages/**' ]
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
			version_reamdme_txt         : {
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
			branchfix_master_reamdme_md : {
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
			branchfix_master_init_php   : {
				src         : [ 'init.php' ],
				overwrite   : true,
				replacements: [ {
					from: /GitHub Branch: (release\/|)([\.\d\w\-]*)/,
					to  : "GitHub Branch: master"
				} ]
			},
			branchfix_2x_reamdme_md     : {
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
			branchfix_2x_init_php       : {
				src         : [ 'init.php' ],
				overwrite   : true,
				replacements: [ {
					from: /GitHub Branch: (release\/|)([\.\d\w\-]*)/,
					to  : "GitHub Branch: 2.x"
				} ]
			},
			branchfix_release_reamdme_md: {
				src         : [ 'README.md' ],
				overwrite   : true,
				replacements: [ {
					from: /\?branch=(release\/|)([\.\d\w\-]*)/g,
					to  : "?branch=release/3.0"
				}, {
					from: /\?b=(release\/|)([\.\d\w\-]*)/g,
					to  : "?b=release/3.0"
				}, {
					from: /\/blob\/(release\/|)([\.\d\w\-]*)\//g,
					to  : "/blob/release/3.0/"
				} ]

			},
			branchfix_release_init_php  : {
				src         : [ 'init.php' ],
				overwrite   : true,
				replacements: [ {
					from: /GitHub Branch: (release\/|)([\.\d\w\-]*)/,
					to  : "GitHub Branch: release/3.0"
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
	grunt.registerTask( 'branch_name_master', [ 'replace:branchfix_master_reamdme_md', 'replace:branchfix_master_init_php' ] );
	grunt.registerTask( 'branch_name_2x', [ 'replace:branchfix_2x_reamdme_md', 'replace:branchfix_2x_init_php' ] );
	grunt.registerTask( 'branch_name_release', [ 'replace:branchfix_release_reamdme_md', 'replace:branchfix_release_init_php' ] );

	// release tasks
	grunt.registerTask( 'version_number', [ 'replace:version_reamdme_txt', 'replace:version_init_php' ] );
	grunt.registerTask( 'pre_vcs', [ 'branch_name_master', 'version_number', 'clean:post_build', 'mkdir:build' ] );
	grunt.registerTask( 'do_svn', [ 'svn_checkout', 'copy:svn_trunk', 'push_svn', 'svn_copy' ] );
	grunt.registerTask( 'do_git', [ 'gitcommit', 'gittag', 'gitpush' ] );
	grunt.registerTask( 'release', [ 'pre_vcs', 'do_svn', 'do_git', 'clean:post_build' ] );

};
