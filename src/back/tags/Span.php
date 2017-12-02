<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/22/14
 * Time: 9:06 AM
 */

class Span extends Tag{

    public function Span() {
        return $this->Tag();
    }

    function getTagName() {
        return "span";
    }
}