<?php

include_once AuWebRoot . '/src/back/views/admin/AdminPagesCreator.php';

class AdminPageCategories extends AdminPagesCreator {

    private $content;

    protected function getHeadContent() {
        return ["
            <title>Категории - консоль augustova.by</title>
            <link rel='stylesheet' type='text/css' href='/src/front/dhtmlx/dhtmlx.css'>
            <script type='text/javascript' src='/src/front/js/ext/jquery-2.1.4.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/dhtmlx.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/require.js'></script>
            <script type='text/javascript' src='/src/front/js/utils.js'></script>
            <script type='text/javascript' src='/src/front/js/admin/require.config.js'></script>
            <script type='text/javascript' src='/src/front/js/admin/categories/index.js'></script>
            "
        ];
    }

    protected function getGeneralContent() {
        return [];
    }

    public function build() {
        $this->content = $this->getHtml();
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

}