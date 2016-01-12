<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/23/14
 * Time: 1:01 AM
 */

class Polygon extends Tag{

    public function Polygon() {
        return $this->Tag();
    }

    function getTagName()
    {
        return "polygon";
    }
}