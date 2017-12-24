<?php

class Meta extends Tag{

    protected $closable = false;

    public function __construct() {
        return parent::__construct();
    }

    public function getTagName() {
        return "meta";
    }
} 