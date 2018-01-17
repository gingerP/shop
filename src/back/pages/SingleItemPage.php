<?php
include_once("src/back/import/import");
include_once("src/back/import/db");

const PREVIEW_IMAGE_FULL_WIDTH = 150;

class SingleItemPage extends APagesCreator
{

    public function __construct()
    {
        parent::__construct();
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

        $this->content = $this->getHtml();
    }

    protected function createGeneralContent()
    {
        $mainTag = new Div();
        $goods = new DBGoodsType();
        $itemId = Utils::getFromGET("page_id");
        $goods->executeRequest(DB::TABLE_GOODS__KEY_ITEM, $itemId, DB::TABLE_GOODS___ORDER, DB::ASC);
        $response = $goods->getResponse();
        $mainTag->addStyleClasses(["single_item"]);
        $product = mysqli_fetch_array($response);
        if ($product) {
            $imagePathes = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR, Constants::SMALL_IMAGE, 'jpg');
            $filesSmall = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR, Constants::SMALL_IMAGE, "jpg");
            $filesMedium = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR, Constants::MEDIUM_IMAGE, "jpg");
            $firstImage = FileUtils::getFirstFileInDirectoryByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR, Constants::MEDIUM_IMAGE, 'jpg');
            if ($firstImage == '') {
                $firstImage = FileUtils::getCapImage(Labels::CAP_IMAGE_FOR_CLOTHING);
            }
            sort($imagePathes);
            sort($filesSmall);
            sort($filesMedium);

            $defaultBackgroundImage = $this->getFirstImage($itemId, Constants::MEDIUM_IMAGE);

            $titleBlock = new Strong();
            $titleBlock->addStyleClasses(["title", "f-30"]);
            $titleBlock->addChild($product[DB::TABLE_GOODS__NAME]);
            $this->updateTitleTagChildren([$product[DB::TABLE_GOODS__NAME] . ' - ']);

            $metaDesc = new Meta();
            $metaDesc->addAttributes([
                "name" => "description",
                "content" => "на этой странице Вы найдете подробное описание для товара " . $product[DB::TABLE_GOODS__NAME] . ", а также сможете пролистать фотографии и просмотреть их увеличенную версию"
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
            $imageIndexLabel = new Div();
            $imageIndexLabel->addStyleClass('image-gallery-position-label');
            $imageIndexLabel->addChild('1 / ' . count($imagePathes));
            $square->addChildList([$zoom, $img1, $img2, $imageIndexLabel]);
            $square->addChildList($this->getImageSwitcher());
            $leftBlock->addChildList([
                $square,
                $this->getPreviewImages(
                    $filesMedium,
                    Constants::MEDIUM_IMAGE . "images",
                    false,
                    $product[DB::TABLE_GOODS__VERSION]
                )
            ]);
            $rightBlock = new Div();
            $rightBlock->addStyleClasses(["right_block"]);
            $overviewImgs = $this->getPreviewImages(
                $filesSmall, Constants::SMALL_IMAGE . "images", true, $product[DB::TABLE_GOODS__VERSION]);
            $overviewImgs->updateId("gallery");
            $overviewImgs->addStyleClass("w-100p");
            $rightBlock->addChildList([$overviewImgs]);
            $imgList = new Div();
            $imgList->addStyleClass("scroll_child");

            $rightBlock->addChild($this->getProductDescription($product[DB::TABLE_GOODS__DESCRIPTION]));

            return $mainTag->addChildList([$titleBlock, $infoBlock->addChildList([$leftBlock, $rightBlock])]);
        }
        throw new ProductNotFoundError("Product '$itemId' not found.");
    }

    private function getProductDescription($description)
    {
        $mainTag = new Div();
        $mainTag->addStyleClass("description");
        $descriptionArray = Utils::getDescriptionArray($description);

        /*        $headerDOM = new Div();
                $headerDOM->addStyleClasses(["header", "f-20"]);
                $headerDOM->addChild("Детали");
                $mainTag->addChild($headerDOM);*/
        function filter($val)
        {
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
        foreach ($descriptionArray as $key => $value) {
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
                $textDOM->addStyleClass("good_description_" . ($odd ? "odd" : "even"));
                $mainTag->addChild($textDOM->addChildList([$keyDOM, $valueDOM]));
                $odd = !$odd;
            }
        }
        return $mainTag;
    }

    private function getPreviewImages($images, $key, $display, $version = 0)
    {
        $mainTag = new Div();
        $mainTag->addStyleClasses([/*"scrollable", */
            "default-skin"]);
        $viewPort = new Div();
        $viewPort->addStyleClass("viewport");
        $overviewImages = new Div();
        $overviewImages->addStyleClasses([$key, "overview"]);
        if (!$display) {
            $overviewImages->addAttribute("style", "display:none;");
        }
        foreach ($images as $image) {
            $imageContainer = new Div();
            $imageContainer->addStyleClasses(["blackout", "image_preview"]);
            $img = new Img();
            $img->addAttribute("src", Utils::normalizeAbsoluteImagePath($image, ['v' => $version]));
            $overviewImages->addChild($imageContainer->addChild($img));
        }
        if (count($images) == 0) {
            $mainTag->addAttribute('style', 'display: none;');
        } else {
            $fullInlineWidth = count($images) * PREVIEW_IMAGE_FULL_WIDTH;
            $viewPort->addAttribute('data-width', $fullInlineWidth);
        }
        return $mainTag->addChild($viewPort->addChild($overviewImages));
    }

    private function getFirstImage($itemId, $size)
    {
        $fileList = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR, $size, 'jpg');
        if (count($fileList) > 0) {
            return $fileList[0];
        }
        return "";
    }

    private function getImageSwitcher()
    {
        $leftArrow = new Div();
        $leftArrow->addStyleClasses(["gallery_left_arrow", "icon_viewPort"]);
        $leftArrowImg = new Div();
        $leftArrowImg->addStyleClass("icon");
        $leftArrowImg->addChild(
            '<svg fill="#414141" height="30" viewBox="0 0 24 24" width="30" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.41 16.09l-4.58-4.59 4.58-4.59L14 5.5l-6 6 6 6z"/>
                <path d="M0-.5h24v24H0z" fill="none"/>
            </svg>'
        );
        $rightArrow = new Div();
        $rightArrow->addStyleClasses(["gallery_right_arrow", "icon_viewPort"]);
        $rightArrowImg = new Div();
        $rightArrowImg->addStyleClass("icon");
        $rightArrowImg->addChild(
            '<svg fill="#414141" height="30" viewBox="0 0 24 24" width="30" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    <path d="M0 0h24v24H0z" fill="none"/>
            </svg>'
        );
        $leftArrow->addChild($leftArrowImg);
        $rightArrow->addChild($rightArrowImg);
        return [$leftArrow, $rightArrow];
    }

    private function getItemInfo($name, $description)
    {
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
