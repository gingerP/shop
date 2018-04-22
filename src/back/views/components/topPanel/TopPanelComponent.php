<?php

include_once AuWebRoot . '/src/back/views/components/AbstractComponent.php';

class TopPanelComponent extends AbstractComponent
{
    private $pageName;

    public function __construct($pageName)
    {
        $this->pageName = $pageName;
    }

    public function build()
    {
        $componentInfo = [
            'i18n' => Localization,
            'headerLinks' => array_map(
                function (&$item) {
                    $item['class'] .= ' ' . (in_array($this->pageName, $item['code']) ? 'selected' : '');
                    return $item;
                },
                [
                    [
                        'link' => '/',
                        'title' => Localization['panel.top.main'],
                        'code' => [UrlParameters::PAGE__MAIN],
                        'class' => ''
                    ],
                    [
                        'link' => '/' . UrlParameters::PAGE__CATALOG,
                        'title' => Localization['panel.top.catalog'],
                        'code' => [UrlParameters::PAGE__CATALOG, UrlParameters::PAGE__PRODUCTS],
                        'class' => ''
                    ],
                    [
                        'link' => '/' . UrlParameters::PAGE__CONTACTS,
                        'title' => Localization['panel.top.contacts'],
                        'code' => [UrlParameters::PAGE__CONTACTS],
                        'class' => ''
                    ],
                    [
                        'link' => '/' . UrlParameters::PAGE__DELIVERY,
                        'title' => Localization['panel.top.delivery'],
                        'code' => [UrlParameters::PAGE__DELIVERY],
                        'class' => ''
                    ]
                ]
            )
        ];
        $tpl = parent::getEngine()->loadTemplate('components/topPanel/top-panel.mustache');
        return $tpl->render($componentInfo);
    }

}