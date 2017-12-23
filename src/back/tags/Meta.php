<?php

class Meta extends Tag{

    protected $closable = false;

    public function Meta() {
        return $this->Tag();
    }

    public function getTagName() {
        return "meta";
    }
} 