<?php

include_once AuWebRoot . '/src/back/views/components/AbstractComponent.php';

class CategoriesComponent extends AbstractComponent
{
    public function __construct()
    {
        parent::__construct();
    }

    public function build()
    {
        $Categories = new DBNavKeyType();
        $categories = $Categories->extractDataFromResponse($Categories->getList());

        foreach ($categories as &$category) {
            $category['link'] = URLBuilder::getCatalogLinkForTree($category[DB::TABLE_NAV_KEY__KEY_ITEM]);
        }
        $tpl = parent::getEngine()->loadTemplate('components/categories/categories.mustache');
        return $tpl->render(['categories' => $categories]);
    }
}