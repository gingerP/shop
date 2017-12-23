<?php

class AdminPage_Login extends AdminPagesCreator {

    public function AdminPage_Login() {}

    protected function getHeadContent() {
        return [
            "<title>Авторизация - консоль augustova.by</title>",
            "<link rel='stylesheet' type='text/css' href='/src/front/style/style-less.css' title='main'/>",
            "<style>
                .input_hover:hover {
                    background: rgb(20, 138, 115);
                }
                .admin_auth_container {
                    position: absolute;
                    width: 300px;
                    height: 220px;
                    padding-top: 20px;
                }
                .button {
                    height: 40px !important;
                    margin: 10px auto !important;
                }
            </style>"
        ];
    }

    protected function getGeneralContent() {
        return ['
            <form class="admin_auth_container" method="POST" action="/admin/login">
                <label class="f-15" for="user" >Пользователь</label>
                <input name="'.Constants::LOGIN_USER.'" id="user">
                <label class="f-15" for="password">Пароль</label>
                <input name="'.Constants::LOGIN_PASSWORD.'" id="password" type="password">
                <input type="submit" class="button f-15 input_hover"></button>
            </form>
        '];
    }
} 