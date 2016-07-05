<?php
include_once("import");

class Item {

    public static function getMetroItemView($name, $images, $itemId, $pageNumber, $num, $key, $valueToSearch, $type, $trimName, $isHighLightElement) {
        $blackOut = new Div();
        $blackOut->addStyleClasses(["blackout", "catalog_item_button_container"]);
        $blackOut->addAttributes([
            "itemscope" => "",
            "itemtype"=> "http://data-vocabulary.org/Product"
        ]);
        $mainDiv = new Div();
        $blackOut->addChild($mainDiv);
        TagUtils::createShadow($mainDiv);
        $mainDiv->addStyleClasses(["simple_item_metro", "ciic"]);
        $imageCount = min(count($images), Constants::MAX_IMAGE_COUNT_METRO_VIEW);
        $brPosition = $imageCount > 2? floor(($imageCount -1) / 2) + 1: -1;

        $row0 = new Div();
        $row0->addStyleClass("images_row");
        $row1 = new Div();
        $row1->addStyleClasses(["images_row_last"]);
        $rows = [$row0, $row1];
        for ($imgIndex = 0; $imgIndex < count($images); $imgIndex++) {
            if ($imgIndex == $imageCount) {
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
            } elseif ($imgIndex >= $brPosition) {
                $rows[0]->addChild($imgView);
            } else {
                $rows[1]->addChild($imgView);
            }
        }
        $mainDiv->addChildList($rows);
        if ($type == 'HARD') {
            $url = URLBuilder::getCatalogLinkForSingleItem($pageNumber, $num, $itemId, array(
                    UrlParameters::KEY => $key,
                    UrlParameters::SEARCH_VALUE => $valueToSearch
                )
            );
            $mainDiv->addStyleClass("cursor_pointer");
            $link = TagUtils::createNote($trimName, "");
            $link->addAttribute("itemprop", "name");
            $blackOut->addChild($link);
            $blackOut->addChild(self::getItemButton($url));
        } elseif ($type == 'SIMPLE') {
            $text = TagUtils::createNote($trimName, "");
            $mainDiv->addChild($text);
        }

        return $blackOut;
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
                URLBuilder::getCatalogLinkForSingleItem($pageNumber, $num, $itemId, array(
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
        return $blackOut;
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
            $url = URLBuilder::getCatalogLinkForSingleItem($pageNumber, $num, $itemId, array(
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
        return $blackOut;
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
        if ($type == 'HARD') {
            $url = URLBuilder::getCatalogLinkForSingleItem($pageNumber, $num, $itemId, array(
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
        return $blackOut;
    }

    public static function getItemButton($url) {
        $button = new A();
        $button->addStyleClasses(["catalog_item_button", "f-17", "input_hover"]);
        $button->addChild("подробнее");
        $button->addAttribute("href", $url);
        return $button;
    }
}
