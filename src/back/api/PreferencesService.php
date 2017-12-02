<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 10/31/14
 * Time: 12:19 AM
 */

class PreferencesService {

    public static function getPublicPreferences() {
        return [
            'contacts_groups' => DBPreferencesType::getPreferenceValue(Constants::CONTACTS_GROUPS)
        ];
    }

} 