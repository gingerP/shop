<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/29/14
 * Time: 5:17 PM
 */

class Td extends Tag {

    public function Td() {
        return $this->Tag();
    }

    function getTagName() {
        return "td";
    }
}