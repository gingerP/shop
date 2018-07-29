<?php

include_once AuWebRoot . '/src/back/views/components/contacts/v2/ContactsComponentV2.php';

class ContactsPage extends AbstractPage
{
    private $contactCode;

    public function __construct($request)
    {
        parent::__construct(UrlParameters::PAGE__CONTACTS);
        $this->contactCode = $request->param('contactCode', '');
    }

    public function build()
    {
        $this->setPageCode('contacts_page');
        $this->setIsTreeVisible(false);
        $this->setIsStatusBarVisible(true);
        $this->setIsViewModeBlockVisible(false);
        $this->setIsPathLinkVisible(false);
        $this->setPathLinkForTree(PathLinks::getDOMForContacts());
        $this->updateTitleTagChildren('Контакты');

        $metaDesc = new Meta();
        $metaDesc->addAttributes([
            'name' => 'description',
            'content' => 'на этой странице Вы найдете наши контакты, торговые точки на рынках и как всегда - электронная почта augustova@mail.ru - ждем ваших писем!'
        ]);
        $this->addMetaTags($metaDesc);

        $this->content = $this->getHtml();
        return $this;
    }

    protected function createGeneralContent()
    {
        $mainTag = new Div();
        return $mainTag;
    }

    private function getInfoBlock()
    {
        $mainTag = new Div();
        $mainTag->addChild('<contacts/>');
        return $mainTag;
    }

    private function getMap()
    {
        $mainTag = new Div();
        $mainTag->addStyleClass('map_viewport');
        $header = new A();
        $header->addAttribute('href', URLBuilder::getContactsLink());
        $header->addChild(Localization['contacts.header.title']);
        $header->addStyleClass('contacts-header-title page-title');
        $map = new Div();
        $map->updateId('google-map');

        $mainTag->addStyleClasses(['map_page'/*, 'float_left'*/]);
        $mainTag->addChildList([$this->getInfoBlock()]);
        return $mainTag->addChildren($header, $map, (new ContactsComponentV2($this->contactCode))->build());
    }


    public function getPreBottom()
    {
        return $this->getMap();
    }

    protected function getSourceScripts()
    {
        $scripts = parent::getSourceScripts();
        $preferences = PreferencesService::getPublicPreferences();
        $scripts .= strtr("<script type='text/javascript'>
            window.AugustovaApp = {googleApiKey: 'googleApiKeyValue'};
        </script>", [
            'googleApiKeyValue' => $preferences['google_maps_api_key']
        ]);

        if ($this->isJsUglify) {
            return $scripts . '<script type="text/javascript">' . file_get_contents(AuWebRoot . '/dist/contacts-page.js') . '</script>';
        }
        $scripts .= '
            <script type="text/javascript" src="/src/front/js/components/google-map/google-map.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/contacts/contacts.component.js"></script>
        ';
        return $scripts;
    }

} 