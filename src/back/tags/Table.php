<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 8/29/14
 * Time: 5:15 PM
 */

class Table extends Tag {

    public function Table() {
        return $this->Tag();
    }

    function getTagName() {
        return "table";
    }
}