<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/20/14
 * Time: 10:40 PM
 */

class Body extends Tag{

    public function __construct() {
        return parent::__construct();
    }

    public function getTagName() {
        return "body";
    }
}