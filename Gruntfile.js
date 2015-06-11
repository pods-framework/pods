module.exports = function ( grunt ) {

	//setup file list for copying/ not copying for SVN
	files_list = [
		'**',
		'!.git/**',
		'!.sass-cache/**',
		'!bin/**',
		'!node_modules/**',
		'!release/**',
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
		'!package.json',
		'!phpunit.xml.dist'
	];

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require( 'load-grunt-tasks' )( grunt );

	// Project configuration.
	grunt.initConfig( {
		pkg : grunt.file.readJSON( 'package.json' ),

		clean: {
			post_build: [
				'build'
			]
		},

		copy: {
			svn_trunk: {
				options : {
					mode :true
				},
				src:  files_list,
				dest: 'build/<%= pkg.name %>/trunk/'
			},
			svn_tag: {
				options : {
					mode :true
				},
				src:  files_list,
				dest: 'build/<%= pkg.name %>/tags/<%= pkg.version %>/'
			}
		},

		gittag: {
			addtag: {
				options: {
					tag: '2.x/<%= pkg.version %>',
					message: 'Version <%= pkg.version %>'
				}
			}
		},

		gitcommit: {
			commit: {
				options: {
					message: 'Version <%= pkg.version %>',
					noVerify: true,
					noStatus: false,
					allowEmpty: true
				},
				files: {
					src: [ 'README.md', 'readme.txt', 'init.php', 'package.json', 'languages/**' ]
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
			reamde_md: {
				src: [ 'README.md' ],
				overwrite: true,
				replacements: [{
					from: /~Current Version:\s*(.*)~/,
					to: "~Current Version: <%= pkg.version %>~"
				}, {
					from: /Latest Stable Release:\s*\[(.*)\]\s*\(https:\/\/github.com\/pods-framework\/pods\/releases\/tag\/(.*)\s*\)/,
					to: "Latest Stable Release: [<%= pkg.git_tag %>](https://github.com/pods-framework/pods/releases/tag/<%= pkg.git_tag %>)"
				}]
			},
			reamde_txt: {
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
						path: [ 'release/<%= pkg.version %>' ],
						repo: 'http://plugins.svn.wordpress.org/pods'
					}
				]
			}
		},

		push_svn: {
			options: {
				remove: true
			},
			main: {
				src: 'release/<%= pkg.version %>',
				dest: 'http://plugins.svn.wordpress.org/pods',
				tmp: 'build/make_svn'
			}
		},

		glotpress_download : {
			core : {
				options : {
					domainPath : 'languages',
					url        : 'http://wp-translate.org',
					slug       : 'pods',
					textdomain : 'pods'
				}
			}
		}

	} );

	//release tasks
	grunt.registerTask( 'version_number', [ 'replace:reamde_md', 'replace:reamde_txt', 'replace:init_php' ] );
	grunt.registerTask( 'pre_vcs', [ 'version_number', 'glotpress_download' ] );
	grunt.registerTask( 'do_svn', [ 'svn_checkout', 'copy:svn_trunk', 'copy:svn_tag', 'push_svn' ] );
	grunt.registerTask( 'do_git', [ 'gitcommit', 'gittag', 'gitpush' ] );
	grunt.registerTask( 'release', [ 'pre_vcs', 'do_svn', 'do_git', 'clean:post_build' ] );

	//register default task
	grunt.registerTask( 'default', [
		'glotpress_download'
	] );

};