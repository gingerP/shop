<?php

include_once AuWebRoot.'/src/back/views/components/AbstractComponent.php';

class ContactsComponentV1 extends AbstractComponent {

    function __construct()
    {
        parent::__construct();
    }

    public function build() {
        $contacts = AddressService::getAddresses()['contacts'];

        $tpl = parent::getEngine()->loadTemplate('components/contacts/v1/contacts.mustache');
        return $tpl->render(['contacts' => $contacts]);
    }
}
