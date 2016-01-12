<?php
include_once("db");
include_once("page");

class SearchPage extends APagesCreator {
    private $search_value = "";

    public function SearchPage() {
        $this->APagesCreator();
        $this->setPageCode("search_page");
        $this->setIsStatusBarVisible(true);
        $this->setIsViewModeBlockVisible(true);
        $this->setIsTreeVisible(true);
        $this->setPathLinkForTree(PathLinks::getDOMForTree());
        $this->setViewModeBlock(PathLinks::getDOMForViewModeSelector());

        $html = $this->getHtml();
        echo $html;
    }

    protected function createGeneralContent() {
        $catalogDOM = CatalogPage::getCatalogDOM();
        if ($catalogDOM == "") {
            $valueToSearch = Utils::getFromGET(UrlParameters::SEARCH_VALUE);
            return $this->createGeneralContentForEmptyResult($valueToSearch);
        }
        return $catalogDOM;
    }

    private function createGeneralContentForEmptyResult($valueToSearch) {
        $mainTag = new Div();
        $mainTag->addStyleClass("empty_search_result");
        $emptySearchResultLabel = new Div();
        $emptySearchResultLabel->addStyleClasses(["empty_search_result_label", "f-16"]);
        $mainTag->addChild($emptySearchResultLabel->addChild(Labels::prefillMessage([$valueToSearch], Labels::EMPTY_SEARCH_RESULT)));
        TagUtils::createShadow($mainTag);
        return $mainTag;
    }

}
