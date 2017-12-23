<?php

include_once('src/back/import/admin_pages');

class AdminPage_Contacts extends AdminPagesCreator{
    public function AdminPage_Contacts() {}

    protected function getHeadContent() {
        return [
            "<title>Контакты - консоль augustova.by</title>",
            "<script type='text/javascript' src='/src/front/js/ext/jquery-2.1.4.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/dhtmlx.js'></script>
            <script type='text/javascript' src='/src/front/js/utils.js'></script>
            <script type='text/javascript' src='/src/front/js/admin/components.js'></script>
            <link rel='stylesheet' type='text/css' href='/src/front/dhtmlx/dhtmlx.css'>",
            "<script type='text/javascript' src='/src/front/js/admin/contacts.js'></script>"
        ];
    }

    protected function getGeneralContent() {
        return [];
    }

}