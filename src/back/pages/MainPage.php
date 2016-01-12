<?php
include_once("import");
include_once("tag");
include_once("db");

class MainPage extends APagesCreator {

    public function MainPage() {
        $this->APagesCreator();
        $this->setPageCode("main_page");
        $this->setIsStatusBarVisible(false);
        $this->setIsViewModeBlockVisible(false);
        $this->setIsPathLinkVisible(false);
        $metaDesc = new Meta();
        $metaDesc->addAttributes([
            "name" => "description",
            "content" => "на augustova.by вы найдете спецодежду для вашей работы, а юридические лица смогут приобрести лакокрасочные материалы, чистящие и моющие ср-ва, канцтовары, строительный инструмент и другое "
        ]);
        $metaKeyword = new Meta();
        $metaKeyword->addAttributes([
           "name" => "keywords",
           "content" => ""
        ]);
        $this->addMetaTags($metaDesc, $metaKeyword);
        $html = $this->getHtml();
        echo $html;
    }

    protected function createGeneralContent() {
        $mainDiv = new Div();
        $div01 = new Div();
        $div01->addStyleClasses(["slide_show", "gallery"]);
        $div01->addAttribute("style", "height: 300px;");
        $div01->addChild($this->getPricesGallery());
        return $mainDiv->addChildren($div01, $this->preRenderNewsItems(), $this->getCatalogItems());
    }

    private function getPricesGallery() {
        $mainDiv = new Div();
        $mainDiv->addStyleClass("prices_gallery");

        $priceDescriptions = new Div();
        $priceDescriptions->addStyleClass("prices_descriptions");
        $priceButtonsContainer = new Div();
        $priceButtonsContainer->addStyleClass("price_buttons_container");
        $priceButtons = new Div();
        $priceButtons->addStyleClass("prices_buttons");
        $priceButtonsContainer->addChild($priceButtons);
        $mainDiv->addChildList([$priceButtonsContainer, $priceDescriptions]);
        $dbGoodsTypesType = new DBGoodsTypesType();
        $goodsTypes = $dbGoodsTypesType->getListActive();
        $index = 0;
        while ($row = mysql_fetch_array($goodsTypes)) {
            $priceDescription = new Div();
            $priceDescription->addAttribute("price_code", $row[ DB::TABLE_GOODS_TYPES__CODE ]);
            $priceDescription->addStyleClasses(["f-16", "prices_description"]);
            $priceDescription->addAttribute("style", "background-image: url(/images/" . $row[ DB::TABLE_GOODS_TYPES__CODE ] . ".jpg)");
            $priceDescriptionChild = new Div();
            $priceDescription->addChild($priceDescriptionChild);
            if ($index == 0) {
                $priceDescription->addStyleClass("selected_description");
            } else {
                $priceDescription->addStyleClass("hidden");
            }
            $priceLabel = new Strong();
            $priceLabel->addChild($row[ DB::TABLE_GOODS_TYPES__NAME ]);
            $priceLabel->addStyleClasses(["description_title description_label", "f-40"]);
            $priceDescriptionChild->addChild($priceLabel);

            $description = $row[ DB::TABLE_GOODS_TYPES__DESCRIPTION ];
            $description = Utils::cleanExplode(Constants::LIST_DELIMITER, $description);
            if (count($description)) {
                array_push($description, "и другое...");
                $list = $this->getStyledTextList($description, ["main_page_price_item_first", /*"main_page_price_item_second", */"main_page_price_item_third"]);
                $list->addStyleClass("description_label");
                $priceDescriptionChild->addChildList([$list]);
            }

            if (strlen($row[ DB::TABLE_GOODS_TYPES__PRICE_FILE_NAME ]) > 0 && FileUtils::isFileExist(Constants::DEFAULT_ROOT_PRICE_PATH . $row[ DB::TABLE_GOODS_TYPES__PRICE_FILE_NAME ])) {
                $textContainer = new Span();
                $textContainer->addChild("скачать прайс лист");
                $priceDownloadButton = new A();
                $priceDownloadButton->addChild($textContainer);
                $priceDownloadButton->addStyleClasses(["button", "f-19", "input_hover"]);
                $priceDownloadButton->addAttribute("href", Constants::DEFAULT_ROOT_PRICE_PATH . $row[ DB::TABLE_GOODS_TYPES__PRICE_FILE_NAME ]);
                $priceDescriptionChild->addChild($priceDownloadButton);
            }
            $priceDescriptions->addChild($priceDescription);

            $priceButton = new Div();
            $priceButton->addAttribute("price_code", $row[ DB::TABLE_GOODS_TYPES__CODE ]);
            $priceButton->addStyleClasses(["price_button", "f-16", "input_hover"]);
            $priceButton->addChild($row[ DB::TABLE_GOODS_TYPES__ABBREVIATION]);
            $priceButtonLabel = new Div();
            $priceButtonLabel->addChild($row[ DB::TABLE_GOODS_TYPES__NAME ]);
            $priceButtons->addChild($priceButton/*->addChild($priceButtonLabel)*/);
            $index++;
        }
        return $mainDiv;
    }

    //TODO check next method for performance (работоспособность)

    private function getCatalogItems() {
        $goodsType = new DBGoodsType();




        $slideShowContainer = new Div();
        $slideShowContainer->addStyleClass("main_page_items_slideshow");
        $slideShow = new Div();
        $slideShow->addStyleClasses(["slide_show", "catalog_items"]);
        $div02 = new Div();
        $div02->addAttribute("style", "overflow: hidden;");
        $response = $goodsType->getRandomRowByKeys(["MH"], 1);
        $div02->addChild($this->renderGalleryItemWithSingleItem($response));
        $response = $goodsType->getRandomRowByKeys(["MK"], 1);
        $div02->addChild($this->renderGalleryItemWithSingleItem($response));
        $response = $goodsType->getRandomRowByKeys(["CS"], 1);
        $div02->addChild($this->renderGalleryItemWithSingleItem($response));
        $response = $goodsType->getRandomRowByKeys(["TS"], 1);
        $div02->addChild($this->renderGalleryItemWithSingleItem($response));

        $div02->addAttribute("style", "overflow: hidden;");
        $response = $goodsType->getRandomRowByKeys(["KS"], 1);
        $div02->addChild($this->renderGalleryItemWithSingleItem($response));
        $response = $goodsType->getRandomRowByKeys(["CC"], 1);
        $div02->addChild($this->renderGalleryItemWithSingleItem($response));
        $response = $goodsType->getRandomRowByKeys(["MH"], 1);
        $div02->addChild($this->renderGalleryItemWithSingleItem($response));
        $response = $goodsType->getRandomRowByKeys(["TS"], 1);
        $div02->addChild($this->renderGalleryItemWithSingleItem($response));

        $slideShowContainer->addChildren($slideShow->addChildren($div02));



        return [$this->getCatalogItemsTitle(), $slideShowContainer];
    }

    private function getCatalogItemsTitle() {
        $headContainer = new Div();

        $head = new Div();
        $head->addStyleClasses(["slogan_container_horizontal", "z-10"]);
        $leftEar = new Div();
        $leftEar->addStyleClass("slogan_left_ear");
        $rightEar = new Div();
        $rightEar->addStyleClass("slogan_right_ear");
        $sloganContainer = new Div();
        $slogan = new Div();
        $slogan->addStyleClass("slogan");
        $slogan->addChild("Наша спецодежда позаботится о Вас.");
        $sloganContainer->addChildren($slogan);
        $head->addChildren($leftEar, $sloganContainer, $rightEar, $rightEar);

        $head2 = new Div();
        $head2->addStyleClasses(["slogan_container_horizontal", "z-9"]);
        $catalogLinkContainer = new A();
        $catalogLinkContainer->addAttribute("href", Labels::$TOP_NAVIGATION_LINKS[catalog]);
        $catalogLinkContainer->addStyleClass("main_page_catalog_link_container");
        $catalogLink = new Span();
        $catalogLink->addChild("Каталог");
        $catalogLink->addStyleClasses(["main_page_catalog_link button input_hover", "f-25"]);
        $catalogLinkContainer->addChildren($catalogLink);
        /*$rightEarForLink = new Div();
        $rightEarForLink->addStyleClasses(["slogan_right_ear", "background_dark"]);*/
        $head2->addChildren($catalogLinkContainer/*, $rightEarForLink*/);

        $headContainer->addChildren($head, $head2);
        $headContainer->addStyleClass("main_page_slogan_container");
        return $headContainer;
    }

    private function renderGalleryItemWithSingleItem($data) {
        $mainDiv = new Div();
        $mainDiv->addStyleClasses(["main_page_item", "blackout", "catalog_item_button_container"]);
        $container = new Div();
        $container->addStyleClass("main_page_item_sub");
        while ($row = mysql_fetch_array($data)) {
            $urlToItem = URLBuilder::getItemLinkForComplexType("", $row[ DB::TABLE_GOODS__KEY_ITEM ], 1, 48);
            $itemImagePath = '';
            $itemName = $row[ DB::TABLE_GOODS__NAME ];
            $images = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $row[ DB::TABLE_GOODS__KEY_ITEM ] . DIRECTORY_SEPARATOR, Constants::MEDIUM_IMAGE, 'jpg');
            if ($images[ 0 ] == '') {
                $itemImagePath = FileUtils::getCapImage(Labels::CAP_IMAGE_FOR_CLOTHING);
            } else {
                $itemImagePath = $images[ 0 ];
            }
            $singleItemView = new Div();
            $singleItemView->addStyleClasses(["main_page_item_sub_single"]);
            $imgView = new Img();
            $noteView = null;
            $imgView->addAttribute("src", $itemImagePath);
            if ($row[ DB::TABLE_GOODS__GOD_TYPE ] == "HARD") {
                $singleItemView->addStyleClass("cursor_pointer");
                $noteView = TagUtils::createNote($itemName, "");
            } else {
                $noteView = TagUtils::createNote($itemName, "");
            }
            $noteView->addStyleClasses(["f-15"]);
            $container->addChildList([$singleItemView, $noteView, Item::getItemButton($urlToItem)]);
            $singleItemView->addChild($imgView);
        }
        return $mainDiv->addChild($container);
    }

    private function renderGalleryItemWithMultipleItems($data) {
        $mainDiv = new Div();
        $mainDiv->addStyleClass("main_page_item");
        while ($row = mysql_fetch_array($data)) {
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
        }
        return $mainDiv;
    }

    private function preRenderNewsItems() {
        $mainDiv = new Div();
        $mainDiv->addStyleClass("main_page_items_slideshow");
        $head = new Div();
        $head->addStyleClass("main_page_slogan_container");
        $sloganContainer = new Div();
        $slogan = new Div();
        $slogan->addStyleClass("slogan");
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
        $itemsFullSizeContainer->addStyleClass('news_items_container');

        $items_ = new Div();
        $items_->addStyleClasses(["slide_show", "news_items"]);
        $items_->addChildren($itemsFullSizeContainer);
        $items->addChildren($items_);
        $items->addAttribute("style", "min-height: 400px; overflow: visible;");
        $dbNewsType = new DBNewsType();
        $dbNewsType->getListActive();
        $newsCount = 0;
        while($row = mysql_fetch_array($dbNewsType->getResponse())) {
            $itemsFullSizeContainer->addChildren($this->getNewsItem($row));
            $newsCount++;
        }
        /*if ($newsCount > 0) {
            $leftArrow = new Div();
            $leftArrow->addStyleClasses(["gallery_left_arrow_bold", "icon_viewPort"]);
            $leftArrow->addAttribute("style", "top: 0px;left: -100px;display: block;");
            $leftArrowImg = new Div();
            $leftArrowImg->addStyleClass("icon visible");
            $leftArrowImg->addAttribute("style", "display: block;");
            $leftArrow->addChild($leftArrowImg);

            $rightArrow = new Div();
            $rightArrow->addStyleClasses(["gallery_right_arrow_bold", "icon_viewPort"]);
            $rightArrow->addAttribute("style", "top: 0px;right: -100px;");
            $rightArrowImg = new Div();
            $rightArrowImg->addStyleClass("icon visible");
            $rightArrowImg->addAttribute("style", "display: block;");
            $rightArrow->addChild($rightArrowImg);
            $items->addChildren($leftArrow, $rightArrow);
        }*/
        $itemsFullSizeContainer->addAttribute("style", "width: ".($newsCount * 1050)."px; height: 400px;");
        return $mainDiv->addChildren($head, $itemsBlock);
    }

    private function getNewsItem($dbRow) {
        $mainDiv = new Div();
        $mainDiv->addStyleClasses(["news_item", "main_page_item"]);
        $newsItemSubContainer = new Div();
        $newsItemSubContainer->addStyleClass("main_page_item_sub");
        $mainDiv->addChildren($newsItemSubContainer);
        if (!is_null($dbRow[DB::TABLE_NEWS__VIDEO]) && strlen($dbRow[DB::TABLE_NEWS__VIDEO]) > 0) {
            $video = new Div();
            $video->addStyleClass("news_video");
            $video->addChild($dbRow[DB::TABLE_NEWS__VIDEO]);
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

    private function renderPriceDownloadItems() {
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

    private function getTabSvg() {
        $mainTag = new Div();
        $mainTag->addAttribute("style", "display: none;");
        $mainTag->addChild('
            <svg height="0" width="0" style="position: absolute; margin-left: -100%;">
                <defs>
                    <filter id="shadow">
                        <feComponentTransfer in="SourceGraphic">
                            <feFuncR type="discrete" tableValues="0"></feFuncR>
                            <feFuncG type="discrete" tableValues="0"></feFuncG>
                            <feFuncB type="discrete" tableValues="0"></feFuncB>
                        </feComponentTransfer>
                        <feGaussianBlur stdDeviation="1"></feGaussianBlur>
                        <feComponentTransfer>
                            <feFuncA type="linear" slope="0.2"></feFuncA>
                        </feComponentTransfer>
                        <feOffset dx="5" dy="1" result="shadow"></feOffset>
                        <feComposite in="SourceGraphic"></feComposite>
                    </filter>

                    <linearGradient id="tab-1-bg" x1="0%" y1="0%" x2="0%" y2="65%">
                        <stop offset="0%" style="stop-color: rgba(136, 195, 229, 1.0);"></stop>
                        <stop offset="100%" style="stop-color: rgba(118, 160, 192, 1.0);"></stop>
                    </linearGradient>

                    <linearGradient id="tab-2-bg" x1="0%" y1="0%" x2="0%" y2="65%">
                        <stop offset="0%" style="stop-color: rgba(149, 190, 233, 1.0);"></stop>
                        <stop offset="100%" style="stop-color: rgba(112, 153, 213, 1.0);"></stop>
                    </linearGradient>

                    <linearGradient id="tab-3-bg" x1="0%" y1="0%" x2="0%" y2="65%">
                        <stop offset="0%" style="stop-color: rgba(61, 149, 218, 1.0);"></stop>
                        <stop offset="100%" style="stop-color: rgba(43, 130, 197, 1.0);"></stop>
                    </linearGradient>

                    <linearGradient id="tab-4-bg" x1="0%" y1="0%" x2="0%" y2="65%">
                        <stop offset="0%" style="stop-color: rgba(72, 204, 243, 1.0);"></stop>
                        <stop offset="100%" style="stop-color: rgba(71, 194, 243, 1.0);"></stop>
                    </linearGradient>
                </defs>
                <path id="tab-shape" class="tab-shape" d="M116.486,29.036c-23.582-8-14.821-29-42.018-29h-62.4C5.441,0.036,0,5.376,0,12.003v28.033h122v-11H116.486z"></path>
                <path id="slogan-shape" class="slogan-shape" d="M116.486, 29.036c-23.582-8-14.821-29-42.018-29h-62.4C5.441, 0.036, 0, 5.376, 0, 12.003v28.033h122v-11H116.486z"></path>
            </svg>
        ');
        return $mainTag;
    }

    private function getStyledTextList($textList, $styles) {
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