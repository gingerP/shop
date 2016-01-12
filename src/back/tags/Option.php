<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 9/13/14
 * Time: 10:05 PM
 */

class Option extends Tag{

    public function Option() {
        return $this->Tag();
    }


    function getTagName() {
        return "option";
    }
}