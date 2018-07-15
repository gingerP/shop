<?php

class PreferencesService {

    public static function getPublicPreferences() {
        return [
            'contacts_groups' => DBPreferencesType::getPreferenceValue(SettingsNames::CONTACTS_GROUPS),
            'google_maps_api_key' => DBPreferencesType::getPreferenceValue(SettingsNames::GOOGLE_MAPS_API_KEY)
        ];
    }

    public static function getAdminPreferences() {
        return [
            'categories_images_path' => DBPreferencesType::getPreferenceValue(SettingsNames::CATEGORIES_IMAGES_PATH),
            'dropbox_access_token' => DBPreferencesType::getPreferenceValue(SettingsNames::DROPBOX_ACCESS_TOKEN),
            'dropbox_root_directory' => DBPreferencesType::getPreferenceValue(SettingsNames::DROPBOX_ROOT_DIRECTORY),
            'description' => $responseData = DescriptionKeys::$keys
        ];
    }


} 