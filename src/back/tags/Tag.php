<?php

include_once("src/back/import/import");

abstract class Tag
{

    protected $id = "";
    protected $classList = array();
    protected $attributeList = array();
    protected $childList = array();

    protected function Tag() {
        return $this;
    }

    abstract function getTagName();

    public function updateId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function addStyleClass($class)
    {
        if (!in_array($class, $this->classList)) {
            array_push($this->classList, $class);
        }
        return $this;
    }

    public function addStyleClasses($classes)
    {
        if (is_array($classes)) {
            for($classIndex = 0; $classIndex < count($classes); $classIndex++) {
                if (!in_array($classes[$classIndex], $this->classList)) {
                    array_push($this->classList, $classes[$classIndex]);
                }

            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getClassList()
    {
        return $this->classList;
    }



    public function addAttribute($attributeKey, $attributeValue) {
        $this->attributeList[$attributeKey] = $attributeValue;
        return $this;
    }

    public function addAttributes($keyValueAttributes) {
        if (is_array($keyValueAttributes)) {
            foreach($keyValueAttributes as $key => $value) {
                $this->attributeList[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributeList()
    {
        return $this->attributeList;
    }

    public function addChild($child) {
        if ($child != null && ($child instanceof Tag || is_string($child) || is_numeric($child))) {
            $this->childList[] = $child;
        }
        return $this;
    }

    public function addChildren() {
        $childCount = func_num_args();
        for($childIndex = 0; $childIndex < $childCount ; $childIndex++) {
            $child = func_get_arg($childIndex);
            if (is_array($child)) {
                for($childIndexInner = 0; $childIndexInner < count($child) ; $childIndexInner++) {
                    $item = $child[$childIndexInner];
                    if (is_array($item)) {
                        $this->childList = array_merge($this->childList, $item);
                    } else if ($item instanceof Tag || is_string($item) || is_numeric($item)) {
                        array_push($this->childList, $item);
                    }
                }
            } else {
                if ($child instanceof Tag || is_string($child) || is_numeric($child)) {
                    array_push($this->childList, $child);
                }
            }
        }
        return $this;
    }

    public function addChildList($children) {
        if (is_array($children)) {
            for($childIndex = 0; $childIndex < count($children); $childIndex++) {
                if ($children[$childIndex] != null) {
                    array_push($this->childList, $children[$childIndex]);
                }
            }
        }
        return $this;
    }

    public function prependChildren() {
        $childCount = func_num_args();
        for($childIndex = $childCount - 1; $childIndex >= 0 ; $childIndex--) {
            $child = func_get_arg($childIndex);
            if (is_array($child)) {
                $childCountInner = count($child);
                for($childIndexInner = $childCountInner - 1; $childIndexInner >= 0 ; $childIndexInner--) {
                    array_unshift($this->childList, $child[$childIndexInner]);
                }
            } else {
                if ($child instanceof Tag || is_string($child) || is_numeric($child)) {
                    array_unshift($this->childList, $child);
                }
            }
        }
    }

    public function replaceChildren($children) {
        if (is_array($children)) {
            $this->childList = [];
            for($childIndex = 0; $childIndex < count($children); $childIndex++) {
                if ($children[$childIndex] != null) {
                    array_push($this->childList, $children[$childIndex]);
                }
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getChildList()
    {
        return $this->childList;
    }


    public function getHtml() {
        return TagUtils::buildHtml($this, new Num(0));
    }
}