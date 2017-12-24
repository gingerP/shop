<?php
include_once("src/back/import/tag");

class Div extends Tag{
    const TAG_NAME = "div";

    public function __construct() {
        return parent::__construct();
    }

    public function getTagName()
    {
        return Div::TAG_NAME;
    }
}