<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/18/14
 * Time: 11:30 PM
 */

class Ul extends Tag{

    public function __construct() {
        return parent::__construct();
    }

    function getTagName() {
        return "ul";
    }
}