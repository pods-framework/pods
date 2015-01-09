module.exports = function(grunt) {

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require('load-grunt-tasks')(grunt);

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		githooks: {
			all: {
				'pre-commit': 'default'
			}
		},

		sprite: {
			all: {
				'src': 'sources/images/sprites/*.png',
				'destImg': 'ui/images/sprites.png',
				'destCSS': 'sources/sass/partials/_sprites.scss',
				'imgPath': 'ui/images/sprites.png',
				'algorithm': 'binary-tree'
			}
		},

		csscomb: {
			dist: {
				files: [{
					expand: true,
					cwd: 'sources/css/',
					src: ['sources/css/*.css'],
					dest: 'sources/css/'
				}]
			}
		},

		sass: {
			dist: {
				options: {
					style: 'expanded',
					lineNumbers: true
					/*loadPath: [
						'bower_components/bourbon/app/assets/stylesheets',
						'bower_components/neat/app/assets/stylesheets'
					]*/
				},
				files: [ {
					expand : true,
					cwd : 'sources/sass/',
					src : ['sources/sass/*.scss'],
					dest : 'sources/css/',
					ext : '.css'
				} ]
			}
		},

		cssmin: {
			minify: {
				expand: true,
				cwd: 'sources/css/',
				src: '*.css',
				dest: 'ui/css/',
				ext: '.css'
			}
		},

		uglify: {
			build: {
				options: {
					mangle: false
				},
				files: [{
					expand: true,
					cwd: 'sources/js/',
					src: '*.js',
					dest: 'ui/js/',
					ext: '.js'
				}]
			}
		},

		imagemin: {
			dynamic: {
				files: [{
					expand: true,
					cwd: 'sources/images/',
					src: ['**/*.{png,jpg,gif}'],
					dest: 'ui/images/'
				}]
			}
		},

		watch: {

			scripts: {
				files: ['sources/js/*.js'],
				tasks: ['javascript'],
				options: {
					spawn: false
				}
			},

			css: {
				files: ['sources/sass/*.scss'],
				tasks: ['styles'],
				options: {
					spawn: false,
					livereload: true
				}
			},

			sprite: {
				files: ['ui/images/sprites/*.png'],
				tasks: ['sprite', 'styles'],
				options: {
					spawn: false,
					livereload: true
				}
			}

		},

		shell: {
			grunt: {
				command: ''
			}
		},

		clean: {
			js: ['ui/js/*.js'],
			css: ['ui/css/*.css']
		},

		makepot: {
			pods: {
				options: {
					cwd: '/',
					domainPath: '/languages/',
					potFilename: 'pods.pot',
					type: 'wp-plugin'
				}
			}
		},

		addtextdomain: {
			pods: {
				options: {
					textdomain: 'pods'
				},
				target: {
					files: {
						src: ['*.php']
					}
				}
			}
		},

		update_submodules: {

			default: {
				options: {
					// default command line parameters will be used: --init --recursive
				}
			},
			withCustomParameters: {
				options: {
					params: '--force' // specifies your own command-line parameters
				}
			}

		},

		phpcs: {
			application: {
				dir: [
					'**/*.php',
					'!**/node_modules/**'
				]
			},
			options: {
				bin: '~/phpcs/scripts/phpcs',
				standard: 'WordPress'
			}
		}

	});

	grunt.registerTask('styles', ['sass', 'csscomb', 'cssmin']);
	grunt.registerTask('javascript', ['uglify']);
	grunt.registerTask('imageminnewer', ['newer:imagemin']);
	grunt.registerTask('i18n', ['makepot', 'glotpress_download']);
	//grunt.registerTask('default', ['sprite', 'styles', 'javascript', 'imageminnewer', 'i18n']);
	grunt.registerTask('default', ['sprite', 'styles', 'javascript', 'imageminnewer']);

};