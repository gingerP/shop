<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/26/14
 * Time: 11:35 PM
 */

class Img extends Tag{

    protected $closable = false;

    public function Img() {
        return $this->Tag();
    }

    public function getTagName() {
        return "img";
    }
} 