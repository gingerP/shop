<?php
include_once("src/back/import/import");

class Item {

    public static function getMetroItemView($name, $images, $itemId, $pageNumber, $num, $key, $valueToSearch, $type, $trimName) {
        $previewsColsNum = 2;
        $previewsNum = 0;
        $blackOut = new Div();
        //$blackOut->addStyleClasses(["blackout", "catalog_item_button_container"]);
        $blackOut->addAttributes([
            "itemscope" => "",
            "itemtype"=> "http://data-vocabulary.org/Product"
        ]);
        $mainDiv = new Div();
        $blackOut->addChild($mainDiv);
        TagUtils::createShadow($mainDiv);
        $mainDiv->addStyleClasses(["simple_item_metro", "ciic"]);
        $imagesCount = min(count($images), Constants::MAX_IMAGE_COUNT_METRO_VIEW);
        $indexToSwitchRow = $imagesCount > 2? floor(($imagesCount -1) / 2) + 1: -1;

        $row0 = new Div();
        $row0->addStyleClass("images_row");
        $row1 = new Div();
        $row1->addStyleClasses(["images_row_last"]);
        $totalCount = count($images);
        if ($totalCount > 3) {
            array_splice($images, 3);
        } else if ($totalCount == 2) {
            $images = [$images[0]];
        }
        $totalCount = count($images);
        for ($imgIndex = 0; $imgIndex < $totalCount; $imgIndex++) {
            if ($imgIndex == $imagesCount) {
                break;
            }
            $imgView = new Img();
            $imgView->addAttributes(
                [
                    "itemprop" => "image",
                    TagLabels::ON_CLICK => "openSimpleImg(arguments[0])",
                    "src" => "/".addslashes($images[$imgIndex]),
                    "alt" => $name
                ]);
            $imgView->addStyleClass($imgIndex > 0? "simple_item_image_half": "simple_item_image");
            if ($imgIndex == 0) {
                $mainDiv->addChild($imgView);
                break;
            } else {
                $row0->addChild($imgView);
                $previewsNum++;
            }
        }

        $blackoutContainer = new Div();
        $blackoutContainer->addStyleClass('blackout_container');
        $blackOut->addChild($blackoutContainer);

        $mainDiv->addChild($row0);

            $url = URLBuilder::getCatalogLinkForSingleItem($itemId, $pageNumber, $num, array(
                    UrlParameters::KEY => $key,
                    UrlParameters::SEARCH_VALUE => $valueToSearch
                )
            );
            $mainDiv->addStyleClass("cursor_pointer");
            $link = TagUtils::createNote($trimName, "");
            $link->addAttribute("itemprop", "name");
            $blackOut->addChild($link);
            $blackOut->addChild(self::getItemButton($url));





        if ($previewsNum == 0) {
            $previewsColsNum = 0;
        } else if ($previewsNum == 1 || $previewsNum == 2) {
            $previewsColsNum = 1;
        }

        return [
            $blackOut,
            [
                'previews_cols_num' => $previewsColsNum,
                'previews_num' => $previewsNum
            ]
        ];
    }

    public static function getLineItemView($name, $images, $itemId, $pageNumber, $num, $key, $valueToSearch, $type, $trimName, $isHighLightElement) {
        $blackOut = new Div();
        $blackOut->addStyleClasses(["blackout", "catalog_item_button_container"]);
        $blackOut->addAttributes([
            "itemscope" => "",
            "itemtype"=> "http://data-vocabulary.org/Product"
        ]);

        $mainDiv = new Div();
        $blackOut->addChild($mainDiv);
        TagUtils::createShadow($mainDiv);
        $mainDiv->addStyleClasses(["simple_item_list", $isHighLightElement? Constants::HIGH_LIGHT_ELEMENT: "", "ciic"]);
        if ($type == 'HARD') {
            $link = TagUtils::createNote(
                $trimName,
                URLBuilder::getCatalogLinkForSingleItem($itemId, $pageNumber, $num, array(
                    UrlParameters::KEY => $key,
                    UrlParameters::SEARCH_VALUE => $valueToSearch
                    )
                )
            );
            $blackOut->addChild($link);
        } elseif ($type == 'SIMPLE') {
            $text = TagUtils::createNote($trimName, "");
            $blackOut->addChild($text);
        }
        return [$blackOut, []];
    }

    public static function getSquareItemView($name, $images, $itemId, $pageNumber, $num, $key, $valueToSearch, $type, $trimName) {
        $blackOut = new Div();
        $blackOut->addStyleClasses(["blackout", "catalog_item_button_container"]);
        $blackOut->addAttributes([
            "itemscope" => "",
            "itemtype"=> "http://data-vocabulary.org/Product"
        ]);

        $mainDiv = new Div();
        $blackOut->addChild($mainDiv);
        TagUtils::createShadow($mainDiv);
        $mainDiv->addStyleClasses(["simple_item_extend", "ciic"]);
        $imgView = new Img();
        $mainDiv->addChild($imgView);
        $imgView->addAttributes(
            array(
                TagLabels::ON_CLICK => "openSimpleImg(arguments[0])",
                "src" => addslashes($images[0]),
                "alt" => $name
        ));
        $imgView->addStyleClass("simple_item_image");
        if ($type == 'HARD') {
            $url = URLBuilder::getCatalogLinkForSingleItem($itemId, $pageNumber, $num, array(
                    UrlParameters::KEY => $key,
                    UrlParameters::SEARCH_VALUE => $valueToSearch
                )
            );
            $mainDiv->addStyleClass("cursor_pointer");
            $link = TagUtils::createNote($trimName, "");
            $blackOut->addChild($link);
            $blackOut->addChild(self::getItemButton($url));
        } elseif ($type == 'SIMPLE') {
            $text = TagUtils::createNote($trimName, "");
            $blackOut->addChild($text);
        }
        return [$blackOut, []];
    }

    public static function getCompactItemView($name, $images, $itemId, $pageNumber, $num, $key, $valueToSearch, $type, $trimName, $isHighLightElement) {
        $blackOut = new Div();
        $blackOut->addStyleClasses(["blackout", "catalog_item_button_container"]);
        $blackOut->addAttributes([
            "itemscope" => "",
            "itemtype"=> "http://data-vocabulary.org/Product"
        ]);

        $mainDiv = new Div();
        $blackOut->addChild($mainDiv);
        TagUtils::createShadow($mainDiv);
        $mainDiv->addStyleClasses(["simple_item_compact", $isHighLightElement? Constants::HIGH_LIGHT_ELEMENT: ""]);
        $imagesContainer = new Div();
        $imagesContainer->addStyleClass("catalog_img_container");
        $mainDiv->addChild($imagesContainer);
        $index = 0;
        foreach($images as $image) {
            if ($index == 1) {
                break;
            }
            $imgView = new Img();
            $imgView->addAttributes(
                array(
                    "src" => addslashes($image),
                    "alt" => $name,
                    TagLabels::ON_CLICK => "openSimpleImg(arguments[0])"
                ));
            $imagesContainer->addChild($imgView);
            $index++;
        }

            $url = URLBuilder::getCatalogLinkForSingleItem($itemId, $pageNumber, $num, array(
                    UrlParameters::KEY => $key,
                    UrlParameters::SEARCH_VALUE => $valueToSearch
                )
            );
            $mainDiv->addStyleClass("cursor_pointer");
            $link = TagUtils::createNote($trimName, "");
            $blackOut->addChild($link);
            $blackOut->addChild(self::getItemButton($url));




        return [$blackOut, []];
    }

    public static function getItemButton($url) {
        $button = new Div();
        $button->addStyleClasses(["catalog_item_button", "f-17", "input_hover"]);
        $button->addChild("подробнее");
        $button->addAttribute("href", $url);
        return $button;
    }
}
