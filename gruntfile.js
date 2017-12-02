var grunt = require('grunt');

require('load-grunt-tasks')(grunt);
grunt.initConfig({
    less: {
        dev: {
            options: {
                compress: true
            },
            files: {
                'dist/style.css': 'src/front/less/style.less'
            }
        }
    },
    watch: {
        less: {
            files: ['src/front/less/**/*.less'],
            tasks: ['less'],
            options: {
                spawn: false
            }
        }
    },
    uglify: {
        build: {
            options: {
                beautify: false,
                compress: true
            },
            files: {
                'dist/vendor1.js': [
                    'src/front/js/ext/jquery-2.1.4.min.js',
                    'src/front/js/ext/jquery.imageloader.js',
                    'src/front/js/ext/handlebars-v3.0.3.min.js'
                ],

                'dist/vendor2.js': [
                    'src/front/js/ext/angular.min.js',
                    'src/front/js/ext/q.js'
                ],
                'dist/bundle1.js': [
                    'src/front/js/components/search-input/search-input.js',
                    'src/front/js/components/common.factory.js',
                    'src/front/js/components/components.module.js',
                    'src/front/js/components/news/components/news.component.js',
                    'src/front/js/components/news.gallery/components/news.gallery.component.js',
                    'src/front/js/components/vCore*.js',
                    'src/front/js/utils.js'
                ],
                'dist/bundle2.js': [
                    'src/front/js/custom.js',
                    'src/front/js/preview.js'
                ]
            }
        }
    }
});
