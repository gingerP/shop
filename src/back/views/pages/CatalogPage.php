<?php
include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/pages.php';
include_once AuWebRoot.'/src/back/import/db.php';

class CatalogPage extends APagesCreator
{

    private $key = "";
    private $urlParams;
    private $pageNumber;
    private $itemsCount;
    private $searchValue;
    private $category;

    public function __construct(&$request)
    {
        parent::__construct(UrlParameters::PAGE__CATALOG);
        $this->category = $request->param('category', '');
        $this->pageNumber = intval($request->param(UrlParameters::PAGE_NUM, 1));
        $this->itemsCount = intval($request->param(UrlParameters::ITEMS_COUNT, Labels::VIEW_MODE_NUMERIC_DEF));
        $this->searchValue = $request->param(UrlParameters::SEARCH_VALUE, '');
    }

    public function build()
    {
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
        return $this;
    }

    protected function createGeneralContent()
    {
        $mainTag = new Div();
        $items = new Items();
        $catalogLinks = new CatalogLinks();

        if ($this->searchValue !== '') {
            $loader = new CatalogLoader();
            $loader->getItemSearchData($this->pageNumber, $this->itemsCount, $this->searchValue);
            if ($loader->dataTotalCount == 0) {
                return "";
            }
            $paginationParams = [
                'pageNum' => $this->pageNumber,
                'itemsCount' => $this->itemsCount,
                'totalCount' => $loader->dataTotalCount,
                'searchValue' => $this->searchValue,
                'position' => 'top'
            ];
            $mainTag->addChild($catalogLinks->getPaginationLinks($paginationParams));
            $mainTag->addChild($items->getItemsTable($loader->data));
            $paginationParams['position'] = 'bottom';
            $mainTag->addChild($catalogLinks->getPaginationLinks($paginationParams));
        } else if ($this->category !== '') {
            $loader = new CatalogLoader();
            $loader->getItemsForCategory($this->pageNumber, $this->itemsCount, $this->category);
            $paginationParams = [
                'pageNum' => $this->pageNumber,
                'itemsCount' => $this->itemsCount,
                'totalCount' => $loader->dataTotalCount,
                'category' => $this->category,
                'position' => 'top'
            ];
            $mainTag->addChild($catalogLinks->getPaginationLinks($paginationParams));
            $mainTag->addChild($items->getItemsTable($loader->data));
            $paginationParams['position'] = 'bottom';
            $mainTag->addChild($catalogLinks->getPaginationLinks($paginationParams));
        } else {
            $Categories = new CategoriesComponent();
            $mainTag->addChild($Categories->build());
/*            $loader = new CatalogLoader();
            $loader->getItemsMainData($this->pageNumber, $this->itemsCount);
            $paginationParams = [
                'pageNum' => $this->pageNumber,
                'itemsCount' => $this->itemsCount,
                'totalCount' => $loader->dataTotalCount,
                'position' => 'top'
            ];
            $mainTag->addChild($catalogLinks->getPaginationLinks($paginationParams));
            $mainTag->addChild($items->getItemsTable($loader->data));
            $paginationParams['position'] = 'bottom';
            $mainTag->addChild($catalogLinks->getPaginationLinks($paginationParams));*/
        }
        return $mainTag;
    }

    public function createPathLinks()
    {

        if ($this->category == 'GN' || $this->category == '') {
            $categoryLabel = Localization['panel.top.catalog'];
            $link = URLBuilder::getCatalogLinkForTree('');
        } else {
            $Categories = new DBNavKeyType();
            $categoryLabel = $Categories->getNameByKey($this->category);
            $link = URLBuilder::getCatalogLinkForTree($this->category);
        }

        return "<a href='$link' class='category-title'>$categoryLabel</a>";
    }

    public static function getCatalogDOM()
    {
        return self::createGeneralContent();
    }
}
