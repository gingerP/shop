<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/20/14
 * Time: 10:40 PM
 */

class Head extends Tag{

    public function Head() {
        return $this->Tag();
    }

    public function getTagName() {
        return "head";
    }
}