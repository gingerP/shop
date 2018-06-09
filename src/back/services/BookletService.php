<?php
include_once AuWebRoot.'/src/back/import/db.php';

class BookletService {
    private static $bookletItemsKey = 'listItems';
    private static $itemImageKey = 'image';
    private static $bookletIdKey = 'id';
    private static $bookletNameKey = 'name';
    private static $bookletCreatedKey = 'created';
    private static $bookletUpdatedKey = 'updated';
    private static $bookletCodeKey = 'code';

    public static function getList($mapping) {
        $dbBookletType = new DBBookletsType();
        return $dbBookletType->extractDataFromResponse($dbBookletType->getList($mapping), $mapping);
    }

    public static function get($id, $mapping = []) {
        $dbBookletType = new DBBookletsType();
        $result = $dbBookletType->get($id);
        if (count($mapping) > 0) {
            $result = Utils::extractObject($result, $mapping);
        } else {
            $result = unserialize($result[DB::TABLE_BOOKLET__DATA]);
        }
        return $result == null? "[]": $result;
    }

    public static function save($booklet) {
        if (is_null($booklet)) {
            return [];
        }
        $id = array_key_exists(self::$bookletIdKey, $booklet) ? $booklet[self::$bookletIdKey] : null;

        $bookletCode = array_key_exists(self::$bookletCodeKey, $booklet) ? $booklet[self::$bookletCodeKey] : null;
        if (is_null($bookletCode) || $bookletCode == '') {
            $bookletCode = Utils::getRandomString();
        }

        $bookletName = array_key_exists(self::$bookletNameKey, $booklet) ? $booklet[self::$bookletNameKey] : null;
        if (is_null($bookletName) || $bookletName == '') {
            $bookletName = $bookletCode;
        }

        $Booklets = new DBBookletsType();
        //is new
        if (is_null($id)) {
            $id = $Booklets->update(null, [
                DB::TABLE_BOOKLET__NAME => $bookletName,
                DB::TABLE_BOOKLET__CREATED => date("Y-m-d H:i:s"),
                DB::TABLE_BOOKLET__UPDATED => date("Y-m-d H:i:s"),
                DB::TABLE_BOOKLET__CODE => $bookletCode,
            ]);
            $booklet[self::$bookletIdKey] = $id;
            $booklet[self::$bookletCodeKey] = $bookletCode;
        }
        $bookletImages = $booklet[self::$bookletItemsKey];
        $bookletImagesCount = count($bookletImages);
        if ($bookletImagesCount > 0) {
            for ($itemIndex = 0; $itemIndex < $bookletImagesCount; $itemIndex++) {
                $item = $bookletImages[$itemIndex];
                if (array_key_exists('cloudId', $item)) {
                    $fileCloudId = $item['cloudId'];
                    $imageExtension = $item['cloudMetaFileExtension'];
                    $imageName = Utils::getRandomString().".".$imageExtension;
                    $imageBinary = DropboxService::downloadFile($fileCloudId);
                    $imageUrl = self::saveBookletBinaryImage($bookletCode, $imageName, $imageBinary, $imageExtension);
                    unset($item['cloudId']);
                    unset($item['cloudMetaFileExtension']);
                    $item[self::$itemImageKey] = $imageUrl;
                } else if (Utils::isBase64($item[self::$itemImageKey])) {
                    $imageExtension = Utils::getImageExtensionFromBase64($item[self::$itemImageKey]);
                    $imageName = Utils::getRandomString().".".$imageExtension;
                    $imageUrl = self::saveBookletImage($bookletCode, $imageName, $item[self::$itemImageKey]);
                    $item[self::$itemImageKey] = $imageUrl;
                }
                $booklet[self::$bookletItemsKey][$itemIndex] = $item;
            }
        }

        $Booklets->update($id, [
            DB::TABLE_BOOKLET__UPDATED => date("Y-m-d H:i:s"),
            DB::TABLE_BOOKLET__DATA => serialize($booklet)
        ]);

        return $id;
    }

    public static function delete($id) {
        $Booklets = new DBBookletsType();
        return $Booklets->delete($id);
    }

    public static function getBookletBackgroundImages() {
        $bacgroundPath = DBPreferencesType::getPreferenceValue(SettingsNames::BOOKLET_BACKGROUND_IMAGES_PATH);
        $bacgroundImagesList = FileUtils::getFilesByPrefixByDescription($bacgroundPath, '.*', 'jpg');
        $bacgroundImagesList = array_merge($bacgroundImagesList, FileUtils::getFilesByPrefixByDescription($bacgroundPath, '.*', 'png'));
        return $bacgroundImagesList;
    }

    private static function saveBookletImage($bookletCode, $imageName, $base64Image) {
        $bookletImagesRoot = DBPreferencesType::getPreferenceValue(SettingsNames::BOOKLET_IMAGE_PATH);
        $bookletImageDirectory = FileUtils::buildPath($bookletImagesRoot, $bookletCode);
        FileUtils::createDir($bookletImageDirectory);

        $imageEditor = ImageEditor::newImageBase64($base64Image);
        $bookletImagePath = FileUtils::buildPath($bookletImagesRoot, $bookletCode, $imageName);
        $imageEditor->saveImage($bookletImagePath);
        FileUtils::unlinkPath($imageEditor->getImagePath());
        return FileUtils::buildPath($bookletCode, $imageName);
    }

    private static function saveBookletBinaryImage($bookletCode, $imageName, $binaryImage, $imageExtension) {
        $bookletImagesRoot = DBPreferencesType::getPreferenceValue(SettingsNames::BOOKLET_IMAGE_PATH);
        $bookletImageDirectory = FileUtils::buildPath($bookletImagesRoot, $bookletCode);
        FileUtils::createDir($bookletImageDirectory);

        $imageEditor = ImageEditor::newImageFromBinary($binaryImage, $imageExtension);
        $bookletImagePath = FileUtils::buildPath($bookletImagesRoot, $bookletCode, $imageName);

        $imageEditor->saveImage($bookletImagePath);
        FileUtils::unlinkPath($imageEditor->getImagePath());
        return FileUtils::buildPath($bookletCode, $imageName);
    }
}