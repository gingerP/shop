<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 1/29/15
 * Time: 12:03 AM
 */

class Br extends SingleTag{

    public function Br() {
        return $this->Tag();
    }

    public function getTagName() {
        return "br";
    }

} 