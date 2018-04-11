<?php

class Option extends Tag{

    public function __construct() {
        return parent::__construct();
    }


    function getTagName() {
        return "option";
    }
}