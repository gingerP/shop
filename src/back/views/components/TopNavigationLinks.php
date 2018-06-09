<?php
include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/pages.php';

class TopNavigationLinks
{

    public function getDOM($pageName)
    {
        $mainDiv = new Div();
        $mainDiv->addStyleClass("top_bottom_main_menu");
        $ul = new Ul();
        $mainDiv->addChild($ul);
        $ul->addStyleClass("top_catalog");
        for ($index = 0; $index < count(Labels::$TOP_NAVIGATION_KEYS); $index++) {
            $key = Labels::$TOP_NAVIGATION_KEYS[$index];
            $selectedPageStyle = "";
            if (strLen($pageName) == 0 && $key == "main"
                || ($pageName == "search" || $pageName == "singleItem") && $key == "catalog"
                || $pageName == $key && $key != "search"
            ) {
                $selectedPageStyle = "top_catalog_item_sel";
            }

            switch ($key) {
                case 'search':
                    $ul->addChild($this->createSearchLink($selectedPageStyle, Labels::$TOP_NAVIGATION_TITLE['search'], Labels::$TOP_NAVIGATION_LINKS['search']));
                    break;
                default:
                    $li = new Li();
                    $ul->addChild($li);
                    $li->updateId($key);
                    $li->addStyleClasses(
                        ["top_catalog_item"
                            , "button"
                            , $selectedPageStyle]);

                    $link = new A();
                    $li->addChild($link);
                    $li->addStyleClasses(["input_hover", strlen($selectedPageStyle) > 0 ? "input_hover_untouchable" : ""]);
                    $link->addStyleClasses(["f-17"]);
                    $text = new Span();
                    $text->addChild(Labels::$TOP_NAVIGATION_TITLE[Labels::$TOP_NAVIGATION_KEYS[$index]]);
                    $text->addStyleClass('top-navigation-text');
                    $link->addChild($text);
                    if ($key === 'main') {
                        $link->addChild(
                    '<svg class="top-navigation-icon" fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                                <path d="M0 0h24v24H0z" fill="none"/>
                            </svg>'
                        );
                    }
                    if ($key != "download" && $key != "mail" && $key != "entry") {
                        $link->addAttribute("href", Labels::$TOP_NAVIGATION_LINKS[Labels::$TOP_NAVIGATION_KEYS[$index]]);
                    }
            }
        }
        return $mainDiv;
    }

    private function createLeftEarBar()
    {
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

    private function createRightEarBar()
    {
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

    private function createSearchLink($selectedPageStyle, $title, $link)
    {
        $mainTag = new Li();
        $searchLabel = new Div();
        $searchInput = new Input();
        $mainTag->addStyleClass("");
        if (Utils::isIE()) {
            $mainTag->addChild($searchLabel);
        } else {
            $searchInput->addAttribute("placeholder", "Поиск по товарам...");
        }

        $mainTag->addStyleClasses(["search_button_container", $selectedPageStyle]);

        $searchInputContainer = new Div();
        $searchInputContainer->addStyleClass('search-input-container');

        $searchLabel->addChild($title);
        $searchLabel->updateId('search');
        $searchLabel->addStyleClasses(["search_label", "f-16", "float_left"]);
        $searchInput->addStyleClasses(["search_input", "f-16", "float_left"]);

        $closeButton = new Button();
        $closeButton->addChild('<svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="24" viewBox="0 0 24 24" width="24">
            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            <path d="M0 0h24v24H0z" fill="none"/>
        </svg>');
        $closeButton->addStyleClass('search_input-close');

        $buttonIconContent = '<svg xmlns="http://www.w3.org/2000/svg" fill="#ffffff" height="24" viewBox="0 0 24 24" width="24">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                            <path d="M0 0h24v24H0z" fill="none"/>
                        </svg>';
        $searchButtonMob = new Button();
        $searchButtonMob->addStyleClasses(["search-button-mob", "float_left"]);
        $searchButtonMob->addChild($buttonIconContent);

        $searchButtonDesk = new Button();
        $searchButtonDesk->addStyleClasses(["search-button-desk", "float_left"]);
        $searchButtonDesk->addChild($buttonIconContent);

        $searchInputContainer->addChildren($searchInput, $closeButton, $searchButtonMob, $searchButtonDesk);

        $mainTag->addChild($searchInputContainer);

        $searchResultPlaceholder = new Div();
        $searchResultPlaceholder->addStyleClass('search-result-placeholder');

        $blackout = new Div();
        $blackout->addStyleClass('search-blackout');

        $mainTag->addChildren($searchResultPlaceholder, $blackout);

        return $mainTag;
    }

    public static function createPriceListLink()
    {
        $pref = new DBPreferencesType();
        $pricesDir = $pref->getPreference(SettingsNames::PRICE_DIRECTORY)[DB::TABLE_PREFERENCES__VALUE];
        $prices = FileUtils::getFilesByDescription($pricesDir, 'xls');
        $descriptions = FileUtils::getFilesByDescription($pricesDir, 'txt');
        $mainTag = new Div();
        $mainTag->addStyleClass("download_table");
        for ($index = 0; $index < count($prices); $index++) {
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