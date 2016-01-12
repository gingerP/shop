<?php
include_once("import");
include_once("page");

class TopNavigationLinks {

    public function getDOM() {
        $pageName = Utils::getFromGET('page_name');
        $mainDiv = new Div();
        $mainDiv->addStyleClass("top_bottom_main_menu");
        $mainDiv->addChild(self::createLeftEarBar());
        $ul = new Ul();
        $mainDiv->addChild($ul);
        $ul->addStyleClass("top_catalog");
        for($index = 0; $index < count(Labels::$TOP_NAVIGATION_KEYS); $index++){
            $key = Labels::$TOP_NAVIGATION_KEYS[$index];
            $selectedPageStyle = "";
            if (strLen($pageName) == 0 && $key == "main"
                || ($pageName == "search" || $pageName == "singleItem") && $key == "catalog"
                || $pageName == $key && $key != "search") {
                $selectedPageStyle = "top_catalog_item_sel";
            }

            switch($key) {
                case 'search':
                    $ul->addChild($this->createSearchLink($selectedPageStyle, Labels::$TOP_NAVIGATION_TITLE['search'], Labels::$TOP_NAVIGATION_LINKS['search']));
                    break;
                default:
                    $li = new Li();
                    $ul->addChild($li);
                    $li->updateId($key);
                    $li->addStyleClasses(
                        [ "top_catalog_item"
                            , "button"
                            , $selectedPageStyle]);

                    $link = new A();
                    $li->addChild($link);
                    $li->addStyleClasses(["input_hover", strlen($selectedPageStyle) > 0? "input_hover_untouchable": ""]);
                    $link->addStyleClasses(["f-17"]);
                    $link->addChild(Labels::$TOP_NAVIGATION_TITLE[Labels::$TOP_NAVIGATION_KEYS[$index]]);
                    if ($key != "download" && $key != "mail" && $key != "entry") {
                        $link->addAttribute("href", Labels::$TOP_NAVIGATION_LINKS[Labels::$TOP_NAVIGATION_KEYS[$index]]);
                    }
            }
        }
        $mainDiv->addChild(self::createRightEarBar());
        return $mainDiv;
    }

    private function createLeftEarBar() {
        $svg = new Svg();
        $earRect = new Polygon();
        $earTriangl = new Polygon();
        $svg->addChild($earRect);
        $svg->addChild($earTriangl);
        $mainSpan = new Span();
        $mainSpan->addChild($svg);
        $mainSpan->addStyleClass("left_ear");
        $earTriangl->addAttribute("points", "0,10   10,10  10,0");
        $earTriangl->addAttribute("style", "stroke:#ade681; fill:#ade681; stroke-width: 1;");
        $earRect->addAttribute("points", "0,10   10,10  10,40 0,40");
        $earRect->addAttribute("style", "stroke:#88cc55; fill:#88cc55; stroke-width: 1;");
        return $mainSpan;
    }

    private function createRightEarBar() {
        $svg = new Svg();
        $earRect = new Polygon();
        $earTriangl = new Polygon();
        $svg->addChild($earRect);
        $svg->addChild($earTriangl);
        $mainSpan = new Span();
        $mainSpan->addChild($svg);
        $mainSpan->addStyleClass("right_ear");
        $earTriangl->addAttribute("points", "0,0   0,10  10,10");
        $earTriangl->addAttribute("style", "stroke:#ade681; fill:#ade681; stroke-width: 1;");
        $earRect->addAttribute("points", "0,10   10,10  10,40 0,40");
        $earRect->addAttribute("style", "stroke:#88cc55; fill:#88cc55; stroke-width: 1;");
        return $mainSpan;
    }

    private function createSearchLink($selectedPageStyle, $title, $link) {
        $mainTag = new Li();
        $searchLabel = new Div();
        $searchInput = new Input();
        $searchButton = new Div();
        $mainTag->addStyleClass("");
        if (Utils::isIE()) {
            $mainTag->addChild($searchLabel);
        } else {
            $searchInput->addAttribute("placeholder", "Поиск по товарам...");
        }
        $mainTag->addChild($searchInput);
        $mainTag->addChild($searchButton);
        $mainTag->addStyleClasses(["search_button_container", $selectedPageStyle]);
        $searchLabel->addChild($title);
        $searchLabel->updateId('search');
        $searchLabel->addStyleClasses(["search_label", "f-16", "float_left"]);
        $searchInput->addStyleClasses(["search_input", "f-16", "float_left"]);
        $searchButton->addStyleClasses(["search_button", "float_left"]);

        return $mainTag;
    }

    public static function createPriceListLink() {
        $pref = new DBPreferencesType();
        $pricesDir = $pref->getPreference(Constants::PRICE_DIRECTORY)[DB::TABLE_PREFERENCES__VALUE];
        $prices = FileUtils::getFilesByDescription($pricesDir, 'xls');
        $descriptions = FileUtils::getFilesByDescription($pricesDir, 'txt');
        $mainTag = new Div();
        $mainTag->addStyleClass("download_table");
        for($index = 0; $index < count($prices); $index++) {
            $priceItem = new Div();
            $priceIcon = new Div();
            $priceIcon->addStyleClasses(["price_icon", "float_left"]);
            $priceText = new Div();
            $priceText->addStyleClass(["download_description", "float_left"]);
            $priceText->addChild(file_get_contents($descriptions[$index]));

            $priceItem->addChildList([$priceIcon, $priceText]);
            $mainTag->addChild($priceItem);
        }


        return $mainTag;
    }
}

?>