/*global module:false*/
module.exports = function(grunt) {

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-compass');
    grunt.loadNpmTasks('grunt-coffee');

// Project configuration.
grunt.initConfig({
    meta: {
        version: '0.1.0',
        banner: '/*! Lasse Stefanz - v<%= meta.version %> - ' +
        '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
        '* http://www.lassestefanz.se/\n' +
        '* Copyright (c) <%= grunt.template.today("yyyy") %> ' +
        'LS Produktions AB; Licensed MIT */'
    },
    coffee: {
        app: {
            src: ['assets/coffee/*.coffee'],
            dest: 'assets/js/coffee'
        }
    },
    concat: {
        files: ['assets/js/*/*.js', 'assets/js/*.js'],
        dist: {
            src: ['<banner:meta.banner>', '<config:concat.files>'],
            dest: 'js/main.min.js'
        }
    },
    min: {
        dist: {
            src: ['<banner:meta.banner>', '<config:concat.dist.dest>'],
            dest: '<config:concat.dist.dest>'
        }
    },
    compass: {
        files: ['assets/sass/*/*.scss', 'assets/sass/*.scss', 'assets/sass/*/*.sass', 'assets/sass/*.sass'],
        app: {
            config: 'config.rb',
            tasks: ['compass:dev', 'compass:prod']
        }
    },
    less: {
        files: ['assets/less/*.less', 'assets/less/*/*.less'],
        app: {
            options: {
                yuicompress: true
            },
            files: {
                "style.css" : 'assets/less/style.less'
            }
        }
    },
    watch: {
        app: {
            files: ['grunt.js', '<config:coffee.app.src>', 'assets/js/*.js', '<config:less.files>'],
            tasks: ['coffee:app', 'concat:dist', 'min:dist', 'less:app']
        }
    },
    jshint: {
        options: {
            curly: true,
            eqeqeq: true,
            immed: true,
            latedef: true,
            newcap: true,
            noarg: true,
            sub: true,
            undef: true,
            boss: true,
            eqnull: true,
            browser: true
        },
        globals: {}
    },
    uglify: {}
});

// Default task.
grunt.registerTask('default', 'watch:app');

};
