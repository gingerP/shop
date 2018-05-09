<?php

include_once AuWebRoot.'/src/back/views/components/AbstractComponent.php';

class MainPageContactsComponent extends AbstractComponent {

    private $backgroundClasses = ['blue-bg', 'green-bg'];

    function __construct()
    {
        parent::__construct();
    }

    public function build() {
        $contacts = AddressService::getAddresses()['contacts'];
        $contactsMap = [];

        foreach ($contacts as $contact) {
            $contactInfo = [
                'map' => $contact['map'],
                'address' => $contact['address'],
                'id' => Utils::uuid()
            ];
            $contact['info'] = json_encode($contactInfo);
            $contactsMap[$contact['city']][] = $contact;
        }

        $openedClass = 'opened';
        $prepareContacts = [];
        $groupsIds = [];
        $index = 0;
        foreach ($contactsMap as $contactCity => $contactsGroup) {
            $contactsInfo = [];
            $groupId = Utils::uuid();
            $contactsInfo['openedClass'] = $openedClass;
            $contactsInfo['title'] = $contactCity;
            $contactsInfo['contacts'] = $contactsGroup;
            $contactsInfo['groupId'] = $groupId;
            $contactsInfo['backgroundClass'] = $this->backgroundClasses[$index];

            $prepareContacts[] = $contactsInfo;
            $groupsIds[] = $groupId;
            $openedClass = '';
            $index++;
        }

        $tpl = parent::getEngine()->loadTemplate('components/mainPageContacts/main-page-contacts.mustache');
        return $tpl->render([
            'groupsIds' => json_encode($groupsIds),
            'backgroundClasses' => json_encode($this->backgroundClasses),
            'contactsMoreLink' => '/' . UrlParameters::PAGE__CONTACTS,
            'contactsGroups' => $prepareContacts,
            'i18n' => Localization
        ]);
    }


}