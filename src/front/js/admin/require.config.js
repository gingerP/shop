require.config({
    baseUrl: '/src/front/js/admin',
    paths: {
        lodash: '../ext/lodash.min',
        'dropbox-sdk': '../ext/Dropbox-sdk.min',
        filesize: '../ext/filesize',
        jquery: '../ext/jquery-2.1.4.min',
        axios: '../ext/axios.min'
    },
    shim: {
    },
    waitSeconds: 15
});