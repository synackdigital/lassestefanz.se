module.exports = function(grunt) {

    // Paths
    var js_src           = 'src/js/';
    var js_dest          = 'js/';
    var coffee_src       = 'src/coffee/';
    var coffee_dest      = js_src;
    var admin_css_src    = 'src/less/admin/';
    var admin_css_dest   = 'admin/css/';

    // Load plugins
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-coffee');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),


        // LESS task
        less: {
            compile: {
                files: {
                    "admin/css/style.css" : admin_css_src + 'style.less'
                }
            },
            compress: {
                options: {
                    yuicompress: true
                },
                files: {
                    "admin/css/style.css" : admin_css_src + 'style.less'
                }
            }
        },


        coffee: {
            main: {
                files: {
                    'src/js/soundcloud.js': ['src/coffee/soundcloud.coffee']
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
                    js_src + 'soundcloud.js'
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
                files: [coffee_src+'*.coffee', coffee_src+'*/*.coffee', admin_css_src+'*.less', admin_css_src+'*/*.less'],
                tasks: ['default'],
                options: {
                    nospawn: true
                }
            }
        }
    });

    // Default task
    grunt.registerTask('default', ['less', 'coffee', 'concat', 'uglify']);
    grunt.registerTask('dist', ['default']);

};
