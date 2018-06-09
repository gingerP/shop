<?php
include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/import/models.php';

class DBAddressType extends DBType
{

    protected $tableName = DB::TABLE_ADDRESS___NAME;

    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    protected function getTable()
    {
        return $this->tableName;
    }

    protected function getTableName()
    {
        return $this->tableName;
    }

    protected function getIndexColumn()
    {
        return DB::TABLE_ADDRESS__ID;
    }

    protected function getOrder()
    {
        return DB::TABLE_ADDRESS__ORDER;
    }

    public function getActiveAddresses()
    {
        $ret = array();
        $contacts = $this->extractDataFromResponse($this->getListActive());
        $contactImagesRoot = DBPreferencesType::getPreferenceValue(SettingsNames::CONTACTS_IMAGES);
        $contactStyles = DBPreferencesType::getPreferenceValue(SettingsNames::CONTACTS_STYLES);
        while (count($contacts)) {
            $contact = array_shift($contacts);
            $address = [];
            $address['id'] = $contact[DB::TABLE_ADDRESS__ID];
            $address['description'] = $contact[DB::TABLE_ADDRESS__DESCRIPTION];
            $address['map'] = json_decode($contact[DB::TABLE_ADDRESS__MAP]);
            if (strlen(trim($contact[DB::TABLE_ADDRESS__PRICES])) > 0) {
                $address['prices'] = explode("|", $contact[DB::TABLE_ADDRESS__PRICES]);
            } else {
                $address['prices'] = [];
            }
            $address['mobileNumbers'] = [];
            if (strlen(trim($contact[DB::TABLE_ADDRESS__MOBILE_NUMBERS])) > 0) {
                $numbers = explode("|", $contact[DB::TABLE_ADDRESS__MOBILE_NUMBERS]);
                for ($numberIndex = 0; $numberIndex < count($numbers); $numberIndex++) {
                    $key = $numbers[$numberIndex];
                    $value = "";
                    $numberIndex++;
                    if ($numberIndex < count($numbers)) {
                        $value = $numbers[$numberIndex];
                    }
                    array_push($address['mobileNumbers'],
                        [
                            'number' => $value,
                            'provider' => $key,
                            'title' => Localization['phone_providers.' . $key]
                        ]
                    );
                }
            } else {
                $address['mobileNumbers'] = [];
            }

            $address['popupBody'] =
            $address['city'] = $contact[DB::TABLE_ADDRESS__CITY];
            $address['title'] = $contact[DB::TABLE_ADDRESS__TITLE];
            $address['address'] = $contact[DB::TABLE_ADDRESS__ADDRESS];
            $address['titleAddress'] = $contact[DB::TABLE_ADDRESS__TITLE_ADDRESS];
            $address['weekend'] = json_decode($contact[DB::TABLE_ADDRESS__WEEKEND]);
            $address['workingHours'] = json_decode($contact[DB::TABLE_ADDRESS__WORKING_HOURS]);
            $address['email'] = $contact[DB::TABLE_ADDRESS__EMAIL];
            $address['order'] = $contact[DB::TABLE_ADDRESS__ORDER];
            $address['color'] = $contactStyles[$address['id']];
            $address['images'] = array_map(
                function ($imageName) use ($contactImagesRoot) {
                    return $contactImagesRoot . '/' . $imageName;
                },
                json_decode($contact[DB::TABLE_ADDRESS__IMAGES])
            );
            $address['toString'] = json_encode($address);
            array_push($ret, $address);
        }
        return $ret;
    }
}