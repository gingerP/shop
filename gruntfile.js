var grunt = require('grunt');

require('load-grunt-tasks')(grunt);
grunt.initConfig({
    less: {
        site: {
            options: {
                compress: true
            },
            files: {
                'dist/style.css': 'src/front/less/site/index.less'
            }
        },
        admin: {
            options: {
                compress: true
            },
            files: {
                'dist/admin-style.css': 'src/front/less/admin/index.less'
            }
        }
    },
    watch: {
        site: {
            files: ['src/front/less/site/**/*.less'],
            tasks: ['less:site'],
            options: {
                spawn: false
            }
        },
        admin: {
            files: ['src/front/less/admin/**/*.less'],
            tasks: ['less:admin'],
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
                'dist/main-page.js': [
                    'src/front/js/ext/jquery-2.1.4.min.js',
                    'node_modules/mustache/mustache.js',
                    'src/front/js/components/core/keyboard/keyboard.component.js',
                    'src/front/js/components/search-input/search-input.component.js',
                    'src/front/js/components/top-menu/top-menu.component.js',
                    'src/front/js/components/google-map/google-map.component.js',
                    'src/front/js/components/main-page-contacts/mainPageContacts.component.js',
                    'src/front/js/components/vCore*.js',
                    'src/front/js/utils.js'
                ],
                'dist/catalog-page.js': [
                    'src/front/js/ext/jquery-2.1.4.min.js',
                    'node_modules/mustache/mustache.js',
                    'src/front/js/components/core/keyboard/keyboard.component.js',
                    'src/front/js/components/search-input/search-input.component.js',
                    'src/front/js/components/top-menu/top-menu.component.js',
                    'src/front/js/components/vCore*.js',
                    'src/front/js/utils.js'
                ],
                'dist/product-page.js': [
                    'src/front/js/ext/jquery-2.1.4.min.js',
                    'node_modules/mustache/mustache.js',
                    'src/front/js/components/core/keyboard/keyboard.component.js',
                    'src/front/js/components/search-input/search-input.component.js',
                    'src/front/js/components/top-menu/top-menu.component.js',
                    'src/front/js/components/core/image-zoom/image-zoom.component.js',
                    'src/front/js/components/core/images-gallery/images-gallery.component.js',
                    'src/front/js/components/email-form/email-form.component.js',
                    'src/front/js/components/products/product.component.js',
                    'src/front/js/components/vCore*.js',
                    'src/front/js/utils.js'
                ],
                'dist/contacts-page.js': [
                    'src/front/js/ext/jquery-2.1.4.min.js',
                    'node_modules/mustache/mustache.js',
                    'src/front/js/components/core/keyboard/keyboard.component.js',
                    'src/front/js/components/search-input/search-input.component.js',
                    'src/front/js/components/google-map/google-map.component.js',
                    'src/front/js/components/contacts/contacts.component.js',
                    'src/front/js/components/vCore*.js',
                    'src/front/js/utils.js'
                ],
                'dist/delivery-page.js': [
                    'src/front/js/ext/jquery-2.1.4.min.js',
                    'node_modules/mustache/mustache.js',
                    'src/front/js/components/core/keyboard/keyboard.component.js',
                    'src/front/js/components/search-input/search-input.component.js',
                    'src/front/js/components/vCore*.js',
                    'src/front/js/utils.js'
                ]
            }
        }
    }
});

grunt.task.registerTask('build', ['less', 'uglify']);
