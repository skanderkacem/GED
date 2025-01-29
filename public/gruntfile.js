module.exports = function (grunt) {
  grunt.initConfig({
      uncss: {
          dist: {
              files: [
                  { src: './src/home/template.html.twig', dest: 'assets/css/styles2.css' }
              ]
          }
      },
      cssmin: {
          dist: {
              files: [
                  { src: 'assets/css/styles2.css', dest: 'assets/css/styles2.css' }
              ]
          }
      }
  });

  // Load the plugins
  grunt.loadNpmTasks('grunt-uncss');
  grunt.loadNpmTasks('grunt-contrib-cssmin');

  // Default tasks
  grunt.registerTask('default', ['uncss', 'cssmin']);
};