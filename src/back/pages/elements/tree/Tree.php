<?php
include_once("src/back/import/import");

class Tree extends Object{
    public $parentKey;
    public $key;
    public $value;
    public $homeViewMode;
    public $childrens = array();
    public $show = '';

    public function Tree($parentKey, $key, $value, $show, $homeViewMode) {
        $this->parentKey = $parentKey;
        $this->key = $key;
        $this->value = $value;
        $this->show = $show;
        $this->homeViewMode = $homeViewMode;
    }

    public function toString() {
        log::temp("{Tree: {"
            ."\n\t   parentKey: ".$this->parentKey
            ."\n\t         key: ".$this->key
            ."\n\t       value: ".$this->value
            ."\n\t        show: ".$this->show
            ."\n\thomeViewMode: ".$this->homeViewMode
            ."\n\t child count: ".count($this->childrens)
        ."}}");
    }
}
