module.exports = function (grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg     : grunt.file.readJSON('package.json'),
		sass    : {
			pods: {
				options: {
					style    : 'expanded',
					sourcemap: true
				},
				files  : [
					{
						expand : true,
						src    : ['sources/ui/css/**/*.scss'],
						dest   : 'ui/css',
						ext    : '.css',
						flatten: true
					}
				]
			}
		},
		imagemin: {
			pods: {
				files: [
					{
						expand: true,
						src   : ['sources/ui/images/**/*.{png,jpg,gif}'],
						dest  : 'ui/images',
						flatten: true
					}
				]
			}
		},
		uglify  : {
			pods: {
				files: [
					'ui/css/front-end-styles.css',
					'ui/css/meta-boxes.css',
					'ui/css/pods-admin.css',
					'ui/css/pods-advanced.css',
					'ui/css/pods-form.css',
					'ui/css/pods-front.css',
					'ui/css/pods-manage.css',
					'ui/css/pods-ui-list-table.css',
					'ui/css/pods-wizard.css'
				]
			}
		},
		watch   : {
			sass: {
				files: ['sources/ui/css/**/*.scss'],
				tasks: ["sass"]
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
	});

	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-imagemin');
	grunt.loadNpmTasks('grunt-glotpress');

	grunt.registerTask('default', ['sass', 'imagemin', 'glotpress_download']);
	grunt.registerTask('production', ['sass', 'imagemin', 'uglify', 'glotpress_download'])

};