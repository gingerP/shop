<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/20/14
 * Time: 10:37 PM
 */

class Html extends Tag{

    public function __construct($lang) {
        $tag = parent::__construct();
        $tag->addAttribute('lang', $lang);
        $tag->addAttribute('dir', 'ltr');
        return $this;
    }

    function getTagName() {
        return 'html';
    }
}