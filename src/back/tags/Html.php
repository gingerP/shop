<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/20/14
 * Time: 10:37 PM
 */

class Html extends Tag{

    public function Html() {
        return $this->Tag();
    }

    function getTagName() {
        return 'html';
    }
}