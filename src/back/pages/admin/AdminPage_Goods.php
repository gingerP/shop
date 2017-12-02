<?php

include_once('admin_pages');
class AdminPage_Goods extends AdminPagesCreator{
    public function AdminPage_Goods() {}

    protected function getHeadContent() {
        return [
            "<title>Товары - консоль augustova.by</title>",
            "<script type='text/javascript' src='/src/front/js/ext/jquery-2.1.4.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/dhtmlx.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/require.js'></script>
            <script type='text/javascript' src='/src/front/js/utils.js'></script>
            <script type='text/javascript' src='/src/front/js/admin/components.js'></script>
            <link rel='stylesheet' type='text/css' href='/src/front/dhtmlx/dhtmlx.css'>",
            "<script type='text/javascript' src='/src/front/js/admin/goods.js'></script>",
        ];
    }

    protected function getGeneralContent() {
        return [];
    }

}