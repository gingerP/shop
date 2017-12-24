<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/20/14
 * Time: 10:37 PM
 */

class Html extends Tag{

    public function __construct() {
        return parent::__construct();
    }

    function getTagName() {
        return 'html';
    }
}