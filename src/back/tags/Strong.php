<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/26/14
 * Time: 11:35 PM
 */

class Strong extends Tag{

    public function __construct() {
        return parent::__construct();
    }

    public function getTagName() {
        return "strong";
    }
} 