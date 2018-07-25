<?php

include_once AuWebRoot . '/src/back/views/components/AbstractComponent.php';

class MainPageContactsComponent extends AbstractComponent
{

    private $backgroundClasses = ['blue-bg', 'green-bg'];

    function __construct()
    {
        parent::__construct();
    }

    public function build()
    {
        $contacts = AddressService::getAddresses()['contacts'];
        $contactsMap = [];
        $groupsTitles = [];

        foreach ($contacts as $contact) {
            $contactInfo = [
                'map' => $contact['map'],
                'address' => $contact['address'],
                'id' => Utils::uuid()
            ];
            $contact['info'] = json_encode($contactInfo);
            $contactsMap[$contact['group']][] = $contact;
            $groupsTitles[$contact['group']] = $contact['title'];
        }

        $contactsGroupsStyles = DBPreferencesType::getPreferenceValue(SettingsNames::CONTACTS_GROUPS_STYLES);

        $openedClass = 'opened';
        $prepareContacts = [];
        $groupsIds = [];
        $index = 0;
        foreach ($contactsMap as $contactGroupCode => $contactsGroup) {
            $contactsInfo = [];
            $groupId = $contactGroupCode;
            $contactsInfo['openedClass'] = $openedClass;
            $contactsInfo['title'] = $groupsTitles[$contactGroupCode];
            $contactsInfo['contacts'] = $contactsGroup;
            $contactsInfo['groupId'] = $groupId;
            $contactsInfo['backgroundClass'] = $contactsGroupsStyles[$contactGroupCode];

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