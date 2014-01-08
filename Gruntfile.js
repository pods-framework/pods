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
				'ui/css/admin-styles.css': 'ui/css/sass/admin-styles.scss'
			  }
			}
		},
		watch: {
			sass: {
				files: ['ui/css/sass/*.scss', 'ui/css/sass/partials/*.scss'],
				tasks: ["sass"]
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-watch');

	grunt.registerTask('default', ['sass']);

}
