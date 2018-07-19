<?php

include_once AuWebRoot.'/src/back/views/components/AbstractComponent.php';

class ContactsComponentV2 extends AbstractComponent {

    function __construct()
    {
        parent::__construct();
    }

    public function build() {
        $contacts = AddressService::getAddresses()['contacts'];
        $markers = [];
        foreach ($contacts as $contact) {
            if (is_array($contact['map']) && count($contact['map'])) {
                $markers[] = $contact['map'];
            }
        }

        $tpl = parent::getEngine()->loadTemplate('components/contacts/v2/contacts.mustache');
        return $tpl->render([
            'i18n' => Localization,
            'markers' => json_encode($markers),
            'contacts' => $contacts
        ]);
    }
}
