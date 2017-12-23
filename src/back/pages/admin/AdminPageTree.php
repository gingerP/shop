<?php

include_once('src/back/import/admin_pages');

class AdminPageTree extends AdminPagesCreator {

    protected function getHeadContent() {
        return [
            "<title>Дерево товаров - консоль augustova.by</title>",
            "<script type='text/javascript' src='/src/front/js/ext/jquery-2.1.4.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/dhtmlx.js'></script>
            <script type='text/javascript' src='/src/front/js/utils.js'></script>
            <script type='text/javascript' src='/src/front/js/admin/components.js'></script>
            <link rel='stylesheet' type='text/css' href='/src/front/dhtmlx/dhtmlx.css'>",
            "<script type='text/javascript' src='/src/front/js/admin/tree.js'></script>"
        ];
    }

    protected function getGeneralContent() {
        return [];
    }

}