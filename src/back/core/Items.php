<?php
include_once("src/back/import/import");
include_once("src/back/import/db");
include_once("src/back/import/page");

class Items {

    public function getItemsTable($pageNumber, $num, $response, $key, $valueToSearch) {
        $mainTag = new Div();
        $tdNum = 3;
        $tdNumTotal = $tdNum * 2 - 1;
        $indOnPage = 0;
        $items = 0;
        $td = 0;
        $item = new Item;
        $rowIndex = 1;
        $tdHeight = Utils::isSquareViewMode() ? 300 : 30;
        $highLightId = Utils::getFromGET(UrlParameters::HIGH_LIGHT_ELEMENT);

        $isMetro = array_key_exists(UrlParameters::VIEW_MODE, $_GET) && Utils::getFromGET(UrlParameters::VIEW_MODE) == "metro" || !array_key_exists(UrlParameters::VIEW_MODE, $_GET);
        $isCompact = array_key_exists(UrlParameters::VIEW_MODE, $_GET) && Utils::getFromGET(UrlParameters::VIEW_MODE) == "compact";
        $isExtend = array_key_exists(UrlParameters::VIEW_MODE, $_GET) && Utils::getFromGET(UrlParameters::VIEW_MODE) == "extend";
        $isList = array_key_exists(UrlParameters::VIEW_MODE, $_GET) && Utils::getFromGET(UrlParameters::VIEW_MODE) == "list";
        $rowViewClass = $isCompact? 'compact': ($isMetro? "metro": ($isExtend? "extend": ($isList? "list": "list")));
        if ($response->num_rows !== 0) {
            $mainTag->addStyleClass("items_table");
            $rowView = new Div();
            $mainTag->addChild($rowView);
            while ($product = mysqli_fetch_array($response)) {
                $items++;
                $indOnPage++;
                $td++;
                if (ceil(fmod($indOnPage, $tdNum)) == 1) {
                    $rowIndex++;
                    $indOnPage = 1;
                    $td = 1;
                } elseif (Utils::isEven($td)) {
                    $td++;
                }
                $rowView->addStyleClass($rowViewClass);
                $cellView = new A();
                $rowView->addChild($cellView);
                $keyItem = $product["key_item"];
                $images = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH.DIRECTORY_SEPARATOR.$keyItem.DIRECTORY_SEPARATOR, Constants::MEDIUM_IMAGE, 'jpg');

                if (count($images) == 0) {
                    $capImage = FileUtils::getCapImage(Labels::CAP_IMAGE_FOR_CLOTHING);
                    $images =  [$capImage];
                }
                $item = null;
                $itemInfo = [];
                if ($isMetro) {
                    $info = Item::getMetroItemView(
                        $product["name"],
                        $images,
                        $product[DB::TABLE_GOODS__VERSION],
                        Utils::formatClotheTitle($product["name"])
                    );
                    $item = $info[0];
                    $itemInfo = $info[1];
                } elseif ($isCompact) {
                    //$name, $images, $itemId, $pageNumber, $num, $key, $valueToSearch, $type, $trimName, $isHighLightElement
                    $info = Item::getCompactItemView(
                        $product["name"],
                        $images,
                        $product["key_item"],
                        $pageNumber,
                        $num,
                        $key,
                        $valueToSearch,
                        $product['god_type'],
                        Utils::formatClotheTitle($product["name"]),
                        $highLightId == $product["key_item"]
                    );
                    $item = $info[0];
                    $itemInfo = $info[1];
                } elseif ($isExtend) {
                    $info = Item::getSquareItemView(
                        $product["name"],
                        $images,
                        $product["key_item"],
                        $pageNumber,
                        $num,
                        $key,
                        $valueToSearch,
                        $product['god_type'],
                        Utils::formatClotheTitle($product["name"])
                    );
                    $item = $info[0];
                    $itemInfo = $info[1];
                } elseif ($isList) {
                    $info = Item::getLineItemView(
                        $product["name"],
                        $images,
                        $product["key_item"],
                        $pageNumber,
                        $num,
                        $key,
                        $valueToSearch,
                        $product['god_type'],
                        Utils::trimFormatClotheTitle($product["name"]),
                        $highLightId == $product["key_item"]
                    );
                    $item = $info[0];
                    $itemInfo = $info[1];
                }

                $cellView->addChild($item);
                $cellView->addStyleClass("catalog_good_item");

                if (array_key_exists('previews_cols_num', $itemInfo)) {
                    if ($itemInfo['previews_cols_num'] == 0) {
                        $cellView->addStyleClass('previews-zero-col');
                    } else if ($itemInfo['previews_cols_num'] == 1) {
                        $cellView->addStyleClass('previews-single-col');
                    } else if ($itemInfo['previews_cols_num'] == 2) {
                        $cellView->addStyleClass('previews-double-col');
                    }
                }
                $url = URLBuilder::getCatalogLinkForSingleItem($product["key_item"], $pageNumber, $num, array(
                        UrlParameters::KEY => $key,
                        UrlParameters::SEARCH_VALUE => $valueToSearch
                    )
                );
                $cellView->addAttribute('href', $url);
            }

        }
        return $mainTag;
    }
}
?>

