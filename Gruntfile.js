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
					cwd: 'ui/css/',
					src: ['ui/css/**/*.css'],
					dest: 'ui/css/'
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
				files: {
					'ui/css/style.css': 'sources/sass/style.scss'
				}
			}
		},

		autoprefixer: {
			options: {
				browsers: ['> 1%', 'last 2 versions', 'Firefox ESR', 'Opera 12.1']
			},
			dist: {
				src:  'ui/css/style.css'
			}
		},

		cmq: {
			options: {
				log: false
			},
			dist: {
				files: {
					'ui/css/style.css': 'ui/css/style.css'
				}
			}
		},

		cssmin: {
			minify: {
				expand: true,
				cwd: 'ui/css/',
				src: ['*.css', '!*.min.css'],
				dest: 'ui/css/',
				ext: '.min.css'
			}
		},

		concat: {
			dist: {
				src: [
					'ui/js/concat/*.js'
				],
				dest: 'ui/js/pods.js'
			}
		},

		uglify: {
			build: {
				options: {
					mangle: false
				},
				files: [{
					expand: true,
					cwd: 'ui/js/',
					src: ['**/*.js', '!**/*.min.js', '!partials/*.js'],
					dest: 'ui/js/',
					ext: '.min.js'
				}]
			}
		},

		imagemin: {
			dynamic: {
				files: [{
					expand: true,
					cwd: 'ui/images/',
					src: ['**/*.{png,jpg,gif}'],
					dest: 'ui/images/'
				}]
			}
		},

		watch: {

			scripts: {
				files: ['ui/js/**/*.js'],
				tasks: ['javascript'],
				options: {
					spawn: false
				}
			},

			css: {
				files: ['sources/sass/**/*.scss'],
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
			js: ['ui/js/project*', 'ui/js/**/*.min.js'],
			css: ['ui/css/style.css', 'ui/css/style.min.css']
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

	grunt.registerTask('styles', ['sass', 'autoprefixer', 'cmq', 'csscomb', 'cssmin']);
	grunt.registerTask('javascript', ['concat', 'uglify']);
	grunt.registerTask('imageminnewer', ['newer:imagemin']);
	grunt.registerTask('i18n', ['makepot', 'glotpress_download']);
	grunt.registerTask('default', ['update_submodules', 'sprite', 'styles', 'javascript', 'imageminnewer', 'i18n']);

};