<?php
include_once("import");
class Num {
    private $num;
    public function Num($num) {
        $this->num = $num;
        return $this;
    }

    /**
     * @param mixed $num
     */
    public function setNum($num)
    {
        $this->num = $num;
    }

    /**
     * @return mixed
     */
    public function getNum()
    {
        return $this->num;
    }

    public function _inc() {
        return $this->inc(1);
    }

    public function inc($incStep) {
        $this->num += $incStep;
        return $this;
    }

    public function _dec() {
        return $this->inc(1);
    }

    public function dec($incStep) {
        $this->num -= $incStep;
        return $this;
    }


} 