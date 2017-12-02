<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vinni
 * Date: 08.09.13
 * Time: 18:19
 * To change this template use File | Settings | File Templates.
 */

include_once("src/back/import/import");

class StoreMode {

    public function StoreMode() {

    }

    public function render() {
        echo "<div class='store_mode_container'>";
        self::renderCheckBoxes();
        echo "</div>";
    }

    private function renderCheckBoxes() {
        $checkFiz = 'chekbox_check';
        $onclickFiz = "onclick=\"window.location='".URLBuilder::storeModeFiz(YesNoType::YES)."'\"";
        $checkUr = 'chekbox_check';
        $onclickUr = "onclick=\"window.location='".URLBuilder::storeModeUr(YesNoType::YES)."'\"";
        if (!array_key_exists(Labels::CHECK_UR, $_GET) && array_key_exists(Labels::CHECK_FIZ, $_GET)) {
            $onclickFiz = '';
            $checkUr = '';
        } elseif (array_key_exists(Labels::CHECK_UR, $_GET) && !array_key_exists(Labels::CHECK_FIZ, $_GET)) {
            $onclickUr = '';
            $checkFiz = '';
        } elseif (array_key_exists(Labels::CHECK_UR, $_GET) && array_key_exists(Labels::CHECK_FIZ, $_GET)) {
            $onclickFiz = "onclick=\"window.location='".URLBuilder::storeModeFiz(YesNoType::NO)."'\"";
            $onclickUr = "onclick=\"window.location='".URLBuilder::storeModeUr(YesNoType::NO)."'\"";
        }
        echo "
            <div class='float_left chekbox_control font_arial'>
                <div id='".Labels::CHECK_UR."' class='chekbox float_left cursor_pointer ".$checkUr."' ".$onclickUr."></div>
                <div class='chekbox_label'>".Labels::STORE_MODE_UR."</div>
            </div>
            <div class='float_left chekbox_control font_arial'>
                <div id='".Labels::CHECK_FIZ."' class='chekbox float_left cursor_pointer ".$checkFiz."' ".$onclickFiz."></div>
                <div class='chekbox_label'>".Labels::STORE_MODE_FIZ."</div>
            </div>
        ";
    }
}