<?php
include_once("src/back/import/import");
include_once("src/back/import/tag");
include_once("src/back/import/page");
include_once("src/back/import/db");

class MainPage extends APagesCreator
{

    public function __construct()
    {
        parent::__construct();
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
    }

    protected function createGeneralContent()
    {
        $mainDiv = new Div();
        $div01 = new Div();
        $div01->addStyleClasses(["slide_show", "gallery"]);
        $div01->addAttribute("style", "height: 320px;");
        $div01->addChild($this->getPricesGallery());
        return $mainDiv->addChildren($div01, $this->getCatalogItems(), $this->preRenderNewsItems());
    }

    private function getPricesGallery()
    {
        $mainDiv = new Div();
        $mainDiv->addStyleClass("prices_gallery center_column");
        $mainDiv->addChild("<news-gallery-component></news-gallery-component>");
        return $mainDiv;
    }

    //TODO check next method for performance (работоспособность)

    private function getCatalogItems()
    {
        $dbGoods = new DBGoodsType();
        $catalogLoader = new CatalogLoader();
        $catalogLoader->getItemsMainData(1, 15);
        $goods = $dbGoods->extractDataFromResponse($catalogLoader->data, DB::TABLE_GOODS___MAPPER);
        $goodIndex = 0;
        $slideShowContainer = new Div();
        $slideShowContainer->addStyleClass("main_page_items_slideshow");
        $slideShow = new Div();
        $slideShow->addStyleClasses(["slide_show", "catalog_items"]);
        $div02 = new Div();
        $div02->addStyleClass('items_table');
        $div02->addAttribute("style", "overflow: hidden;");
        while ($goodIndex < count($goods)) {
            //$name, $images, $itemId, $pageNumber, $num, $key, $valueToSearch, $type, $trimName, $isHighLightElement
            $product = $goods[$goodIndex];
            $images = FileUtils::getFilesByPrefixByDescription(
                Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $product[DB::TABLE_GOODS__KEY_ITEM] . DIRECTORY_SEPARATOR,
                Constants::MEDIUM_IMAGE,
                'jpg'
            );
            $info = Item::getMetroItemView(
                $product["name"],
                $images,
                $product["key_item"],
                null,
                null,
                null,
                null,
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
        $slideShowContainer->addChildren($slideShow->addChildren($div02));
        return [$this->getCatalogItemsTitle(), $slideShowContainer];
    }

    private function getCatalogItemsTitle()
    {
        $headContainer = new Div();

        $head = new Div();
        $head->addStyleClasses(["slogan_container_horizontal", "z-10"]);
        $slogan = new Div();
        $slogan->addStyleClass("slogan");
        $slogan->addChild("Наша спецодежда позаботится о Вас.");
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

    private function renderGalleryItemWithSingleItem($data)
    {
        $mainDiv = new Div();
        $mainDiv->addStyleClasses(["main_page_item", "blackout", "catalog_item_button_container"]);
        $container = new Div();
        $container->addStyleClass("main_page_item_sub");
        $urlToItem = URLBuilder::getItemLinkForComplexType("", $data[DB::TABLE_GOODS__KEY_ITEM], 1, 48);
        $itemImagePath = '';
        $itemName = $data[DB::TABLE_GOODS__NAME];
        $images = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $data[DB::TABLE_GOODS__KEY_ITEM] . DIRECTORY_SEPARATOR, Constants::MEDIUM_IMAGE, 'jpg');
        if ($images[0] == '') {
            $itemImagePath = FileUtils::getCapImage(Labels::CAP_IMAGE_FOR_CLOTHING);
        } else {
            $itemImagePath = $images[0];
        }
        $singleItemView = new Div();
        $singleItemView->addStyleClasses(["main_page_item_sub_single"]);
        $imgView = new Img();
        $noteView = null;
        $imgView->addAttribute("src", $itemImagePath);
        $singleItemView->addStyleClass("cursor_pointer");
        $noteView = TagUtils::createNote($itemName, "");
        $noteView->addStyleClasses(["f-15"]);
        $container->addChildList([$singleItemView, $noteView, Item::getItemButton($urlToItem)]);
        $singleItemView->addChild($imgView);
        return $mainDiv->addChild($container);
    }

    private function renderGalleryItemWithMultipleItems($data)
    {
        $mainDiv = new Div();
        /*$mainDiv->addStyleClass("main_page_item");
        $mainDiv->addChild("<news-gallery></news-gallery>");*/
        /*while ($row = mysqli_fetch_array($data)) {
            $urlToItem = URLBuilder::getItemLinkForSimpleType($row[ DB::TABLE_GOODS__KEY_ITEM ]);
            $onClickValue = Utils::getWindowOnclickValue($urlToItem);
            $itemImagePath = $row[ DB::TABLE_GOODS__IMAGE_PATH ];
            $itemName = $row[ DB::TABLE_GOODS__NAME ];
            $singleItemViewContainer = new Div();
            $singleItemView = new Div();
            $singleItemViewContainer->addChild($singleItemView);
            $singleItemViewContainer->addStyleClass("main_page_multiple_container");
            $singleItemView->addStyleClasses(["main_page_item_sub_multiple", "blackout"]);
            $imgView = new Img();
            $imgView->addAttribute("src", $itemImagePath);
            $noteView = TagUtils::createNote($itemName, $urlToItem);
            $noteView->addStyleClass("f-15");
            $mainDiv->addChild($singleItemViewContainer);
            $singleItemView->addChild($imgView);
            $singleItemView->addChild($noteView);
        }*/
        return $mainDiv;
    }

    private function preRenderNewsItems()
    {
        $mainDiv = new Div();
        /* $mainDiv->addStyleClass("main_page_items_slideshow");
         $head = new Div();
         $head->addStyleClass("main_page_slogan_container news_slogan center_column");
         $sloganContainer = new Div();
         $slogan = new Div();
         $slogan->addStyleClass("slogan news_slogan ");
         $slogan->addChild("Новости");

         $leftEar = new Div();
         $leftEar->addStyleClass("slogan_left_ear");
         $rightEar = new Div();
         $rightEar->addStyleClass("slogan_right_ear");

         $head->addChildren($leftEar, $sloganContainer->addChildren($slogan), $rightEar);
         $itemsBlock = new Div();
         $items = new Div();
         $itemsBlock->addStyleClass("news_items_block");
         $itemsBlock->addChildren($items);
         $items->addStyleClass("slide_show news_items");
         $itemsFullSizeContainer = new Div();
         $itemsFullSizeContainer->addStyleClass('news_items_container center_column');
         $itemsFullSizeContainer->addChild("<news-component></news-component>");*/

        $newsCount = 0;
        //$itemsFullSizeContainer->addAttribute("style", "width: ".($newsCount * 1050)."px; height: 400px;");
        return $mainDiv/*->addChildren($head, $itemsBlock->addChild($itemsFullSizeContainer))*/
            ;
    }

    private function getNewsItem($dbRow)
    {
        $mainDiv = new Div();
        $mainDiv->addStyleClasses(["news_item", "main_page_item"]);
        $newsItemSubContainer = new Div();
        $newsItemSubContainer->addStyleClass("main_page_item_sub");
        $mainDiv->addChildren($newsItemSubContainer);
        if (!is_null($dbRow[DB::TABLE_NEWS__CONTENT]) && strlen($dbRow[DB::TABLE_NEWS__CONTENT]) > 0) {
            $video = new Div();
            $video->addStyleClass("news_video");
            $video->addChild($dbRow[DB::TABLE_NEWS__CONTENT]);
            $newsItemSubContainer->addChildren($video);
        }
        if (!is_null($dbRow[DB::TABLE_NEWS__TITLE]) && strlen($dbRow[DB::TABLE_NEWS__TITLE]) > 0) {
            $title = new Div();
            $title->addStyleClass("news_title");
            $title->addChild($dbRow[DB::TABLE_NEWS__TITLE]);
            $newsItemSubContainer->addChildren($title);
        }
        if (!is_null($dbRow[DB::TABLE_NEWS__TEXT]) && strlen($dbRow[DB::TABLE_NEWS__TEXT]) > 0) {
            $description = new Div();
            $description->addStyleClass("news_text");
            $description->addChild($dbRow[DB::TABLE_NEWS__TEXT]);
            $newsItemSubContainer->addChildren($description);
        }
        return $mainDiv;
    }

    private function renderPriceDownloadItems()
    {
        $mainDiv = new Div();
        $mainDiv->addStyleClasses(["w-25p", "price_download_container", "blackout"]);
        $button = new Div();
        $buttonContainer = new Div();
        $button->addStyleClasses(["price_download_button", "button", "note", "w-50p", "input_hover", "f-15"]);
        $button->addChild("скачать прайс-лист");
        $buttonContainer->addChild($button);
        $mainDiv->addChild($buttonContainer);
        return $mainDiv;
    }

    private function getStyledTextList($textList, $styles)
    {
        $mainDiv = new Div();
        $styleIndex = 0;
        for ($textIndex = 0; $textIndex < count($textList); $textIndex++) {
            $text = new Div();
            $text->addChild($textList[$textIndex]);
            $text->addStyleClass($styles[$styleIndex]);
            if ($styleIndex == count($styles) - 1) {
                $styleIndex = 0;
            } else {
                $styleIndex++;
            }
            $mainDiv->addChild($text);
        }
        return $mainDiv;
    }
}