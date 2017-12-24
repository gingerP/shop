<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 9/13/14
 * Time: 10:05 PM
 */

class Option extends Tag{

    public function __construct() {
        return parent::__construct();
    }


    function getTagName() {
        return "option";
    }
}