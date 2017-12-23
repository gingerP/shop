<?php

class ContactsPage extends APagesCreator{

    public function __construct() {
        parent::__construct();
        $this->setPageCode("contacts_page");
        $this->setIsTreeVisible(false);
        $this->setIsStatusBarVisible(true);
        $this->setIsViewModeBlockVisible(false);
        $this->setIsPathLinkVisible(false);
        $this->setPathLinkForTree(PathLinks::getDOMForContacts());
        $this->updateTitleTagChildren(["Контакты - "]);

        $metaDesc = new Meta();
        $metaDesc->addAttributes([
            "name" => "description",
            "content" => "на этой странице Вы найдете наши контакты, торговые точки на рынках и как всегда - электронная почта augustova@mail.ru - ждем ваших писем!"
        ]);
        $this->addMetaTags($metaDesc);

        $this->content = $this->getHtml();
    }

    protected function createGeneralContent() {
        $mainTag = new Div();
        $mainTag->addStyleClasses(["map_page", "float_left"]);
        $mainTag->addChildList([$this->getInfoBlock()]);
        return $mainTag;
    }

    private function getInfoBlock() {
        $mainTag = new Div();
        $mainTag->updateId("contact_list");
        return $mainTag;
    }

    private function getMap() {
        $mainTag = new Div();
        $mainTag->addStyleClass("map_viewport");
        $map = new Div();
        $map->updateId("google_map");
        return $mainTag->addChild($map);
    }


    public function getPreBottom() {
        return $this->getMap();
    }

} 