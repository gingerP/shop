<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/23/14
 * Time: 1:22 AM
 */

class Svg extends Tag{

    public function __construct() {
        return parent::__construct();
    }

    function getTagName()
    {
        return "svg";
    }
}