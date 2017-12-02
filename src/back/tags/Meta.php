<?php

class Meta extends Tag{

    public function Meta() {
        return $this->Tag();
    }

    public function getTagName() {
        return "meta";
    }
} 