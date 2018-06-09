<?php

include_once AuWebRoot . '/src/back/views/admin/AdminPagesCreator.php';

class AdminPageBooklets extends AdminPagesCreator {

    private $content;

    protected function getHeadContent() {
        return [
            "<title>Буклеты - консоль augustova.by</title>
            <script type='text/javascript' src='/src/front/js/ext/jquery-2.1.4.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/jquery-ui.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/CSSPlugin.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/Draggable.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/TweenLite.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/dhtmlx.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/handlebars-v3.0.3.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/require.js'></script>
            <script type='text/javascript' src='/src/front/js/utils.js'></script>
            <link rel='stylesheet' type='text/css' href='/src/front/dhtmlx/dhtmlx.css'>
            <script type='text/javascript' src='/src/front/js/admin/require.config.js'></script>
            <script type='text/javascript' src='/src/front/js/admin/booklets/index.js'></script>"
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