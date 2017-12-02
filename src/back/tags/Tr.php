<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/29/14
 * Time: 5:16 PM
 */

class Tr extends Tag {

    public function Tr() {
        return $this->Tag();
    }

    function getTagName() {
        return "tr";
    }
}