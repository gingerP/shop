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
        $contactsMap = [];
        $contactsGroupsStyles = DBPreferencesType::getPreferenceValue(SettingsNames::CONTACTS_GROUPS_STYLES);
        foreach ($contacts as $contact) {
            if (is_array($contact['map']) && count($contact['map'])) {
                $markers[] = $contact['map'];
            }
            $groupCode = $contact['group'];
            if (!isset($contactsMap[$groupCode])) {
                $contactsMap[$groupCode] = [
                    'group' => $groupCode,
                    'title' => $contact['title'],
                    'backgroundClass' => $contactsGroupsStyles[$groupCode],
                    'contactsList' => []
                ];
            }
            $contactsMap[$groupCode]['contactsList'][] = $contact;
        }

        $contactsList = [];
        foreach ($contactsMap as $contactObj) {
            $contactsList[] = $contactObj;
        }

        $tpl = parent::getEngine()->loadTemplate('components/contacts/v2/contacts.mustache');
        return $tpl->render([
            'i18n' => Localization,
            'markers' => json_encode($markers),
            'contacts' => $contactsList
        ]);
    }
}
