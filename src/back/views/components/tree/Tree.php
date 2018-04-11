<?php
include_once AuWebRoot.'/src/back/import/import.php';

class Tree
{
    public $parentKey;
    public $key;
    public $value;
    public $homeViewMode;
    public $childrens = array();
    public $show = '';

    public function Tree($parentKey, $key, $value, $show, $homeViewMode)
    {
        $this->parentKey = $parentKey;
        $this->key = $key;
        $this->value = $value;
        $this->show = $show;
        $this->homeViewMode = $homeViewMode;
    }
}