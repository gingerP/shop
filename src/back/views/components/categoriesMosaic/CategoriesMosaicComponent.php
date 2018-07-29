<?php

include_once AuWebRoot . '/src/back/views/components/AbstractComponent.php';

class CategoriesMosaicComponent extends AbstractComponent
{
    private $type;

    public function __construct($type = 'common')
    {
        $this->type = $type;
        parent::__construct();
    }

    public function build()
    {
        $Categories = new DBNavKeyType();
        $categories = $Categories->extractDataFromResponse($Categories->getList(null, ['order', 'ASC']));
        $categoriesImagesCatalog = DBPreferencesType::getPreferenceValue(SettingsNames::CATEGORIES_IMAGES_PATH);
        $categoriesMap = [];

        foreach ($categories as $category) {
            $code = $category['key_item'];
            $categoriesMap[$code] = [
                'value' => $category[DB::TABLE_NAV_KEY__VALUE],
                'link' => URLBuilder::getCatalogLinkForTree($category[DB::TABLE_NAV_KEY__KEY_ITEM]),
                'image' => '/' . $categoriesImagesCatalog . '/' . $category[DB::TABLE_NAV_KEY__IMAGE]
            ];
        }

        $dir = __DIR__;
        $categoriesStyles = json_decode(file_get_contents($dir . '/' . $this->type . '/categories.json'), true);
        $categoriesColumnsSettings = json_decode(file_get_contents($dir . '/' . $this->type . '/categories-columns.json'), true);

        $categoriesGroupedByColumns = [];
        foreach ($categoriesStyles as $categoryStyle) {
            $code = $categoryStyle['category'];
            $column = $categoryStyle['column'];
            if (!isset($categoriesGroupedByColumns[$column])) {
                $categoriesGroupedByColumns[$column] = [];
            }
            $categoryStyleData = $categoryStyle['data'];
            $categoryStyle['data'] = [];
            if (isset($categoryStyleData)) {
                foreach ($categoryStyleData as $key => $value) {
                    $categoryStyle['data'][] = [
                        'key' => $key,
                        'value' => $value
                    ];
                }
            }
            $categoriesGroupedByColumns[$column][] = [
                'style' => $categoryStyle,
                'category' => $categoriesMap[$code]
            ];
        }

        $preparedCategories = [];
        for ($index = 0; $index < count($categoriesColumnsSettings); $index++) {
            $columnSettings = $categoriesColumnsSettings[$index];
            $preparedCategories[] = [
                'column' => $columnSettings,
                'categoriesParams' => $categoriesGroupedByColumns[$index]
            ];

        }

        $tpl = parent::getEngine()->loadTemplate('components/categoriesMosaic/categories.mustache');
        return $tpl->render([
            'columns' => $preparedCategories,
            'i18n' => Localization
        ]);
    }
}