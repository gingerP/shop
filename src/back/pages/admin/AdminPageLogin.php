<?php

include_once('src/back/import/tag');

class AdminPageLogin extends AdminPagesCreator
{

    private $extendData;

    function __construct($extendData = [])
    {
        $this->extendData = $extendData;
    }

    protected function getHeadContent()
    {
        return [
            "<title>Авторизация - консоль augustova.by</title>",
        ];
    }

    protected function getGeneralContent()
    {
        $main = new Div();

        $main->addStyleClass('login-page-container');

        $background = new Img();
        $background->addStyleClass('login-page-background');
        $background->addAttribute('src', '/images/wallpaper.jpg');

        $formContainer = new Div();
        $formContainer->addStyleClass('login-form-container');

        $loginFailed = '';
        if (array_key_exists('login_failed', $this->extendData) && $this->extendData['login_failed'] === true) {
            $loginFailed = '<div class="login-failed-label">Неправильные пользователь или пароль.</div>';
        }
        $form = '
            <div class="admin_auth_container">
                <form class="" method="POST" action="/admin/login">
                    <label class="f-15" for="user" >Пользователь</label>
                    <input name="' . Constants::LOGIN_USER . '" id="user" type="text" autofocus >
                    <label class="f-15" for="password">Пароль</label>
                    <input name="' . Constants::LOGIN_PASSWORD . '" id="password" type="password">
                    <button type="submit" class="f-15 input_hover">Войти</button>
                </form>
            </div>
        ';
        return [$main->addChildren($background, $formContainer->addChildren($form, $loginFailed))];
    }
} 