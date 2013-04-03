module.exports = function(grunt) {

    // Paths
    var cmp_src    = 'components/';
    var js_src     = 'src/js/';
    var js_dest    = 'wordpress/wp-content/themes/lasse-stefanz/js/';
    var css_src    = 'src/less/';
    var css_dest   = 'wordpress/wp-content/themes/lasse-stefanz/';

    // Load plugins
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // LESS task
        less: {
            compile: {
                files: {
                    "wordpress/wp-content/themes/lasse-stefanz/style.css" : 'src/less/style.less'
                }
            },
            compress: {
                options: {
                    yuicompress: true
                },
                files: {
                    "wordpress/wp-content/themes/lasse-stefanz/style.css" : 'src/less/style.less'
                }
            }
        },

        // Concat task
        concat: {
            options: {
                separator: ';'
            },
            main: {
                src: [
                    js_src+'main.js'
                ],
                dest: js_dest+'<%= pkg.name %>.js'
            }
        },

        // Uglify task
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
                        ' * <%= pkg.website %>/\n' +
                        ' * Copyright (c) <%= grunt.template.today("yyyy") %> ' +
                        '<%= pkg.author %>; <%= pkg.license %> */\n'
            },
            main: {
                src: js_dest+'<%= pkg.name %>.js',
                dest: js_dest+'<%= pkg.name %>.min.js'
            }
        },

        // Watch task
        watch: {
            main: {
                files: [js_src+'*.js', js_src+'*/*.js', css_src+'*.less', css_src+'*/*.less'],
                tasks: ['default'],
                options: {
                    nospawn: true
                }
            }
        }
    });

    // Default task
    grunt.registerTask('default', ['less:compile', 'concat']);
    grunt.registerTask('dist', ['default', 'less:compress', 'uglify']);

};
