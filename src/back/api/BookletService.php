<?php
include_once('db');

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
            $result = json_encode(Utils::extractObject($result, $mapping));
        } else {
            $result = json_encode(unserialize($result[DB::TABLE_BOOKLET__DATA]));
        }
        return $result == null? "[]": $result;
    }

    public static function save($booklet) {
        $id = $booklet[self::$bookletIdKey];
        $dbBookletType = new DBBookletsType();
        //is new
        if ($booklet[self::$bookletIdKey] == null && strlen($booklet[self::$bookletIdKey]) == 0) {
            $id = $dbBookletType->update(null, [
                DB::TABLE_BOOKLET__NAME => $booklet[self::$bookletNameKey],
                DB::TABLE_BOOKLET__CREATED => time(),
                DB::TABLE_BOOKLET__UPDATED => time()
            ]);
            $booklet[self::$bookletIdKey] = $id;
            $booklet[self::$bookletCodeKey] = Utils::getRandomString();
        }
        if ($booklet != null && count($booklet[self::$bookletItemsKey]) > 0) {
            for ($itemIndex = 0; $itemIndex < count($booklet[self::$bookletItemsKey]); $itemIndex++) {
                $item = $booklet[self::$bookletItemsKey][$itemIndex];
                if (Utils::isBase64($item[self::$itemImageKey])) {
                    $imageExtension = Utils::getImageExtensionFromBase64($item[self::$itemImageKey]);
                    $imageUrl = self::saveBookletImage($booklet[self::$bookletCodeKey], Utils::getRandomString().".".$imageExtension, $item[self::$itemImageKey]);
                    $item[self::$itemImageKey] = $imageUrl;
                }
                $booklet[self::$bookletItemsKey][$itemIndex] = $item;
            }
        }

        $dbBookletType->update($id, [
            DB::TABLE_BOOKLET__NAME => $booklet[self::$bookletNameKey],
            DB::TABLE_BOOKLET__CREATED => $booklet[self::$bookletCreatedKey],
            DB::TABLE_BOOKLET__UPDATED => time(),
            DB::TABLE_BOOKLET__CODE => $booklet[self::$bookletCodeKey],
            DB::TABLE_BOOKLET__DATA => serialize($booklet)
        ]);

        return $id;
    }

    public static function delete($id) {
        $dbBookletType = new DBBookletsType();
        return $dbBookletType->delete($id);
    }

    public static function getBookletBackgroundImages() {
        $bacgroundPath = DBPreferencesType::getPreferenceS(Constants::BOOKLET_BACKGROUND_IMAGES_PATH);
        $bacgroundImagesList = FileUtils::getFilesByPrefixByDescription($bacgroundPath, '.*', 'jpg');
        $bacgroundImagesList = array_merge($bacgroundImagesList, FileUtils::getFilesByPrefixByDescription($bacgroundPath, '.*', 'png'));
        return $bacgroundImagesList;
    }

    private static function saveBookletImage($bookletCode, $imageName, $base64Image) {
        $bookletImagesRoot = DBPreferencesType::getPreferenceS(Constants::BOOKLET_IMAGE_PATH);
        $bookletImageDirectory = FileUtils::buildPath($bookletImagesRoot, $bookletCode);
        FileUtils::createDir($bookletImageDirectory);

        $imageEditor = ImageEditor::newImageBase64($base64Image);
        $bookletImagePath = FileUtils::buildPath($bookletImagesRoot, $bookletCode, $imageName);
        $imageEditor->saveImage($bookletImagePath);
        return FileUtils::buildPath($bookletCode, $imageName);
    }



    /*
 var bookletEntity = {
        id: null,
        name: null,
        listColumns: [],
        itemType: this.itemTypes.booklet,
        created: null,
        updated: null
    }
    var bookletColumn = {
        id: null,
        listItems: [],
        itemType: 'column'
    }
    var bookletItemEntity = {
        id: null,
        image: null,
        listLabels: [],
        number: null,
        position: null,
        size: null,
        itemType: this.itemTypes.item
    }
    var bookletItemLabelEntity = {
        id: null,
        type: null,
        text: null,
        itemType: this.itemTypes.label
    }
     * */

}