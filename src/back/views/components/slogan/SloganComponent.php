<?php

include_once AuWebRoot . '/src/back/views/components/AbstractComponent.php';

class SloganComponent extends AbstractComponent
{
    public function __construct()
    {
        parent::__construct();
    }

    public function build()
    {
        $engine = parent::getEngine();
        $tpl = $engine->loadTemplate('components/slogan/slogan.mustache');
        return $tpl->render([
            'i18n' => Localization,
            'catalogLink' => URLBuilder::getCatalogLink(),
            'contactsLink' => URLBuilder::getContactsLink(),
            'deliveryLink' => URLBuilder::getDeliveryLink()
        ]);
    }
}