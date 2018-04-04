<?php
include_once("src/back/import/import");
include_once("src/back/import/tag");

class CatalogLink {

    public function getLink($pageNumber, $num) {
        $mainTag = new A();
        $mainTag->addStyleClasses(["link_style", "cursor_pointer", "text_non_select", "f-15", "input_hover", "pagination-item"]);
        $mainTag->addAttribute("href", URLBuilder::getCatalogLink($pageNumber, $num));
        $mainTag->addChild($pageNumber);
        return $mainTag;
    }

    public function getLinkForCategory($category, $pageNumber, $num) {
        $mainTag = new A();
        $mainTag->addStyleClasses(["link_style", "cursor_pointer", "text_non_select", "f-15", "input_hover", "pagination-item"]);
        $mainTag->addAttribute("href", URLBuilder::getCatalogLinkForCategory($category, $pageNumber, $num));
        $mainTag->addChild($pageNumber);
        return $mainTag;
    }

    public function getLinkForSearch($searchValue, $pageNumber, $num) {
        $mainTag = new A();
        $mainTag->addStyleClasses(["link_style", "cursor_pointer", "text_non_select", "f-15", "input_hover", "pagination-item"]);
        $mainTag->addAttribute("href", URLBuilder::getCatalogLinkForSearch($searchValue, $pageNumber, $num));
        $mainTag->addChild($pageNumber);
        return $mainTag;
    }

    public function getEmptyLink($pageNumber) {
        $mainTag = new Div();
        $mainTag->addStyleClasses(["empty_link_style", "text_non_select", "pagination-item"]);
        $mainTag->addChild($pageNumber);
        return $mainTag;
    }

    public function get3Dots () {
        $mainTag = new Div();
        $mainTag->addStyleClasses(["three_dots_style", "f-15", "pagination-item"]);
        $mainTag->addChild("...");
        return $mainTag;
    }
}
