<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 9/13/14
 * Time: 10:04 PM
 */

class Select extends Tag{

    public function Select() {
        return $this->Tag();
    }

    function getTagName() {
        return "select";
    }
}