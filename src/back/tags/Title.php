<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/26/14
 * Time: 11:35 PM
 */

class Title extends Tag{

    public function Title() {
        return $this->Tag();
    }

    public function getTagName() {
        return "title";
    }
} 