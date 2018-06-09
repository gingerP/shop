require.config({
    baseUrl: '/src/front/js/admin',
    paths: {
        lodash: '../ext/lodash.min',
        dropboxSdk: '../ext/Dropbox-sdk.min',
        filesize: '../ext/filesize',
        bazil: '../ext/basil.min',
        jquery: '../ext/jquery-2.1.4.min',
        jqueryUi: '../ext/jquery-ui-1.10.2',
        axios: '../ext/axios.min'
    },
    shim: {
        jquery: {
            deps: ['jqueryUi']
        }
    },
    waitSeconds: 15
});
