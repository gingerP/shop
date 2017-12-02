<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/26/14
 * Time: 11:35 PM
 */

class Strong extends Tag{

    public function Strong() {
        return $this->Tag();
    }

    public function getTagName() {
        return "strong";
    }
} 