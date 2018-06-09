define([
    'common/authorization',
    'lodash',
    'axios'
], function (/**@type Authorization*/
             Authorization, _, axios) {
    'use strict';
    var winOpened = false;

    function requestAuthorization(userName, password) {
        return axios({
            method: 'POST',
            data: {username: userName, password: password},
            dataType: 'json',
            headers: {contentType: 'application/json;charset=utf-8'},
            url: '/api/admin/auth'
        });
    }

    function openLoginWindow() {
        document.documentElement.className += ' dhtmlx_dom';
        document.body.className += ' dhtmlx_dom';

        var dhxWins = new dhtmlXWindows({
            wins: [
                {
                    id: 'authorize',
                    left: 20,
                    top: 30,
                    width: 320,
                    height: 300,
                    text: 'Логин',
                    keep_in_viewport: true,
                    resize: false
                }
            ]
        });
        var win = dhxWins.window('authorize');
        win.button('close').hide();
        win.button('minmax').hide();
        win.button('park').hide();
        win.denyMove();
        win.centerOnScreen();
        winOpened = true;
        return win;
    }

    function initLoginForm(loginWin, successCallback) {
        var loginForm = loginWin.attachForm([
            {type: 'settings', position: 'label-left', labelWidth: 130, inputWidth: 270},
            {
                type: 'block', width: 300,
                list: [
                    {
                        type: 'input', label: 'Пользователь', validate: 'Empty',
                        name: 'username', required: true, position: 'label-left'
                    },
                    {
                        type: 'password', label: 'Пароль', validate: 'Empty',
                        name: 'password', required: true, position: 'label-left'
                    },
                    {type: 'button', name: 'btn', value: 'Войти', width: 270, offsetTop: 25},
                    {
                        type: 'label', name: 'invalid-login-message',
                        label: '', className: 'invalid-login-message'
                    }

                ]
            }
        ]);

        function loginFailed(response) {
            loginWin.progressOff();
            var message = _.get(response, 'response.data.message');
            loginForm.setItemLabel('invalid-login-message', message || '');
        }

        function loginResolve(response) {
            loginWin.progressOff();
            Authorization.storeToken(response.data.token);
            Authorization.storeRefreshToken(response.data.refreshToken);
            successCallback();
        }

        loginForm.setItemFocus('username');

        loginForm.attachEvent('onButtonClick', function () {
            var userName = loginForm.getItemValue('username');
            var password = loginForm.getItemValue('password');
            if (!_.isEmpty(userName) && !_.isEmpty(password)) {
                loginWin.progressOn();
                requestAuthorization(userName, password)
                    .then(loginResolve)
                    .catch(loginFailed);
            }
        });

        loginForm.attachEvent('onKeyUp', function (inp, event) {
            if (event.keyCode === 13) {
                var userName = loginForm.getItemValue('username');
                var password = loginForm.getItemValue('password');
                if (!_.isEmpty(userName) && !_.isEmpty(password)) {
                    requestAuthorization(userName, password)
                        .then(loginResolve)
                        .catch(loginFailed);
                }
            }
        });
        return loginForm;
    }

    function openLoginForm() {
        return new Promise(function (resolve, reject) {
            if (!winOpened) {
                var win = openLoginWindow();
                initLoginForm(win, function () {
                    win.close();
                    winOpened = false;
                    resolve();
                });
            }
        });
    }

    function close() {

    }

    return {
        openLoginForm: openLoginForm
    };
});
