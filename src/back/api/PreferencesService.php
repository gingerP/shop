<?php

class PreferencesService {

    public static function getPublicPreferences() {
        return [
            'contacts_groups' => DBPreferencesType::getPreferenceValue(Constants::CONTACTS_GROUPS)
        ];
    }

    public static function getAdminPreferences() {
        return [
            'dropbox_access_token' => DBPreferencesType::getPreferenceValue(Constants::DROPBOX_ACCESS_TOKEN),
            'dropbox_root_directory' => DBPreferencesType::getPreferenceValue(Constants::DROPBOX_ROOT_DIRECTORY)
        ];
    }


} 