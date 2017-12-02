<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/26/14
 * Time: 11:35 PM
 */

class Input extends Tag{

    public function Input() {
        return $this->Tag();
    }

    public function getTagName() {
        return "input";
    }
} 