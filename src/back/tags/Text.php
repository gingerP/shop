<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 11/11/14
 * Time: 12:37 AM
 */

class Text extends Tag{

    public function __construct() {
        return parent::__construct();
    }

    function getTagName() {
        return "text";
    }
}