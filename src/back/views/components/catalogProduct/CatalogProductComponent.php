<?php

include_once AuWebRoot . '/src/back/views/components/AbstractComponent.php';

class CatalogProductComponent extends AbstractComponent
{
    private $product;
    private $catalogPath;

    function __construct($product, $catalogPath)
    {
        parent::__construct();
        $this->product = $product;
        $this->catalogPath = $catalogPath;
    }

    public function build()
    {
        $code = $this->product[DB::TABLE_GOODS__KEY_ITEM];
        $imagesCodes = json_decode(isset($this->product[DB::TABLE_GOODS__IMAGES]) ? $this->product[DB::TABLE_GOODS__IMAGES] : "[]");
        $images = ProductsUtils::normalizeImagesFromCodes($imagesCodes, $code, Constants::MEDIUM_IMAGE, $this->catalogPath);
        $tpl = parent::getEngine()->loadTemplate('components/catalogProduct/catalog-product.mustache');
        return $tpl->render([
            'title' => $this->product[DB::TABLE_GOODS__NAME],
            'titleHtml' => self::getHtmlTitle($this->product[DB::TABLE_GOODS__NAME]),
            'imageSrc' => "/".addslashes($images[0]),
            'link' => URLBuilder::getCatalogLinkForSingleItem($code),
            'i18n' => Localization
        ]);
    }

    private function getHtmlTitle($title)
    {
        $replacement = 'Модель';
        $res_ = strripos($title, $replacement);
        if ($res_ !== false && $res_ > 0) {
            $value = substr($title, 0, $res_ - 1);
            return $value . '<br>' . substr($title, $res_, strlen($title) - 1);
        }
        return $title;
    }
}
