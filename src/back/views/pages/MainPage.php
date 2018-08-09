<?php
include_once AuWebRoot . '/src/back/import/import.php';
include_once AuWebRoot . '/src/back/import/tags.php';
include_once AuWebRoot . '/src/back/import/pages.php';
include_once AuWebRoot . '/src/back/import/db.php';
include_once AuWebRoot . '/src/back/views/components/mainPageContacts/MainPageContactsComponent.php';
include_once AuWebRoot . '/src/back/views/components/catalogProduct/CatalogProductComponent.php';
include_once AuWebRoot . '/src/back/views/components/categoriesMosaic/CategoriesMosaicComponent.php';
include_once AuWebRoot . '/src/back/views/components/slogan/SloganComponent.php';

class MainPage extends AbstractPage
{

    public function __construct()
    {
        parent::__construct(UrlParameters::PAGE__MAIN);
    }

    public function build()
    {
        $this->setPageCode('main_page');
        $this->setIsStatusBarVisible(false);
        $this->setIsViewModeBlockVisible(false);
        $this->setIsPathLinkVisible(false);
        $metaDesc = new Meta();
        $metaDesc->addAttributes([
            'name' => 'description',
            'content' => 'на augustova.by вы найдете спецодежду для вашей работы, а юридические лица смогут приобрести лакокрасочные материалы, чистящие и моющие ср-ва, канцтовары, строительный инструмент и другое '
        ]);
        $this->addMetaTags($metaDesc);
        $this->content = $this->getHtml();
        return $this;
    }

    public function validate()
    {
        return $this;
    }

    protected function createGeneralContent()
    {
        $mainDiv = new Div();
        $div01 = new Div();
        $div01->addStyleClasses(['slide_show', 'gallery']);
        $div01->addChildren($this->getPricesGallery());
        return $mainDiv->addChildren(
            (new SloganComponent())->build(),
            $div01,
            $this->getCatalogItems()
        );
    }

    private function getPricesGallery()
    {
        $map = new Div();
        $map->addAttribute('id', 'main-page-map');
        return [$map, (new MainPageContactsComponent())->build()];
    }

    private function getCatalogItems()
    {
        $result = [$this->getCatalogItemsTitle()];
        $catalogLoader = new CatalogLoader();
        $catalogLoader->getItemsMainData(1, 10);
        $products = $catalogLoader->data;
        $goodIndex = 0;
        $slideShowContainer = new Div();
        $slideShowContainer->addStyleClass('main_page_items_slideshow categories-links');
        $slideShow = new Div();
        $slideShow->addStyleClasses(['slide_show', 'catalog_items']);
        if (count($products) > 0) {
            $div02 = new Div();
            $div02->addStyleClass('items_table');
            $div02->addAttribute('style', 'overflow: hidden;');

            $Preferences = new DBPreferencesType();
            $catalogPath = $Preferences->getPreference(SettingsNames::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];

            while ($goodIndex < count($products)) {
                $product = $products[$goodIndex];
                $productView = (new CatalogProductComponent($product, $catalogPath))->build();
                $div02->addChild($productView);
                $goodIndex++;
            }

            $url = URLBuilder::getCatalogLink();
            $remainingCountLink = new A();
            $remainingCountLink->addStyleClass('catalog-more-link');
            $remainingCountLink->addChild('Eщё ...');
            $remainingCountLink->addAttribute('href', $url);

            $slideShowContainer->addChildren($slideShow->addChildren($div02, $remainingCountLink));

            $result[] = $slideShowContainer;
        }
        $categoriesTitle = new A();
        $categoriesTitle->addChild(Localization['main_page.categories.title']);
        $categoriesTitle->addStyleClass('main-page-categories-title');
        $categoriesTitle->addAttribute('href', URLBuilder::getCatalogLink());
        $slideShowContainer->addChild($categoriesTitle);
        $slideShowContainer->addChild((new CategoriesMosaicComponent('main-page'))->build());
        $result[] = $this->getCategoriesGroupsDom();
        return $result;
    }

    private function getCatalogItemsTitle()
    {
        return '';
    }

    private function getCategoriesGroupsDom()
    {
        function normalizeCategories($categories)
        {
            $result = [];
            $index = count($categories) - 1;
            while ($index >= 0) {
                $category = $categories[$index];
                $result[$category[DB::TABLE_NAV_KEY__KEY_ITEM]] = $category;
                $index--;
            }
            return $result;
        }

        $container = new Div();
        $container->addStyleClass('main_page_items_slideshow categories');
        $Preferences = new DBPreferencesType();
        $categoriesCodes = $Preferences->getPreferenceValue(SettingsNames::SETTINGS_MAIN_PAGE_CATEGORIES);
        $categoriesCodes = explode(';', $categoriesCodes);
        if (count($categoriesCodes) > 0) {
            $Categories = new DBNavKeyType();
            $categories = $Categories->getListIn(DB::TABLE_NAV_KEY__KEY_ITEM, $categoriesCodes);
            $categories = $Categories->extractDataFromResponse($categories);
            $normalizedCategories = normalizeCategories($categories);
            $Products = new DBGoodsType();
            $categoriesCounts = $Products->getCategoriesCount();
            $index = 0;
            $existsIndex = 0;
            while ($index < count($categoriesCodes)) {
                $categoryCode = $categoriesCodes[$index];
                $category = $normalizedCategories[$categoryCode];
                $products = $Products->getUserSortedForMenu([$categoryCode], 0, 5);
                $products = $Products->extractDataFromResponse($products);
                if (count($products) > 0) {
                    $totalCount = $categoriesCounts[$categoryCode];
                    $group = $this->getCategoryGroupDom($category, $products, $totalCount);
                    $group->addStyleClass($existsIndex % 2 == 0 ? 'odd' : 'even');
                    $container->addChild($group);
                    $existsIndex++;
                }
                $index++;
            }
        }
        return $container;
    }

    private function getCategoryGroupDom($category, $products, $totalProductsCount)
    {
        $container = new Div();
        $productsList = new Div();
        $container->addChild($productsList);
        $container->addStyleClass('items_table ');
        $container->addAttribute('id', $category[DB::TABLE_NAV_KEY__KEY_ITEM]);

        $subContainer = new Div();
        $subContainer->addStyleClass('slide_show catalog_items ');
        $subSubContainer = new Div();
        $subSubContainer->addStyleClass('');
        $subContainer->addChildren(
            $this->getGroupTitleDom($category, $totalProductsCount - count($products)),
            $subSubContainer
        );
        $index = 0;

        $Preferences = new DBPreferencesType();
        $catalogPath = $Preferences->getPreference(SettingsNames::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
        while ($index < count($products)) {
            $product = $products[$index];
            $productView = (new CatalogProductComponent($product, $catalogPath))->build();
            $subSubContainer->addChild($productView);
            $index++;
        }
        return $container->addChild($subContainer);
    }

    private function getGroupTitleDom($category, $remainingCount)
    {
        $container = new Div();
        $url = URLBuilder::getCatalogLinkForTree($category[DB::TABLE_NAV_KEY__KEY_ITEM]);
        $link = new A();
        $link->addAttribute('href', $url);
        $link->addStyleClass('category-title');
        $link->addChild($category[DB::TABLE_NAV_KEY__VALUE]);
        $linkContainer = new Div();
        $linkContainer->addChild($link);
        $linkContainer->addStyleClass('category-title-left');
        $container->addChildren($linkContainer);
        if ($remainingCount > 0) {
            $remainingCountLink = new A();
            $remainingCountLink->addStyleClass('category-title-postfix');
            $remainingCountLink->addChild("ещё $remainingCount " . self::getLabelByCount($remainingCount) . '...');
            $remainingCountLink->addAttribute('href', $url);
            $remainingLinkContainer = new Div();
            $remainingLinkContainer->addChild($remainingCountLink);
            $remainingLinkContainer->addStyleClass('category-title-right');
            $container->addChildren($remainingLinkContainer);
        }
        $container->addStyleClass('category-title-container');
        return $container;
    }

    private static function getLabelByCount($productsCount)
    {
        $lastStringNumber = $productsCount . '';
        $lastStringNumber = $lastStringNumber[strlen($lastStringNumber) - 1];
        $label = 'товар';
        if ($productsCount > 1 && $productsCount <= 4
            || $productsCount > 20 && ($lastStringNumber == '2' || $lastStringNumber == '4')
        ) {
            $label = 'товара';
        } else if ($productsCount > 4 && $productsCount <= 20) {
            $label = 'товаров';
        } else if ($productsCount > 20 && $lastStringNumber == '1') {
            $label = 'товар';
        }
        return $label;
    }

    protected function getSourceScripts()
    {
        $scripts = parent::getSourceScripts();
        $preferences = PreferencesService::getPublicPreferences();
        $scripts .= strtr("<script type='text/javascript'>
            window.AugustovaApp = {googleApiKey: 'googleApiKeyValue'};
        </script>", [
            'googleApiKeyValue' => $preferences['google_maps_api_key']
        ]);
        if ($this->isJsUglify) {
            return $scripts . '<script type="text/javascript">' . file_get_contents(AuWebRoot . '/dist/main-page.js') . '</script>';
        }
        $scripts .= '
            <script type="text/javascript" src="/src/front/js/components/google-map/google-map.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/main-page-contacts/mainPageContacts.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/categories-component/categories.component.js"></script>
            ';
        return $scripts;
    }

}