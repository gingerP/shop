<?php
include_once AuWebRoot . '/src/back/import/import.php';
include_once AuWebRoot . '/src/back/import/db.php';
include_once AuWebRoot . '/src/back/views/components/products/ProductComponent.php';

const PREVIEW_IMAGE_FULL_WIDTH = 150;

class ProductPage extends APagesCreator
{
    private $productCode;

    public function __construct(&$request)
    {
        parent::__construct(UrlParameters::PAGE__PRODUCTS);
        $this->productCode = $request->param('productCode', '');
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
        $product = new ProductComponent($this->productCode);
        return $product->build();
        //throw new ProductNotFoundError("Product '$itemId' not found.");
    }

    private function getProductDescription($description)
    {
        $mainTag = new Div();
        $mainTag->addStyleClass("description");
        $descriptionArray = Utils::getDescriptionArray($description);

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

    protected function getSourceScripts()
    {
        $scripts = parent::getSourceScripts();
        if ($this->isJsUglify) {
            return $scripts . '
                <script type="text/javascript" src="/dist/vendor1.js"></script>
                <script type="text/javascript" src="/dist/vendor2.js"></script>
                <script type="text/javascript" src="/dist/bundle1.js"></script>
                <script type="text/javascript" src="/dist/bundle2.js"></script>
            ';
        }
        return $scripts . '
            <script type="text/javascript" src="/src/front/js/utils.js"></script>
            <script type="text/javascript" src="/src/front/js/components/core/image-zoom/image-zoom.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/core/images-gallery/images-gallery.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/products/product.component.js"></script>
        ';
    }
}
