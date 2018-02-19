<?php
include_once("src/back/import/import");
include_once("src/back/import/tag");
include_once("src/back/import/page");

class DeliveryPage extends APagesCreator
{

    public function __construct()
    {
        parent::__construct();
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
                    <!--<div class='number-separator'></div>-->
                    <div class='delivery-description'>
                        <div style='margin-bottom: 10px;'>Для оформления заказа нам необходима следующая информация о Вас:</div>
                        
                        <ul class='dresses-size-description-common'>
                            <li class='dresses-size-label'>адрес доставки</li>
                            <li class='dresses-size-label'>ФИО получателя</li>
                            <li class='dresses-size-label'>контактный номер телефона</li>
                            <li>а так же мерки без припусков, согласно следующей схеме:</li>
                        </ul>
                    </div>
                    <div class='dresses-size-container'>
                        <img src='/images/dresses_sizes.png' class='dresses-size-image'>
                        <div class='dresses-size-description'>
                            <ul class='dresses-size-list'>
                                <li>
                                    <div class='dresses-size-number'>1</div>
                                    <div class='dresses-size-list-text'><span class='dresses-size-label'>Обхват груди</span> - измеряется вокруг туловища по самым выступающим точкам грудных желез.</div>
                                </li>
                                <li>
                                    <div class='dresses-size-number'>2</div>
                                    <div class='dresses-size-list-text'><span class='dresses-size-label'>Обхват талии</span> - измеряется вокруг туловища на уровне линии талии.</div>
                                </li>
                                <li>
                                    <div class='dresses-size-number'>3</div>
                                    <div class='dresses-size-list-text'><span class='dresses-size-label'>Обхват бёдер</span> - измеряется в области на 20 см ниже линии талии.</div>
                                </li>
                                <li>
                                    <div class='dresses-size-number'>4</div>
                                    <div class='dresses-size-list-text dresses-size-label'>Рост</div>
                                </li>
                            </ul>
                            <div>
                                <div class='dresses-size-description-label'>Расчет стоимости заказа, доставляемого почтой по всем регионам  РБ:</div>
                                <span>
                                    К цене товара необходимо добавить оплату за почтовое отправление. <br>
                                    На  данный  момент она составляет примерно 6,00  бел. рублей. <br>
                                    При получении следует учитывать дополнительную плату  3 % от суммы вашего заказа  почте (за электронный перевод денежных средств).
                                </span>
                            </div>
                        </div>
                    </div>
                               
                </div>
            </div>
            
            ");
        return $mainTag;
    }

}