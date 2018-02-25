require.config({
    baseUrl: '/src/front/js/admin',
    paths: {
        lodash: '../ext/lodash.min',
        'dropbox-sdk': '../ext/Dropbox-sdk.min',
        filesize: '../ext/filesize',
        jquery: '../ext/jquery-2.1.4.min',
        'jquery-ui': '../ext/jquery-ui-1.10.2',
        axios: '../ext/axios.min'
    },
    shim: {
        jquery: {
            deps: ['jquery-ui']
        }
    },
    waitSeconds: 15
});
