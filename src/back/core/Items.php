<?php
include_once("src/back/import/import");
include_once("src/back/import/db");
include_once("src/back/import/page");

class Items {

    public function getItemsTable($pageNumber, $num, $products, $key, $valueToSearch) {
        $mainTag = new Div();
        $tdNum = 3;
        $indOnPage = 0;
        $items = 0;
        $td = 0;
        $rowIndex = 1;

        if (count($products) > 0) {
            $mainTag->addStyleClass("items_table");
            $rowView = new Div();
            $mainTag->addChild($rowView);
            while ($product = array_shift($products)) {
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
                $rowView->addStyleClass("metro");
                $cellView = new A();
                $rowView->addChild($cellView);
                $keyItem = $product["key_item"];
                $images = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH.DIRECTORY_SEPARATOR.$keyItem.DIRECTORY_SEPARATOR, Constants::MEDIUM_IMAGE, 'jpg');

                if (count($images) == 0) {
                    $capImage = FileUtils::getCapImage(Labels::CAP_IMAGE_FOR_CLOTHING);
                    $images =  [$capImage];
                }
                $item = null;
                $info = Item::getMetroItemView(
                    $product["name"],
                    $images,
                    $product[DB::TABLE_GOODS__VERSION],
                    Utils::formatClotheTitle($product["name"])
                );
                $item = $info[0];
                $itemInfo = $info[1];

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

