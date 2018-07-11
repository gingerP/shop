<?php

include_once AuWebRoot.'/src/back/views/components/contacts/ContactsComponent.php';

class ContactsPage extends AbstractPage
{

    public function __construct()
    {
        parent::__construct(UrlParameters::PAGE__CONTACTS);

    }

    public function build()
    {
        $this->setPageCode("contacts_page");
        $this->setIsTreeVisible(false);
        $this->setIsStatusBarVisible(true);
        $this->setIsViewModeBlockVisible(false);
        $this->setIsPathLinkVisible(false);
        $this->setPathLinkForTree(PathLinks::getDOMForContacts());
        $this->updateTitleTagChildren('Контакты');

        $metaDesc = new Meta();
        $metaDesc->addAttributes([
            "name" => "description",
            "content" => "на этой странице Вы найдете наши контакты, торговые точки на рынках и как всегда - электронная почта augustova@mail.ru - ждем ваших писем!"
        ]);
        $this->addMetaTags($metaDesc);

        $this->content = $this->getHtml();
        return $this;
    }

    protected function createGeneralContent()
    {
        $mainTag = new Div();
        $mainTag->addStyleClasses(["map_page", "float_left"]);
        $mainTag->addChildList([$this->getInfoBlock()]);
        $mainTag->addChild((new ContactsComponent())->build());
        return $mainTag;
    }

    private function getInfoBlock()
    {
        $mainTag = new Div();
        $mainTag->addChild("<contacts/>");
        return $mainTag;
    }

    private function getMap()
    {
        $mainTag = new Div();
        $mainTag->addStyleClass("map_viewport");
        $map = new Div();
        $map->updateId("google_map");
        return $mainTag->addChild($map);
    }


    public function getPreBottom()
    {
        return $this->getMap();
    }

    protected function getSourceScripts()
    {
        $preferences = PreferencesService::getPublicPreferences();
        $scripts = strtr("<script type='text/javascript'>
            window.AugustovaApp = {googleApiKey: 'googleApiKeyValue'};
        </script>", [
            'googleApiKeyValue' => $preferences['google_maps_api_key']
        ]);
        $scripts .= parent::getSourceScripts();
        if (!$this->isJsUglify) {
            $scripts .=
                '
                <script type="text/javascript" src="/src/front/js/components/google-map/google-map.component.js"></script>
                <script type="text/javascript" src="/src/front/js/components/contacts/contacts.component.js"></script>';
        }
        return $scripts;
    }

} 