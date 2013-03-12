module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        less: {
            files: ['assets/less/*.less', 'assets/less/*/*.less'],
            production: {
                options: {
                    yuicompress: true
                },
                files: {
                    "style.css" : 'assets/less/style.less'
                }
            }
        },

        concat: {
            options: {
                separator: ';'
            },
            js: {
                src: [
                    /*
                    'assets/js/_bootstrap/bootstrap-transition.js',
                    'assets/js/_bootstrap/bootstrap-alert.js',
                    'assets/js/_bootstrap/bootstrap-button.js',
                    'assets/js/_bootstrap/bootstrap-carousel.js',
                    'assets/js/_bootstrap/bootstrap-collapse.js',
                    'assets/js/_bootstrap/bootstrap-dropdown.js',
                    'assets/js/_bootstrap/bootstrap-modal.js',
                    'assets/js/_bootstrap/bootstrap-tooltip.js',
                    'assets/js/_bootstrap/bootstrap-popover.js',
                    'assets/js/_bootstrap/bootstrap-scrollspy.js',
                    'assets/js/_bootstrap/bootstrap-tab.js',
                    'assets/js/_bootstrap/bootstrap-typeahead.js',
                    'assets/js/_bootstrap/bootstrap-affix.js',
                    'assets/js/app.jquery.js'
                    */
                    'assets/js/main.js'
                ],
                dest: 'js/<%= pkg.name %>.js'
            }
        },

        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
                        ' * <%= pkg.website %>/\n' +
                        ' * Copyright (c) <%= grunt.template.today("yyyy") %> ' +
                        '<%= pkg.author %>; <%= pkg.license %> */\n'
            },
            build: {
                src: 'js/<%= pkg.name %>.js',
                dest: 'js/<%= pkg.name %>.min.js'
            }
        },

        watch: {
            watch: {
                files: ['assets/js/*.js', 'assets/kess/*.less'],
                tasks: ['default'],
                options: {
                    nospawn: true
                }
            }
        }
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Default task(s).
    grunt.registerTask('default', ['less:production', 'concat:js', 'uglify:build']);

};
