<?php
include_once AuWebRoot . '/src/back/import/import.php';
include_once AuWebRoot . '/src/back/import/db.php';
include_once AuWebRoot . '/src/back/views/components/products/ProductComponent.php';
include_once AuWebRoot . '/src/back/views/components/productPath/ProductPathComponent.php';
include_once AuWebRoot . '/src/back/tags/Meta.php';

const PREVIEW_IMAGE_FULL_WIDTH = 150;

class ProductPage extends AbstractPage
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

        $this->content = $this->getHtml();
    }

    protected function createGeneralContent()
    {
        $Products = new DBGoodsType();
        $productInfo = $Products->findByCode($this->productCode);
        $this->addMetaTags(
            (new Meta())->addAttributes(
                [
                    'name' => 'description',
                    'content' => $productInfo[DB::TABLE_GOODS__NAME]
                ]
            )
        );
        $product = new ProductComponent($productInfo);
        return $product->build();
    }

    public function createPathLinks()
    {
        $Products = new DBGoodsType();
        $product = $Products->findByCode($this->productCode);
        $path = new ProductPathComponent($product[DB::TABLE_GOODS__CATEGORY]);
        $this->updateTitleTagChildren($product[DB::TABLE_GOODS__NAME]);
        return $path->build(['path' => $path]);
    }

    protected function getSourceScripts()
    {
        $scripts = parent::getSourceScripts();
        if ($this->isJsUglify) {
            return $scripts . '<script type="text/javascript" src="/dist/product-page.js"></script>';
        }
        return $scripts . '
            <script type="text/javascript" src="/src/front/js/utils.js"></script>
            <script type="text/javascript" src="/src/front/js/components/core/image-zoom/image-zoom.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/core/images-gallery/images-gallery.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/products/product.component.js"></script>
        ';
    }
}
