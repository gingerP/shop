<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/20/14
 * Time: 10:40 PM
 */

class Button extends Tag{

    public function Button() {
        return $this->Tag();
    }

    public function getTagName() {
        return "button";
    }
}