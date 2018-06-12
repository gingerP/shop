<?php

include_once AuWebRoot.'/src/back/views/components/AbstractComponent.php';

class ContactsComponent extends AbstractComponent {

    function __construct()
    {
        parent::__construct();
    }

    public function build() {
        $contacts = AddressService::getAddresses()['contacts'];

        $tpl = parent::getEngine()->loadTemplate('components/contacts/contacts.mustache');
        return $tpl->render(['contacts' => $contacts]);
    }
}
