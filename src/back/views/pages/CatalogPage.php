<?php
include_once AuWebRoot . '/src/back/import/import.php';
include_once AuWebRoot . '/src/back/import/pages.php';
include_once AuWebRoot . '/src/back/import/db.php';
include_once AuWebRoot . '/src/back/views/components/pagination/PaginationComponent.php';
include_once AuWebRoot . '/src/back/views/components/categoriesMosaic/CategoriesMosaicComponent.php';

class CatalogPage extends AbstractPage
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
        if ($this->category !== '') {
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
            $mainTag->addChild((new CategoriesMosaicComponent())->build());
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

    protected function getSourceScripts()
    {
        $scripts = parent::getSourceScripts();
        if ($this->isJsUglify) {
            return $scripts . '<script type="text/javascript" src="/dist/catalog-page.js"></script>';
        }
        return $scripts;
    }

    public static function getCatalogDOM()
    {
        return self::createGeneralContent();
    }
}
