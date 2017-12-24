<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/22/14
 * Time: 9:06 AM
 */

class Span extends Tag{

    public function __construct() {
        return parent::__construct();
    }

    function getTagName() {
        return "span";
    }
}