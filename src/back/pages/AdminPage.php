<?php
include_once("import");
include_once("tag");

class AdminPage {

    public function AdminPage() {
        /*if (AuthManager::isCurrentUserAuthenticate()) {*/
        if (1) {
            $mainTag = new Html();
            $head = new Head();
            $head->addChild("
            <title>Admin page</title>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
            <link rel='shortcut icon' href='images/system/favicon.ico' type='image/x-icon'/>
            <link rel='stylesheet' type='text/css' href='/src/front/style/style-less.css'/>
            <script type='text/javascript' src='/src/front/js/fixies.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/jquery.js'></script>
            <script type='text/javascript' src='/src/front/js/v-utils.js'></script>
            <script type='text/javascript' src='/src/front/js/admin.js'></script>
            ");

            $body = new Body();
            $body->addChild($this->getPreparedDom());
            echo TagUtils::buildHtml($mainTag->addChildList([$head, $body]), new Num(0));
        } else {
            $mainTag = $this->getPreAuthPage();
            echo TagUtils::buildHtml($mainTag, new Num(0));
        }
    }


    private function getPreAuthPage() {
        $mainTag = new Html();
        $head = new Head();
        $head->addChild("
            <title>Admin page</title>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
            <link rel='shortcut icon' href='images/system/favicon.ico' type='image/x-icon'/>
            <link rel='stylesheet' type='text/css' href='/src/front/style/style-less.css'/>
            <script type='text/javascript' src='/src/front/js/fixies.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/jquery.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/jquery-1.10.2.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/tinycolor.js'></script>
            <script type='text/javascript' src='/src/front/js/v-utils.js'></script>
            <script type='text/javascript' src='/src/front/js/utils.js'></script>
            <script type='text/javascript' src='/src/front/js/admin.js'></script>
            <script type='text/javascript' src='/src/front/js/components/vCore-effects.js'></script>
            ");

        $body = new Body();
        $body->addChild("
            <div class='admin_auth_container' >
                <div class='auth_header f-20'>Авторизация</div>
                <label class='f-15' for='user'>User</label>
                <input id='user'>
                <label class='f-15' for='password'>Password</label>
                <input id='password' type='password'>
                <button class='button f-20 input_hover'>Войти</button>
            </div>
            <script type='text/javascript'>
                /*inputHoverModule.update();*/
            </script>
        ");
        return $mainTag->addChildList([$head, $body]);
    }

    private function getPreparedDom() {
        return "
            <div id='container'>
                <div id='editor_container'>
                    <div class='header'>
                        <span>Редактор</span>
                        <button id='save'>сохранить</button>
                        <button id='add'>добавить</button>
                        <button id='clear'>очистить</button>
                        <span id='message'></span>
                    </div>
                    <div>
                        <div style='float: left;'>
                            <div for='id'>id:</div>
                            <input id='id' readonly>
                        </div>
                        <div style='float: left;'>
                            <div>key_item:</div>
                            <textarea id='key_item'></textarea>
                        </div>
                        <div style='float: left;'>
                            <div>name:</div>
                            <textarea id='name' style='width: 550px;'></textarea>
                        </div>
                        <label for='person'>person:</label>
                        <select id='person'>
                            <option value='YES'>YES</option>
                            <option value='NO'>NO</option>
                        </select>
                        <label for='individual'>individual:</label>
                        <select id='individual'>
                            <option value='YES'>YES</option>
                            <option value='NO'>NO</option>
                        </select>
                        <div style='float: left;'>
                            <div for='image_path'>image_path:</div>
                            <textarea id='image_path'></textarea>
                        </div>
                        <label for='god_type'>god_type:</label>
                        <select id='god_type'>
                            <option value='HARD'>HARD</option>
                            <option value='SIMPLE'>SIMPLE</option>
                        </select>
                        <br>
                        <div id='descriptions' style='display: inline-block;'>
                        </div>

                    </div>
                </div>
                <div id='table_container'>
                    <div class='header'>Таблица значений</div>
                    <div>
                        <table style='cursor: pointer;'>
                            <tbody style='display: block; overflow: auto; height: 640px;'></tbody>
                        </table>
                    </div>
                </div>
            </div>
        ";
    }

} 