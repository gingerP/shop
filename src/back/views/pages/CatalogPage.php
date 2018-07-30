<?php
include_once AuWebRoot . '/src/back/import/import.php';
include_once AuWebRoot . '/src/back/import/pages.php';
include_once AuWebRoot . '/src/back/import/db.php';
include_once AuWebRoot . '/src/back/views/components/pagination/PaginationComponent.php';
include_once AuWebRoot . '/src/back/views/components/categoriesMosaic/CategoriesMosaicComponent.php';

class CatalogPage extends AbstractPage
{

    private $key = "";
    private $pageNumber;
    private $itemsCount;
    private $searchValue;
    private $category;

    public function __construct(&$request)
    {
        parent::__construct(UrlParameters::PAGE__CATALOG);
        $this->category = $request->param('category', '');
        $this->pageNumber = $request->param(UrlParameters::PAGE_NUM, 1);
        if (!ctype_digit($this->pageNumber)) {
            $this->pageNumber = 1;
        } else {
            $this->pageNumber = intval($this->pageNumber);
        }
        if ($this->pageNumber < 1) {
            $this->pageNumber = 1;
        }
        $this->itemsCount = $request->param(UrlParameters::ITEMS_COUNT, Labels::VIEW_MODE_NUMERIC_DEF);
        if (!ctype_digit($this->itemsCount)) {
            $this->itemsCount = Labels::VIEW_MODE_NUMERIC_DEF;
        } else {
            $this->itemsCount = intval($this->itemsCount);
        }
        if ($this->itemsCount < 1) {
            $this->pageNumber = Labels::VIEW_MODE_NUMERIC_DEF;
        }
        $this->searchValue = $request->param(UrlParameters::SEARCH_VALUE, '');
    }

    public function build()
    {
        $this->updateTitleTagChildren('Каталог');
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
        $this->addMetaTags($metaDesc);
        $this->content = $this->getHtml();
        return $this;
    }

    protected function createGeneralContent()
    {
        $mainTag = new Div();
        $items = new Items();
        if ($this->category !== '' && !is_null($this->category)) {
            $loader = new CatalogLoader();
            $loader->getItemsForCategory($this->pageNumber, $this->itemsCount, $this->category);
            $paginationView = (new PaginationComponent())->buildForCategory(
                $this->category, $this->pageNumber, $this->itemsCount, $loader->dataTotalCount
            );

            $mainTag->addChild($paginationView);
            $mainTag->addChild($items->getItemsTable($loader->data));
            $paginationParams['position'] = 'bottom';
            $mainTag->addChild($paginationView);
        } else {
            if ($this->pageNumber === 1) {
                $mainTag->addChild((new CategoriesMosaicComponent())->build());
            }
            $loader = new CatalogLoader();
            $loader->getItemsMainData($this->pageNumber, $this->itemsCount);
            $paginationView = (new PaginationComponent())->buildForAll(
                $this->pageNumber, $this->itemsCount, $loader->dataTotalCount
            );

            $mainTag->addChild($paginationView);
            $mainTag->addChild($items->getItemsTable($loader->data));
            $paginationParams['position'] = 'bottom';
            $mainTag->addChild($paginationView);
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

        return "<a href='$link' class='category-title page-title'>$categoryLabel</a>";
    }

    protected function getSourceScripts()
    {
        $scripts = parent::getSourceScripts();
        if ($this->isJsUglify) {
            return $scripts . '<script type="text/javascript">' . file_get_contents(AuWebRoot . '/dist/catalog-page.js') . '</script>';
        } else {
            return $scripts . '<script type="text/javascript" src="/src/front/js/components/categories-component/categories.component.js"></script>';
        }
    }

    public static function getCatalogDOM()
    {
        return self::createGeneralContent();
    }
}
