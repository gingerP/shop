<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/29/14
 * Time: 5:16 PM
 */

class Tr extends Tag {

    public function __construct() {
        return parent::__construct();
    }

    function getTagName() {
        return "tr";
    }
}