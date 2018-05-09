<?php

include_once AuWebRoot.'/src/back/views/components/AbstractComponent.php';
include_once AuWebRoot . '/src/back/views/components/emailForm/EmailFormComponent.php';

class ProductComponent extends AbstractComponent
{

    private $productCode;

    public function __construct($productCode)
    {
        parent::__construct();
        $this->productCode = $productCode;
    }

    public function build()
    {
        $Products = new DBGoodsType();
        $product = $Products->findByCode($this->productCode);
        $engine = parent::getEngine();
        $engine->getPartialsLoader()->setTemplate('emailForm', (new EmailFormComponent())->build());
        $tpl = $engine->loadTemplate('components/products/product.mustache');
        $productCode = $this->productCode;
        $imagesCodes = json_decode($product[DB::TABLE_GOODS__IMAGES], false);
        $imagesCatalogRoot = DBPreferencesType::getPreferenceValue(Constants::CATALOG_PATH);
        $preparedProduct = [
            'name' => $product[DB::TABLE_GOODS__NAME],
            'description' => $this->prepareDescription($product[DB::TABLE_GOODS__DESCRIPTION]),
            'images' => [],
            'imagesNum' => count($imagesCodes),
            'smallImagesTotalWidth' => count($imagesCodes) * 130,
            'viberChatUri' => urlencode(DBPreferencesType::getPreferenceValue(Constants::VIBER_CHAT_URI, '')),
            'viberPhoneNumber' => DBPreferencesType::getPreferenceValue(Constants::VIBER_PHONE_NUMBER, ''),
            'email' => DBPreferencesType::getPreferenceValue(Constants::FEEDBACK_MAIL, ''),
            'emailSubject' => Localization['product.contact.email.subject'],
            'i18n' => Localization
        ];

        foreach ($imagesCodes as $imageCode) {
            $preparedProduct['images'][] = [
                's' => '/' . $imagesCatalogRoot . '/' . $productCode . '/' . Constants::SMALL_IMAGE . $imageCode . '.jpg',
                'm' => '/' . $imagesCatalogRoot . '/' . $productCode . '/' . Constants::MEDIUM_IMAGE . $imageCode . '.jpg',
                'l' => '/' . $imagesCatalogRoot . '/' . $productCode . '/' . Constants::LARGE_IMAGE . $imageCode . '.jpg'
            ];
        }

        $preparedProduct['shouldPrintImages'] = count($preparedProduct['images']) > 1;
        $preparedProduct['topDescriptionClass'] = count($preparedProduct['images']) <= 1 ? 'no-border' : '';


        return $tpl->render($preparedProduct);
    }

    private function prepareDescription($descriptionString)
    {
        $descriptionObject = json_decode($descriptionString, true);
        $descriptionStructuredList = [];
        $odd = false;
        foreach ($descriptionObject as $key => $descValue) {
            if ($key != 'k_main' && $descValue != '' && !is_null($descValue)) {
                $odd = !$odd;
                $descriptionStructuredList[] = [
                    'odd' => $odd ? 'odd' : 'even',
                    'name' => array_key_exists('product.description.' . $key, Localization)
                        ? Localization['product.description.' . $key]
                        : '',
                    'value' => $descValue
                ];
            }
        }

        return [
            'list' => $descriptionObject['k_main'],
            'structuredList' => $descriptionStructuredList
        ];
    }

}