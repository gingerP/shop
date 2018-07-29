<?php

include_once AuWebRoot . '/src/back/views/components/AbstractComponent.php';

class ContactsComponentV2 extends AbstractComponent
{

    private $openedContact;

    function __construct($openedContact = '')
    {
        parent::__construct();
        $this->openedContact = $openedContact;
    }

    public function build()
    {
        $contacts = AddressService::getAddresses()['contacts'];
        $markers = [];
        $contactsMap = [];
        $contactsGroupsStyles = DBPreferencesType::getPreferenceValue(SettingsNames::CONTACTS_GROUPS_STYLES);
        foreach ($contacts as &$contact) {
            if (is_array($contact['map']) && count($contact['map'])) {
                $markers[] = $contact['map'];
            }
            $groupCode = $contact['group'];
            if (!isset($contactsMap[$groupCode])) {
                $contactsMap[$groupCode] = [
                    'group' => $groupCode,
                    'title' => $contact['title'],
                    'markers' => [],
                    'backgroundClass' => $contactsGroupsStyles[$groupCode],
                    'contactsList' => [],
                    'tabsGroupSelectedClass' => 'opened',
                    'tabsContentSelectedClass' => 'closed',
                    'tabsSelectedClass' => 'opened'
                ];
            }

            $group = &$contactsMap[$groupCode];
            $group['markers'][] = $contact['map'];
            $contact['selectedClass'] = 'closed';
            $contact['link'] = URLBuilder::getContactsLink($contact['code']);
            if ($this->openedContact !== '') {
                if ($group['tabsContentSelectedClass'] === 'closed') {
                    if ($contact['code'] === $this->openedContact) {
                        $group['tabsGroupSelectedClass'] = 'opened';
                        $group['tabsContentSelectedClass'] = 'opened';
                        $group['tabsSelectedClass'] = 'closed';
                    } else {
                        $group['tabsGroupSelectedClass'] = 'closed';
                        $group['tabsContentSelectedClass'] = 'closed';
                        $group['tabsSelectedClass'] = 'closed';
                    }
                }
                $contact['selectedClass'] = $contact['code'] === $this->openedContact ? 'opened' : 'closed';
            } else {
                $group['tabsContentSelectedClass'] = 'closed';
                $group['tabsSelectedClass'] = 'opened';
            }

            $contactsMap[$groupCode]['contactsList'][] = $contact;
        }

        $contactsList = [];
        foreach ($contactsMap as $contactObj) {
            $contactObj['markers'] = json_encode($contactObj['markers']);
            $contactsList[] = $contactObj;
        }

        $tpl = parent::getEngine()->loadTemplate('components/contacts/v2/contacts.mustache');
        return $tpl->render([
            'i18n' => Localization,
            'markers' => json_encode($markers),
            'contactsLink' => URLBuilder::getContactsLink(),
            'contacts' => $contactsList
        ]);
    }
}
