<?php

class A extends Tag{

    public function __construct() {
        return parent::__construct();
    }

    public function getTagName() {
        return "a";
    }
} 