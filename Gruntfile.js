module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		sass: {
		  dist: {
			  options: {
				style: 'compressed'
			  },
			  files: {
				'ui/css/front-end-styles.css': 'ui/css/sass/front-end-styles.scss',
				'ui/css/meta-boxes.css': 'ui/css/sass/meta-boxes.scss',
				'ui/css/pods-admin.css': 'ui/css/sass/pods-admin.scss',
				'ui/css/pods-advanced.css': 'ui/css/sass/pods-advanced.scss',
				'ui/css/pods-form.css': 'ui/css/sass/pods-form.scss',
				'ui/css/pods-front.css': 'ui/css/sass/pods-front.scss',
				'ui/css/pods-manage.css': 'ui/css/sass/pods-manage.scss',
				'ui/css/pods-ui-list-table.css': 'ui/css/sass/pods-ui-list-table.scss',
				'ui/css/pods-wizard.css': 'ui/css/sass/pods-wizard.scss',
			  }
			}
		},
		watch: {
			sass: {
				files: ['ui/css/sass/*.scss', 'ui/css/sass/partials/*.scss', 'ui/css/sass/partials/fields/*.scss'],
				tasks: ["sass"]
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-watch');

	grunt.registerTask('default', ['sass']);

}
