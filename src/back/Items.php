<?php
include_once("import");
include_once("db");
include_once("page");

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
        if ($response != 0) {
            $mainTag->addStyleClass("items_table");
            $rowView = new Div();
            $mainTag->addChild($rowView);
            while ($row = mysql_fetch_array($response)) {
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
                $cellView = new Div();
                $rowView->addChild($cellView);
                $keyItem = $row["key_item"];
                $images = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH.DIRECTORY_SEPARATOR.$keyItem.DIRECTORY_SEPARATOR, Constants::MEDIUM_IMAGE, 'jpg');

                if (count($images) == 0) {
                    $capImage = FileUtils::getCapImage(Labels::CAP_IMAGE_FOR_CLOTHING);
                    $images =  [$capImage];
                }
                $item = null;
                if ($isMetro) {
                    $item = Item::getMetroItemView(
                        $row["name"],
                        $images,
                        $row["key_item"],
                        $pageNumber,
                        $num,
                        $key,
                        $valueToSearch,
                        $row['god_type'],
                        Utils::formatClotheTitle($row["name"]),
                        $highLightId == $row["key_item"]
                    );
                } elseif ($isCompact) {
                    //$name, $images, $itemId, $pageNumber, $num, $key, $valueToSearch, $type, $trimName, $isHighLightElement
                    $item = Item::getCompactItemView(
                        $row["name"],
                        $images,
                        $row["key_item"],
                        $pageNumber,
                        $num,
                        $key,
                        $valueToSearch,
                        $row['god_type'],
                        Utils::formatClotheTitle($row["name"]),
                        $highLightId == $row["key_item"]
                    );
                } elseif ($isExtend) {
                    $item = Item::getSquareItemView(
                        $row["name"],
                        $images,
                        $row["key_item"],
                        $pageNumber,
                        $num,
                        $key,
                        $valueToSearch,
                        $row['god_type'],
                        Utils::formatClotheTitle($row["name"])
                    );
                } elseif ($isList) {
                    $item = Item::getLineItemView(
                        $row["name"],
                        $images,
                        $row["key_item"],
                        $pageNumber,
                        $num,
                        $key,
                        $valueToSearch,
                        $row['god_type'],
                        Utils::trimFormatClotheTitle($row["name"]),
                        $highLightId == $row["key_item"]
                    );
                }

                $cellView->addChild($item);
            }

        }
        return $mainTag;
    }
}
?>

