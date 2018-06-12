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
        $imagesCatalogRoot = DBPreferencesType::getPreferenceValue(SettingsNames::CATALOG_PATH);
        $preparedProduct = [
            'name' => $product[DB::TABLE_GOODS__NAME],
            'description' => $this->prepareDescription($product[DB::TABLE_GOODS__DESCRIPTION]),
            'images' => [],
            'imagesNum' => count($imagesCodes),
            'smallImagesTotalWidth' => count($imagesCodes) * 130,
            'showViber' => false,
            'viberChatUri' => urlencode(DBPreferencesType::getPreferenceValue(SettingsNames::VIBER_CHAT_URI, '')),
            'viberPhoneNumber' => DBPreferencesType::getPreferenceValue(SettingsNames::VIBER_PHONE_NUMBER, ''),
            'email' => DBPreferencesType::getPreferenceValue(SettingsNames::FEEDBACK_MAIL, ''),
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
            if ($key != 'k_main' && !$this->isDescriptionListEmpty($descValue)) {
                $odd = !$odd;
                $descriptionStructuredList[] = [
                    'odd' => $odd ? 'odd' : 'even',
                    'name' => array_key_exists('product.description.' . $key, Localization)
                        ? Localization['product.description.' . $key]
                        : '',
                    'value' => implode('; ', $this->filterDescriptionList($descValue))
                ];
            }
        }

        return [
            'main' => $this->filterDescriptionList($descriptionObject['k_main']),
            'structuredList' => $descriptionStructuredList
        ];
    }

    private function filterDescriptionList($descValueList) {
        if (is_array($descValueList)) {
            $result = [];
            foreach ($descValueList as $description) {
                if (!is_null($description) && $description !== '') {
                    $result[] = $description;
                }
            }
            return $result;
        }
        return $descValueList;
    }

    private function isDescriptionListEmpty($descValueList) {
        if ($descValueList === '' || is_null($descValueList) || count($descValueList) === 0) {
            return true;
        }
        if (is_array($descValueList) && count($descValueList) > 0) {
            $isEveryEmpty = true;
            foreach ($descValueList as $desc) {
                $isEveryEmpty = $isEveryEmpty && ($desc === '' || is_null($desc));
                if (!$isEveryEmpty) {
                    return false;
                }
            }
            if ($isEveryEmpty) {
                return true;
            }

        }
        return false;
    }

}