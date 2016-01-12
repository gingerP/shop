<?php

class ItemB {
    public $key_item = "";
    public $name = "";
    public $description = "";
    public $imagePath = "";

    public function ItemB($key_item_, $name_, $description_, $image_path_) {
        $this->key_item = $key_item_;
        $this->name = $name_;
        $this->description = $description_;
        $this->imagePath = $image_path_;
    }

}
