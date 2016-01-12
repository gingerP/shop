<?php
include_once("import");
include_once("db");

class SingleItemPage extends APagesCreator{

    public function SingleItemPage() {
        $this->APagesCreator();
        $this->setPageCode("single_item_page");
        $this->setIsStatusBarVisible(true);
        $this->setIsTreeVisible(true);
        $this->setIsViewModeBlockVisible(false);
        $this->setPathLinkForMainBlock(PathLinks::getDOMForSingleItemPage());

        $treeView = new TreeView();
        $treeLabels = implode(", ", $treeView->getAllLabels());
        $metaKeyWords = new Meta();
        $metaKeyWords->addAttributes([
            "name" => "keywords",
            "content" => $treeLabels
        ]);

        $this->addMetaTags($metaKeyWords);

        $html = $this->getHtml();
        echo $html;
    }

    protected function createGeneralContent() {
        $mainTag = new Div();
        $goods = new DBGoodsType();
        $itemId = Utils::getFromGET("page_id");
        $goods->executeRequest(DB::TABLE_GOODS__KEY_ITEM, $itemId, DB::TABLE_GOODS___ORDER,DB::ASC);
        $response = $goods->getResponse();
        $mainTag->addStyleClasses(["single_item"]);
        $row = mysql_fetch_array($response);
        if ($row) {
            $imagePathes = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH.DIRECTORY_SEPARATOR.$itemId.DIRECTORY_SEPARATOR, Constants::SMALL_IMAGE, 'jpg');
            $filesSmall = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH.DIRECTORY_SEPARATOR.$itemId.DIRECTORY_SEPARATOR, Constants::SMALL_IMAGE, "jpg");
            $filesMedium = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH.DIRECTORY_SEPARATOR.$itemId.DIRECTORY_SEPARATOR, Constants::MEDIUM_IMAGE, "jpg");
            $firstImage = FileUtils::getFirstFileInDirectoryByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH.DIRECTORY_SEPARATOR.$itemId.DIRECTORY_SEPARATOR, Constants::MEDIUM_IMAGE, 'jpg');
            if ($firstImage == '') {
                $firstImage = FileUtils::getCapImage(Labels::CAP_IMAGE_FOR_CLOTHING);
            }
            sort($imagePathes);
            sort($filesSmall);
            sort($filesMedium);

            $defaultBackgroundImage = $this->getFirstImage($itemId, Constants::MEDIUM_IMAGE);

            $titleBlock = new Strong();
            $titleBlock->addStyleClasses(["title", "f-30"]);
            $titleBlock->addChild($row[DB::TABLE_GOODS__NAME]);
            $this->updateTitleTagChildren([$row[DB::TABLE_GOODS__NAME].' - ']);

            $metaDesc = new Meta();
            $metaDesc->addAttributes([
                "name" => "description",
                "content" => "на этой странице Вы найдете подробное описание для товара ".$row[DB::TABLE_GOODS__NAME].", а также сможете пролистать фотографии и просмотреть их увеличенную версию"
            ]);
            $this->addMetaTags($metaDesc);

            $infoBlock = new Div();
            $infoBlock->addStyleClass("info");
            $leftBlock = new Div();
            $leftBlock->addStyleClasses(["left_block"]);
            $bigImg = new Div();
            $bigImg->addStyleClasses(["big_img", "float_left"]);
            $bigImg->addAttribute("style", "background-image: url($defaultBackgroundImage);filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$defaultBackgroundImage',sizingMethod='scale');-ms-filter: \"progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$defaultBackgroundImage',sizingMethod='scale')\";");
            $square = new Div();
            $square->addStyleClass("squareX");
                $zoom = new Div();
                $zoom->addStyleClass("zoom_image");
                $zoom->addAttribute("style", "background-image: url(.$defaultBackgroundImage.)");
                $img1 = new Img();
                $img1->addAttribute("src", $firstImage);
                $img1->updateId("main_gallery_image");
                $img2 = new Img();
                $img2->addAttribute("src", $firstImage);
                $img2->updateId("img_effect");
            $square->addChildList([$zoom, $img1, $img2]);
            $square->addChildList($this->getImageSwitcher());
            $leftBlock->addChildList([$square, $this->getPreviewImages($filesMedium, Constants::MEDIUM_IMAGE."images", false)]);


            $rightBlock = new Div();
            $rightBlock->addStyleClasses(["right_block"]);
            $overviewImgs = $this->getPreviewImages($filesSmall, Constants::SMALL_IMAGE."images", true);
            $overviewImgs->updateId("gallery");
            $overviewImgs->addStyleClass("w-100p");
            $rightBlock->addChildList([$overviewImgs]);
            $index = 0;
            $imgList = new Div();
            $imgList->addStyleClass("scroll_child");
            /*foreach($imagePathes as $imagePath) {
                if (count(trim($imagePath)) != 0) {
                    $index++;
                    $activeImg = '';
                    $cursor = 'review';
                    if ($index == 1) {
                        $active = 'preview_image_current_border';
                        $activeImg = 'img_to_front';
                    } else {
                        $active = "preview_image_hover_border";
                        $cursor = 'cursor_pointer';
                    }
                    $imgItem = new Div();
                    $imgItem->addStyleClasses(["image_item", "float_left", $cursor]);
                    $zoomWindowContainer = new Div();
                    $zoomWindowContainer->addStyleClass("zoom_window_container");
                    $zoomWindow = new Div();
                    $zoomWindow->addStyleClass("zoom_window");
                    $zoomWindowContainer->addChild($zoomWindow);
                    $previewImgBorder = new Div();
                    $previewImgBorder->addStyleClasses("preview_image_border", $active);
                    $previewImage = new Img();
                    $previewImage->addAttribute("src", $imagePath);
                    $previewImage->addStyleClasses(["preview_image", $activeImg]);
                    $imgItem->addChildList([$zoomWindowContainer, $previewImgBorder, $previewImage]);
                }
            }*/
        }
        $description = $this->getImageDescription($row[DB::TABLE_GOODS__DESCRIPTION]);
        return $mainTag->addChildList([$titleBlock, $infoBlock->addChildList([$leftBlock, $rightBlock, $description])]);
    }

    private function getImageDescription($description) {
        $mainTag = new Div();
        $mainTag->addStyleClass("description");
        $descriptionArray = Utils::getDescriptionArray($description);

/*        $headerDOM = new Div();
        $headerDOM->addStyleClasses(["header", "f-20"]);
        $headerDOM->addChild("Детали");
        $mainTag->addChild($headerDOM);*/
        function filter($val) {
            return strlen($val) != 0;
        }
        //main_description
        if (strlen($descriptionArray[DescriptionKeys::$keys[Constants::DEFAULT_ITEM_DESCRIPTION_KEY]]) != 0) {
            $value = $descriptionArray[DescriptionKeys::$keys[Constants::DEFAULT_ITEM_DESCRIPTION_KEY]];
            $valueArray = array_filter(explode(";", $value), "filter");
            $valueDOM = TagUtils::createList($valueArray);
            $valueDOM->addStyleClasses(["description_value", "description_main_info", "f-15"]);
            $textDOM = new Div();
            $mainTag->addChild($textDOM->addChild($valueDOM));
            unset($descriptionArray[DescriptionKeys::$keys[Constants::DEFAULT_ITEM_DESCRIPTION_KEY]]);
        }
        //others fields
        $odd = true;
        foreach($descriptionArray as $key => $value) {
            if (!is_null($value) && strlen(trim($value)) > 0) {
                $valueArray = array_filter(explode(";", $value), "filter");
                $valueArray = Utils::arrayAppendToItem($valueArray, "<br>");
                $value = join("", $valueArray);
                $keyDOM = new Div();
                $keyDOM->addStyleClasses(["description_key", "f-15"]);
                $keyDOM->addChild($key);
                $valueDOM = new Div();
                $valueDOM->addStyleClasses(["description_value", "f-15"]);
                $valueDOM->addChild($value);
                $textDOM = new Div();
                $textDOM->addStyleClass("good_description_".($odd? "odd": "even"));
                $mainTag->addChild($textDOM->addChildList([$keyDOM, $valueDOM]));
                $odd = !$odd;
            }
        }
        return $mainTag;
    }

    private function getPreviewImages($images, $key, $display) {
        $mainTag = new Div();
        $mainTag->addStyleClasses([/*"scrollable", */"default-skin"]);
        $viewPort = new Div();
        $viewPort->addStyleClass("viewport");
        $overviewImages = new Div();
        $overviewImages->addStyleClasses([$key, "overview"]);
        if (!$display) {
            $overviewImages->addAttribute("style", "display:none;");
        }
        foreach($images as $image) {
            $imageContainer = new Div();
            $imageContainer->addStyleClasses(["blackout", "image_preview"]);
            $img = new Img();
            $img->addAttribute("src", $image);
            $overviewImages->addChild($imageContainer->addChild($img));
        }
        if (count($images) == 0) {
            $mainTag->addAttribute('style', 'display: none;');
        }
        return $mainTag->addChild($viewPort->addChild($overviewImages));
    }

    private function getFirstImage($itemId, $size) {
        $fileList = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH.DIRECTORY_SEPARATOR.$itemId.DIRECTORY_SEPARATOR, $size, 'jpg');
        if (count($fileList) > 0) {
            return $fileList[0];
        }
        return "";
    }

    private function getImageSwitcher() {
        $leftArrow = new Div();
        $leftArrow->addStyleClasses(["gallery_left_arrow", "icon_viewPort"]);
        $leftArrowImg = new Div();
        $leftArrowImg->addStyleClass("icon");
        $leftArrow->addChild($leftArrowImg);
        $rightArrow = new Div();
        $rightArrow->addStyleClasses(["gallery_right_arrow", "icon_viewPort"]);
        $rightArrowImg = new Div();
        $rightArrowImg->addStyleClass("icon");
        $rightArrow->addChild($rightArrowImg);
        return [$leftArrow, $rightArrow];
    }

    private function getItemInfo($name, $description) {
        $mainTag = new Div();
        $itemTitle = new Div();
        $itemTitle->addStyleClasses(["title", "font_arial"]);
        $itemTitle->addChild($name);
        $itemDescription = new Div();
        $itemDescription->addStyleClasses(["title", "font_arial"]);
        $itemDescription->addChild($description);
        return $mainTag->addChildList([$itemTitle, $itemDescription]);
    }

}
