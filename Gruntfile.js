module.exports = function(grunt) {

    // Paths
    var cmp_src     = 'components/';
    var js_src      = 'src/js/';
    var js_dest     = 'wordpress/wp-content/themes/lasse-stefanz/js/';
    var coffee_src  = 'src/coffee/';
    var coffee_dest = js_src;
    var css_src     = 'src/less/';
    var css_dest    = 'wordpress/wp-content/themes/lasse-stefanz/';

    // Load plugins
    grunt.loadNpmTasks('grunt-string-replace');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-coffee');

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // Copy task
        copy: {
          main: {
            files: [
              {
                expand: true,
                cwd: cmp_src+'fancybox/source/',
                src: [
                    '*.png',
                    '*.gif'
                ],
                dest: css_dest+'images/'
              },
              {
                expand: true,
                cwd: 'external/CSS3MultiColumn/',
                src: [
                    'css3-multi-column.min.js'
                ],
                dest: css_dest+'js/'
              }
              // {
              //   expand: true,
              //   cwd: cmp_src+'background-size-polyfill/',
              //   src: [
              //       '*.htc'
              //   ],
              //   dest: css_dest
              // }
            ]
          }
        },

        // String replace task
        'string-replace': {
          main: {
            files: {
              'wordpress/wp-content/themes/lasse-stefanz/style.css': 'wordpress/wp-content/themes/lasse-stefanz/style.css'
            },
            options: {
              replacements: [
                {
                  pattern: "url('fancybox_",
                  replacement: "url('images/fancybox_"
                }
              ]
            }
          }
        },

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

        // Coffee task
        coffee: {
            main: {
                files: {
                    'src/js/main.js': [coffee_src + 'main.coffee']
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
                    cmp_src+'jquery-mousewheel/jquery.mousewheel.js',
                    cmp_src+'fancybox/source/jquery.fancybox.js',
                    cmp_src+'flexslider-less/jquery.flexslider.js',
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
                files: [coffee_src+'*.coffee', coffee_src+'*/*.coffee', css_src+'*.less', css_src+'*/*.less'],
                tasks: ['default'],
                options: {
                    nospawn: true
                }
            }
        }
    });

    // Default task

    grunt.registerTask('style', ['less']);
    grunt.registerTask('copy_files', ['string-replace-loop', 'copy']);
    grunt.registerTask('scripts', ['coffee', 'concat', 'uglify']);

    grunt.registerTask('default', ['style', 'scripts']);
    grunt.registerTask('dist', ['copy_files', 'default']);

    // Loop through string-replace
    // Since string-replace seems broken we must loop a few times manually
    grunt.registerTask('string-replace-loop', ['string-replace', 'string-replace', 'string-replace', 'string-replace']);

};
