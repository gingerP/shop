<?php

include_once("src/back/import/db");

class AdminPage_Booklet extends AdminPagesCreator {

    public function AdminPage_Booklet() {}

    protected function getHeadContent() {
        return [
            "<title>Буклеты - консоль augustova.by</title>",
            "<script type='text/javascript' src='/src/front/js/ext/jquery-2.1.4.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/jquery-ui.min.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/CSSPlugin.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/Draggable.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/TweenLite.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/dhtmlx.js'></script>
            <script type='text/javascript' src='/src/front/js/utils.js'></script>
            <script type='text/javascript' src='/src/front/js/admin/components.js'></script>
            <script type='text/javascript' src='/src/front/js/ext/handlebars-v3.0.3.min.js'></script>
            <link rel='stylesheet' type='text/css' href='/src/front/dhtmlx/dhtmlx.css'>",
            "<link rel='stylesheet' type='text/css' href='/src/front/style/booklet.css'>",
            "<script type='text/javascript' src='/src/front/js/admin/booklet.js'></script>",
            "<script type='text/javascript'>
                app = app || {};
                app.bookletImageRoot = '".DBPreferencesType::getPreferenceValue(Constants::BOOKLET_IMAGE_PATH)."';
                app.bookletBackgroundImageRoot = '".DBPreferencesType::getPreferenceValue(Constants::BOOKLET_BACKGROUND_IMAGES_PATH)."';
            </script>"
        ];
    }

    protected function getGeneralContent() {
        return [];
    }

} 