<?php
include_once("src/back/import/import");
include_once("src/back/import/tag");
include_once("src/back/import/page");

class DeliveryPage extends APagesCreator
{

    public function DeliveryPage()
    {
        $this->APagesCreator();
        $this->setPageCode("delivery");
        $this->setIsTreeVisible(false);
        $this->setIsStatusBarVisible(true);
        $this->setIsViewModeBlockVisible(false);
        $this->setIsPathLinkVisible(false);
        $this->setPathLinkForTree(PathLinks::getDOMForContacts());
        $this->updateTitleTagChildren(["Доставка почтой - "]);

        $metaDesc = new Meta();
        $metaDesc->addAttributes([
            "name" => "description",
            "content" => "на этой странице Вы сможете оформить доставку."
        ]);
        $this->addMetaTags($metaDesc);

        $this->content = $this->getHtml();
    }

    protected function createGeneralContent()
    {
        $NUMBER = '+375(29) 559-46-51';
        $mainTag = new Div();
        $mainTag->addChild("
            <div class='delivery-block f-15'>
                <div class='delivery-placeholder'>
                    <div class='delivery-description'>
                        Для оформления доставки напишите нам или свяжитесь с нашим представителем.
                    </div>
                    <div class='number-separator'></div>
                    <a href='tel:$NUMBER' class='delivery-phone-link'>
                        <svg fill=\"#414141\" height=\"24\" viewBox=\"0 0 24 24\" width=\"24\" xmlns=\"http://www.w3.org/2000/svg\">
                            <path d=\"M0 0h24v24H0z\" fill=\"none\"/>
                            <path d=\"M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z\"/>
                        </svg>
                    </a>
                    <div class='delivery-phone-text-container'>
                        <span class='delivery-phone-provider'>МТС</span>
                        <span class='delivery-phone-text'>$NUMBER Галина</span>
                     </div>             
                </div>
            </div>
            
            ");
        return $mainTag;
    }

}