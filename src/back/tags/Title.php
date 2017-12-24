<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/26/14
 * Time: 11:35 PM
 */

class Title extends Tag{

    public function __construct() {
        return parent::__construct();
    }

    public function getTagName() {
        return "title";
    }
} 