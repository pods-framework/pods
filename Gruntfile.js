module.exports = function (grunt) {

	//setup file list for copying/ not copying for SVN
	files_list = [
		'**',
		'!node_modules/**',
		'!release/**',
		'!.git/**',
		'!.sass-cache/**',
		'!Gruntfile.js',
		'!package.json',
		'!.gitignore',
		'!.gitmodules',
		'!bin/**',
		'!tests/**',
		'!.gitattributes',
		'!.travis.yml',
		'!composer.lock',
		'!composer.json',
		'!CONTRIBUTING.md',
		'!git-workflow.md',
		'!phpunit.xml.dist'
	];

	// Project configuration.
	grunt.initConfig({
		pkg     : grunt.file.readJSON( 'package.json' ),
		glotpress_download : {
			core : {
				options : {
					domainPath : 'languages',
					url        : 'http://wp-translate.org',
					slug       : 'pods',
					textdomain : 'pods'
				}
			}
		},
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
					from: "<%= pkg.last_version %>",
					to: "<%= pkg.version %>"
				}]
			},
			reamde_txt: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [{
					from: "<%= pkg.last_version %>",
					to: "<%= pkg.version %>"
				}]
			},
			init_php: {
				src: [ 'init.php' ],
				overwrite: true,
				replacements: [{
					from: "<%= pkg.last_version %>",
					to: "<%= pkg.version %>"
				}]
			}
		},
		svn_checkout: {
			make_local: {
				repos: [
					{
						path: [ 'release' ],
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
				src: 'release/<%= pkg.name %>',
				dest: 'http://plugins.svn.wordpress.org/pods',
				tmp: 'build/make_svn'
			}
		}
	});

	//load modules
	grunt.loadNpmTasks( 'grunt-glotpress' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-git' );
	grunt.loadNpmTasks( 'grunt-text-replace' );
	grunt.loadNpmTasks( 'grunt-svn-checkout' );
	grunt.loadNpmTasks( 'grunt-push-svn' );
	grunt.loadNpmTasks( 'grunt-remove' );

	//register default task
	grunt.registerTask( 'default', [ 'glotpress_download' ]);

	//release tasks
	grunt.registerTask( 'version_number', [ 'replace:reamde_md', 'replace:reamde_txt', 'replace:init_php' ] );
	grunt.registerTask( 'pre_vcs', [ 'version_number', 'glotpress_download' ] );
	grunt.registerTask( 'do_svn', [ 'svn_checkout', 'copy:svn_trunk', 'copy:svn_tag', 'push_svn' ] );
	grunt.registerTask( 'do_git', [ 'gitcommit', 'gittag', 'gitpush' ] );

	grunt.registerTask( 'release', [ 'pre_vcs', 'do_svn', 'do_git', 'clean:post_build' ] );


};
