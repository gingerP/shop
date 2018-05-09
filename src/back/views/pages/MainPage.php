<?php
include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/tags.php';
include_once AuWebRoot.'/src/back/import/pages.php';
include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/views/components/mainPageContacts/MainPageContactsComponent.php';

class MainPage extends APagesCreator
{

    public function __construct()
    {
        parent::__construct(UrlParameters::PAGE__MAIN);
    }

    public function build() {
        $this->setPageCode("main_page");
        $this->setIsStatusBarVisible(false);
        $this->setIsViewModeBlockVisible(false);
        $this->setIsPathLinkVisible(false);
        $metaDesc = new Meta();
        $metaDesc->addAttributes([
            "name" => "description",
            "content" => "на augustova.by вы найдете спецодежду для вашей работы, а юридические лица смогут приобрести лакокрасочные материалы, чистящие и моющие ср-ва, канцтовары, строительный инструмент и другое "
        ]);
        $this->addMetaTags($metaDesc);
        $this->content = $this->getHtml();
        return $this;
    }

    public function validate() {
        return $this;
    }

    protected function createGeneralContent()
    {
        $mainDiv = new Div();
        $div01 = new Div();
        $div01->addStyleClasses(["slide_show", "gallery"]);
        $div01->addChildren($this->getPricesGallery());
        return $mainDiv->addChildren($div01, $this->getCatalogItems());
    }

    private function getPricesGallery()
    {
        $mainDiv = new Div();
        $map = new Div();
        $map->addAttribute("id", "main-page-map");
        return [$map, (new MainPageContactsComponent())->build()];
    }

    //TODO check next method for performance (работоспособность)

    private function getCatalogItems()
    {
        $result = [$this->getCatalogItemsTitle()];
        $catalogLoader = new CatalogLoader();
        $catalogLoader->getItemsMainData(1, 10);
        $products = $catalogLoader->data;
        $goodIndex = 0;
        $slideShowContainer = new Div();
        $slideShowContainer->addStyleClass("main_page_items_slideshow");
        $slideShow = new Div();
        $slideShow->addStyleClasses(["slide_show", "catalog_items"]);
        if (count($products) > 0) {
            $div02 = new Div();
            $div02->addStyleClass('items_table');
            $div02->addAttribute("style", "overflow: hidden;");

            $Preferences = new DBPreferencesType();
            $catalogPath = $Preferences->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];

            while ($goodIndex < count($products)) {
                $product = $products[$goodIndex];
                $code = $product[DB::TABLE_GOODS__KEY_ITEM];

                $imagesCodes = json_decode($product[DB::TABLE_GOODS__IMAGES]);
                $images = ProductsUtils::normalizeImagesFromCodes($imagesCodes, $code, Constants::MEDIUM_IMAGE, $catalogPath);
                $info = Item::getMetroItemView(
                    $product["name"],
                    $images,
                    $product[DB::TABLE_GOODS__VERSION],
                    Utils::formatClotheTitle($product["name"])
                );
                $productContainer = new A();
                $productContainer->addStyleClass("catalog_good_item previews-zero-col");
                $productContainer->addChild($info[0]);
                $url = URLBuilder::getItemLinkForComplexType("", $product[DB::TABLE_GOODS__KEY_ITEM], 1, 48);
                $productContainer->addAttribute('href', $url);
                $div02->addChild($productContainer);
                //$div02->addChild($this->renderGalleryItemWithSingleItem($goods[$goodIndex]));
                $goodIndex++;
            }

            $url = URLBuilder::getCatalogLink();
            $remainingCountLink = new A();
            $remainingCountLink->addStyleClass('catalog-more-link');
            $remainingCountLink->addChild("Eщё ...");
            $remainingCountLink->addAttribute('href', $url);

            $slideShowContainer->addChildren($slideShow->addChildren($div02, $remainingCountLink));

            array_push($result, $slideShowContainer);
        }
        array_push($result, $this->getCategoriesGroupsDom());
        return $result;
    }

    private function getCatalogItemsTitle()
    {
        $headContainer = new Div();

        $head = new Div();
        $head->addStyleClasses(["slogan_container_horizontal", "z-10"]);
        $slogan = new Div();
        $slogan->addStyleClass("slogan");
        $slogan->addChild("Собственное производство.");
        $head->addChildren($slogan);

        $head2 = new Div();
        $head2->addStyleClasses(["slogan_container_horizontal", "z-9"]);
        $catalogLink = new A();
        $catalogLink->addAttribute("href", Labels::$TOP_NAVIGATION_LINKS['catalog']);
        $catalogLink->addStyleClass("main_page_catalog_link");
        $catalogLink->addChild("Каталог");
        $head2->addChildren($catalogLink/*, $rightEarForLink*/);

        $headContainer->addChildren($head, $head2);
        $slogan = new Div();
        $slogan->addStyleClass('catalog_slogan');
        $headContainer->addStyleClass("main_page_slogan_container");
        return $slogan->addChild($headContainer);
    }

    private function getCategoriesGroupsDom()
    {
        function normalizeCategories($categories) {
            $result = [];
            $index = count($categories) - 1;
            while($index >= 0) {
                $category = $categories[$index];
                $result[$category[DB::TABLE_NAV_KEY__KEY_ITEM]] = $category;
                $index--;
            }
            return $result;
        }
        $container = new Div();
        $container->addStyleClass('main_page_items_slideshow categories');
        $Preferences = new DBPreferencesType();
        $categoriesCodes = $Preferences->getPreferenceValue(Constants::SETTINGS_MAIN_PAGE_CATEGORIES);
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
        $catalogPath = $Preferences->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
            while ($index < count($products)) {
            $product = $products[$index];
            $code = $product[DB::TABLE_GOODS__KEY_ITEM];

            $imagesCodes = json_decode($product[DB::TABLE_GOODS__IMAGES]);
            $images = ProductsUtils::normalizeImagesFromCodes($imagesCodes, $code, Constants::MEDIUM_IMAGE, $catalogPath);
            $productCard = Item::getMetroItemView(
                $product[DB::TABLE_GOODS__NAME],
                $images,
                $product[DB::TABLE_GOODS__VERSION],
                Utils::formatClotheTitle($product[DB::TABLE_GOODS__NAME])
            )[0];
            $productLink = new A();
            $productLink->addStyleClass('catalog_good_item');
            $productLink->addChild($productCard);
            $url = URLBuilder::getCatalogLinkForSingleItem($product[DB::TABLE_GOODS__KEY_ITEM]);
            $productLink->addAttribute('href', $url);
            $subSubContainer->addChild($productLink);
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
            $remainingCountLink->addChild("ещё $remainingCount " . self::getLabelByCount($remainingCount) . "...");
            $remainingCountLink->addAttribute('href', $url);
            $remainingLinkContainer = new Div();
            $remainingLinkContainer->addChild($remainingCountLink);
            $remainingLinkContainer->addStyleClass('category-title-right');
            $container->addChildren($remainingLinkContainer);
        }
        $container->addStyleClass('category-title-container');
        return $container;
    }

    private static function getLabelByCount($productsCount) {
        $lastStringNumber = $productsCount.'';
        $lastStringNumber = $lastStringNumber[strlen($lastStringNumber) - 1];
        $label = 'товар';
        if ($productsCount > 1 && $productsCount <= 4
            || $productsCount > 20 && ($lastStringNumber == '2' || $lastStringNumber == '4')) {
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
        $preferences = PreferencesService::getPublicPreferences();
        $scripts = strtr("<script type='text/javascript'>
            window.AugustovaApp = {googleApiKey: 'googleApiKeyValue'};
        </script>", [
            'googleApiKeyValue' => $preferences['google_maps_api_key']
        ]);
        $scripts .= parent::getSourceScripts();
        if (!$this->isJsUglify) {
            $scripts .=
                '
                <script type="text/javascript" src="/src/front/js/components/google-map/google-map.component.js"></script>
                <script type="text/javascript" src="/src/front/js/components/main-page-contacts/mainPageContacts.component.js"></script>';
        }
        return $scripts;
    }

}