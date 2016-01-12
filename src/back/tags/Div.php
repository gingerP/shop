<?php
include_once("tag");

class Div extends Tag{
    const TAG_NAME = "div";

    public function Div() {
        return $this->Tag();
    }

    public function getTagName()
    {
        return Div::TAG_NAME;
    }
}