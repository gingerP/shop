<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/18/14
 * Time: 11:30 PM
 */

class Ul extends Tag{

    public function Ul() {
        return $this->Tag();
    }

    function getTagName() {
        return "ul";
    }
}