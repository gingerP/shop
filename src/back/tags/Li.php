<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/18/14
 * Time: 11:31 PM
 */

class Li extends Tag {

    public function __construct() {
        return parent::__construct();
    }

    public function getTagName() {
        return "li";
    }
}