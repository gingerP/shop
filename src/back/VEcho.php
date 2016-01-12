<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/4/14
 * Time: 12:16 PM
 * @property mixed echoValue
 */

class VEcho
{
    private $echoValues = array();

    public function concat($echoKey, $echoValue)
    {
        $this->echoValues[$echoKey] .= $this->echoValue;
    }

    public function getEchoValue($key) {
        return $this->echoValues[$key];
    }

    public function _echo()
    {
        echo $this->echoValue;
    }
}