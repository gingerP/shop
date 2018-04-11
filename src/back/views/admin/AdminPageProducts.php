<?php

include_once('src/back/import/admin_pages');

class AdminPageProducts extends AdminPagesCreator {

    protected function getHeadContent() {
        return ["
            <title>Товары - консоль augustova.by</title>
            <link rel='stylesheet' type='text/css' href='/src/front/dhtmlx/dhtmlx.css'>
            <script type='text/javascript' src='/src/front/js/ext/jquery-2.1.4.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/dhtmlx.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/require.js'></script>
            <script type='text/javascript' src='/src/front/js/utils.js'></script>
            <script type='text/javascript' src='/src/front/js/admin/require.config.js'></script>
            <script type='text/javascript' src='/src/front/js/admin/products/index.js'></script>
            "
        ];
    }

    protected function getGeneralContent() {
        return [];
    }

}