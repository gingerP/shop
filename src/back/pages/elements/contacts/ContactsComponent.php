<?php
include_once __DIR__.'/../AbstractComponent.php';
use Katzgrau\KLogger\Logger as Logger;

class ContactsComponent extends AbstractComponent {

    function __construct()
    {
    }

    public function build() {
        $this->logger = new Logger(AU_CONFIG['log.file'], AU_CONFIG['log.level']);
        $contacts = AddressService::getAddresses()['contacts'];

        $tpl = parent::getEngine()->loadTemplate('elements/contacts/contacts.mustache');
        return $tpl->render(['contacts' => $contacts]);
    }


}