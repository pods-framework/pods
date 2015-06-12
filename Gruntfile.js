module.exports = function ( grunt ) {

	//setup file list for copying/ not copying for SVN
	files_list = [
		'**',
		'!.git/**',
		'!.sass-cache/**',
		'!bin/**',
		'!node_modules/**',
		'!build/**',
		'!sources/**',
		'!tests/**',
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

		clean: {
			post_build: [
				'build'
			]
		},

		copy: {
			svn_trunk: {
				options: {
					mode:true
				},
				src:  files_list,
				dest: 'build/<%= pkg.name %>/trunk/'
			},
			svn_tag: {
				options: {
					mode:true
				},
				src:  files_list,
				dest: 'build/<%= pkg.name %>/tags/<%= pkg.version %>/'
			}
		},

		gittag: {
			addtag: {
				options: {
					tag: '2.x/<%= pkg.version %>',
					message: 'Pods <%= pkg.version %>'
				}
			}
		},

		gitcommit: {
			commit: {
				options: {
					message: 'Pods <%= pkg.version %>',
					noVerify: true,
					noStatus: false,
					allowEmpty: true
				},
				files: {
					src: [ 'readme.txt', 'init.php', 'package.json', 'Gruntfile.js', 'languages/**' ]
				}
			}
		},

		gitpush: {
			push: {
				options: {
					tags: true,
					remote: 'origin',
					branch: 'master'
				}
			}
		},

		replace: {
			reamdme_txt: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [{
					from: /Stable tag: (.*)/,
					to: "Stable tag: <%= pkg.version %>"
				}]

			},
			init_php: {
				src: [ 'init.php' ],
				overwrite: true,
				replacements: [{
					from: /Version:\s*(.*)/,
					to: "Version: <%= pkg.version %>"
				}, {
					from: /define\(\s*'PODS_VERSION',\s*'(.*)'\s*\);/,
					to: "define( 'PODS_VERSION', '<%= pkg.version %>' );"
				}]
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
			main: {
				src: 'build/<%= pkg.name %>',
				dest: 'http://plugins.svn.wordpress.org/<%= pkg.name %>',
				tmp: 'build/push_svn'
			}
		},

		glotpress_download: {
			core: {
				options: {
					domainPath: 'languages',
					url       : 'http://wp-translate.org',
					slug      : '<%= pkg.name %>',
					textdomain: '<%= pkg.name %>'
				}
			}
		},

		mkdir: {
			build: {
				options: {
					create: [ 'build' ]
				}
			}
		}

	} );

	//release tasks
	grunt.registerTask( 'version_number', [ 'replace:reamdme_txt', 'replace:init_php' ] );
	grunt.registerTask( 'pre_vcs', [ 'version_number', 'glotpress_download', 'mkdir:build' ] );
	grunt.registerTask( 'do_svn', [ 'svn_checkout', 'copy:svn_trunk', 'copy:svn_tag', 'push_svn' ] );
	grunt.registerTask( 'do_git', [ 'gitcommit', 'gittag', 'gitpush' ] );
	grunt.registerTask( 'release', [ 'pre_vcs', 'do_svn', 'do_git', 'clean:post_build' ] );

	//register default task
	grunt.registerTask( 'default', [
		'glotpress_download'
	] );

};