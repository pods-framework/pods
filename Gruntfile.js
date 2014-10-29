module.exports = function (grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg     : grunt.file.readJSON('package.json'),
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

	grunt.loadNpmTasks('grunt-glotpress');

	grunt.registerTask('default', ['glotpress_download']);

};