<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/26/14
 * Time: 11:35 PM
 */

class A extends Tag{

    public function A() {
        return $this->Tag();
    }

    public function getTagName() {
        return "a";
    }
} 