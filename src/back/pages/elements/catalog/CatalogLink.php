<?php
include_once("import");
include_once("tag");

class CatalogLink {

    public function getLink($pageNumber, $num) {
        $mainTag = new A();
        $mainTag->addStyleClasses(["link_style", "cursor_pointer", "text_non_select", "f-15", "input_hover"]);
        $mainTag->addAttribute("href", URLBuilder::getCatalogLinkNumeric($pageNumber, $num));
        $mainTag->addChild($pageNumber);
        return $mainTag;
    }

    public function getEmptyLink($pageNumber) {
        $mainTag = new Div();
        $mainTag->addStyleClasses(["empty_link_style", "text_non_select"]);
        $mainTag->addChild($pageNumber);
        return $mainTag;
    }

    public function get3Dots () {
        $mainTag = new Div();
        $mainTag->addStyleClasses(["three_dots_style", "f-15"]);
        $mainTag->addChild("...");
        return $mainTag;
    }
}
