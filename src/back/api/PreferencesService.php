<?php

class PreferencesService {

    public static function getPublicPreferences() {
        return [
            'contacts_groups' => DBPreferencesType::getPreferenceValue(Constants::CONTACTS_GROUPS),
            'google_maps_api_key' => DBPreferencesType::getPreferenceValue(Constants::GOOGLE_MAPS_API_KEY)
        ];
    }

    public static function getAdminPreferences() {
        return [
            'dropbox_access_token' => DBPreferencesType::getPreferenceValue(Constants::DROPBOX_ACCESS_TOKEN),
            'dropbox_root_directory' => DBPreferencesType::getPreferenceValue(Constants::DROPBOX_ROOT_DIRECTORY)
        ];
    }


} 