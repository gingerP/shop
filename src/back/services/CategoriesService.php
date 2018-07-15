<?php

class CategoriesService
{

    public static function getList() {
        $dbGoodsKeys = new DBNavKeyType();
        $dbGoodsKeys->getList();
        $ret = [];
        $resKeys = [
            DB::TABLE_NAV_KEY__ID,
            DB::TABLE_NAV_KEY__VALUE,
            DB::TABLE_NAV_KEY__KEY_ITEM,
            DB::TABLE_NAV_KEY__PARENT_KEY,
            DB::TABLE_NAV_KEY__IMAGE,
            DB::TABLE_NAV_KEY__ORDER,
        ];
        $response = $dbGoodsKeys->getResponse();
        while ($row = mysqli_fetch_array($response)) {
            $item = [];
            foreach ($resKeys as $key) {
                $item[$key] = $row[$key];
            }
            array_push($ret, $item);
        }
        return $ret;
    }

    public static function removeCategory($id)
    {
        $categoriesImagesRoot = DBPreferencesType::getPreferenceValue(SettingsNames::CATEGORIES_IMAGES_PATH);
        if (!isset($categoriesImagesRoot) || $categoriesImagesRoot === '') {
            throw new InternalError('CATEGORIES_IMAGES_PATH not configured!');
        }

        $Categories = new DBNavKeyType();
        $category = $Categories->get($id);
        $image = $category['image'];
        if (isset($image)) {
            FileUtils::unlinkPath(FileUtils::buildPath($categoriesImagesRoot, $category['image']));
        }
        $Categories->delete($id);
        return [];
    }

    public static function saveCategory($category)
    {
        $categoriesImagesRoot = DBPreferencesType::getPreferenceValue(SettingsNames::CATEGORIES_IMAGES_PATH);
        if (!isset($categoriesImagesRoot) || $categoriesImagesRoot === '') {
            throw new InternalError('CATEGORIES_IMAGES_PATH not configured!');
        }

        $imageParams = $category['image_params'];
        $image = $category['image'];
        if (isset($imageParams) && in_array($imageParams['type'], ['cloud', 'base64'])) {
            $image = self::saveImage($imageParams, $image, $categoriesImagesRoot);
        }

        $id = $category['id'];
        $Categories = new DBNavKeyType();
        if (isset($id)) {
            $oldCategory = $Categories->get($id);
            $oldImage = $oldCategory['image'];
            if ($oldImage !== '' && $oldImage !== $image) {
                FileUtils::unlinkPath(FileUtils::buildPath($categoriesImagesRoot, $oldImage));
            }
        }

        $preparedCategory = [
            'key_item' => $category['key_item'],
            'parent_key' => $category['parent_key'],
            'order' => $category['order'],
            'value' => $category['value'],
            'image' => $image
        ];
        $id = $Categories->update($category['id'], $preparedCategory);
        $updatedCategory = $Categories->get($id);
        return Utils::extractObject($updatedCategory, $Categories->getMappings());
    }

    private static function saveImage($imageParams, $image, $categoriesImagesRoot)
    {

        $type = $imageParams['type'];
        if ($type === 'base64') {
            return self::saveImageFromBase64($image, $categoriesImagesRoot);
        } else if ($type === 'cloud') {
            $params = $imageParams['params'];
            $filePath = $params['path'];
            $binaryImage = DropboxService::downloadFile($filePath);
            $imageExtension = FileUtils::getFileExtensionFromName($params['path']);
            return self::saveImageFromBinary($binaryImage, $categoriesImagesRoot, $imageExtension);
        }

        throw new BadRequestError("Unsupported image type \"$type\"!");
    }

    private static function saveImageFromBase64($base64, $directory)
    {
        $imageExtension = Utils::getImageExtensionFromBase64($base64);
        $imageName = Utils::getRandomString() . '.' . $imageExtension;
        $base64PrefixLess = Utils::extractBase64($base64);
        $path = FileUtils::buildPath($directory, $imageName);
        $file = file_put_contents($path, base64_decode($base64PrefixLess));
        if ($file) {
            return $imageName;
        }

        $error = error_get_last();
        throw new InternalError($error['message']);
    }

    private static function saveImageFromBinary($binaryImage, $directory, $imageExtension)
    {
        $imageName = Utils::getRandomString() . '.' . $imageExtension;
        $path = FileUtils::buildPath($directory, $imageName);
        $file = file_put_contents($path, $binaryImage);
        if ($file) {
            return $imageName;
        }

        $error = error_get_last();
        throw new InternalError($error['message']);
    }

}