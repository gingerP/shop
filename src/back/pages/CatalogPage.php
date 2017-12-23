<?php
include_once("src/back/import/import");
include_once("src/back/import/page");
include_once("src/back/import/db");

class CatalogPage extends APagesCreator{

    private $key = "";

    public function __construct() {
        parent::__construct();
        $this->updateTitleTagChildren(["Каталог - "]);
        $this->setPageCode("catalog_page");
        $this->setIsTreeVisible(true);
        $this->setIsStatusBarVisible(true);
        $this->setIsViewModeBlockVisible(true);
        $this->setPathLinkForTree(PathLinks::getDOMForTree());
        if (array_key_exists(UrlParameters::KEY, $_GET)) {
            $this->key = Utils::getFromGET(UrlParameters::KEY);
            $this->setTreeKey($this->key);
            $this->setPathLinkForMainBlock(PathLinks::getDOMForTreeCatalog($this->key, $this));
        } else {
            $this->setPathLinkForMainBlock(PathLinks::getDOMForCatalog());
        }
        $this->setViewModeBlock(PathLinks::getDOMForViewModeSelector());

        $metaDesc = new Meta();
        $metaDesc->addAttributes([
            "name" => "description",
            "content" => "в каталоге augustova.by вы  найдете спецодежду для вашей работы"
        ]);
        $treeView = new TreeView();
        $treeLabels = implode(", ", $treeView->getAllLabels());
        $metaKeyWords = new Meta();
        $metaKeyWords->addAttributes([
            "name" => "keywords",
            "content" => $treeLabels
        ]);

        $this->addMetaTags($metaDesc, $metaKeyWords);

        $this->content = $this->getHtml();
    }

    protected function createGeneralContent() {
        $mainTag = new Div();
        $items = new Items();
        $catalogLinks = new CatalogLinks();
        if (array_key_exists(UrlParameters::PAGE_NAME, $_GET)) {
            $pageNumber = Constants::DEFAULT_PAGE_NUMBER;
            $itemsCount = Labels::VIEW_MODE_NUMERIC_DEF;
            if (array_key_exists(UrlParameters::PAGE_NUM, $_GET)) {
                $pageNumber = Utils::getFromGET(UrlParameters::PAGE_NUM);
            }
            if (array_key_exists(UrlParameters::ITEMS_COUNT, $_GET)) {
                $itemsCount = Utils::getFromGET(UrlParameters::ITEMS_COUNT);
            }
            if (array_key_exists(UrlParameters::SEARCH_VALUE, $_GET)) {
                $itemsType = new CatalogLoader();
                $searchValue = Utils::getFromGET(UrlParameters::SEARCH_VALUE);
                $itemsType->getItemSearchData($pageNumber, $itemsCount, $searchValue);
                if($itemsType->dataTotalCount == 0) return "";
                $mainTag->addChild($catalogLinks->getPaginationLinks($pageNumber, $itemsCount, $itemsType->dataTotalCount, 'top'));
                $mainTag->addChild($items->getItemsTable($pageNumber, $itemsCount, $itemsType->data, '', $searchValue));
                $mainTag->addChild($catalogLinks->getPaginationLinks($pageNumber, $itemsCount, $itemsType->dataTotalCount, 'bottom'));
            } else if (array_key_exists(UrlParameters::KEY, $_GET)) {
                $itemsType = new CatalogLoader();
                $keyValue = Utils::getFromGET(UrlParameters::KEY);
                $itemsType->getItemsMenuData($pageNumber, $itemsCount, $keyValue);
                $mainTag->addChild($catalogLinks->getPaginationLinks($pageNumber, $itemsCount, $itemsType->dataTotalCount, 'top'));
                $mainTag->addChild($items->getItemsTable($pageNumber, $itemsCount, $itemsType->data, $keyValue, ''));
                $mainTag->addChild($catalogLinks->getPaginationLinks($pageNumber, $itemsCount, $itemsType->dataTotalCount, 'bottom'));
            } else {
                $itemsType = new CatalogLoader();
                $itemsType->getItemsMainData($pageNumber, $itemsCount);
                $mainTag->addChild($catalogLinks->getPaginationLinks($pageNumber, $itemsCount, $itemsType->dataTotalCount, 'top'));
                $mainTag->addChild($items->getItemsTable($pageNumber, $itemsCount, $itemsType->data, '', ''));
                $mainTag->addChild($catalogLinks->getPaginationLinks($pageNumber, $itemsCount, $itemsType->dataTotalCount, 'bottom'));
            }
        }
        return $mainTag;
    }

    public static function getCatalogDOM() {
        return self::createGeneralContent();
    }
}
