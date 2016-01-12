<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/26/14
 * Time: 11:35 PM
 */

class Script extends Tag{

    public function Script() {
        return $this->Tag();
    }

    public function getTagName() {
        return "script";
    }
} 