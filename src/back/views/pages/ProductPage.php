<?php
include_once AuWebRoot . '/src/back/import/import.php';
include_once AuWebRoot . '/src/back/import/db.php';
include_once AuWebRoot . '/src/back/views/components/products/ProductComponent.php';
include_once AuWebRoot . '/src/back/views/components/productPath/ProductPathComponent.php';

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
        $product = new ProductComponent($this->productCode);
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
