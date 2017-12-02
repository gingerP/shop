<?php

include_once('admin_pages');
class AdminPage_Prices extends AdminPagesCreator{
    public function AdminPage_Prices() {}

    protected function getHeadContent() {
        return [
            "<title>Прайс листы - консоль augustova.by</title>",
            "<script type='text/javascript' src='/src/front/js/ext/jquery-2.1.4.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/dhtmlx.js'></script>
            <script type='text/javascript' src='/src/front/js/utils.js'></script>
            <script type='text/javascript' src='/src/front/js/admin/components.js'></script>
            <link rel='stylesheet' type='text/css' href='/src/front/dhtmlx/dhtmlx.css'>",
            "<script type='text/javascript' src='/src/front/js/admin/prices.js'></script>",
            "<script type='text/javascript' src='/src/front/js/ext/moment.min.js'></script>"
        ];
    }

    protected function getGeneralContent() {
        return ["<input id='load_price' style='display: none;' type='file'/>"];
    }

}