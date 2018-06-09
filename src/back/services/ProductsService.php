<?php
include_once AuWebRoot . '/src/back/import/db.php';
include_once AuWebRoot . '/src/back/import/import.php';
include_once AuWebRoot . '/src/back/import/errors.php';

class ProductsService
{

    public static function getGoods($id)
    {
        $Products = new DBGoodsType();
        if ($id == -1) {
            $Products->executeRequest('', '', DB::TABLE_GOODS___ORDER, DB::ASC);
        } else {
            $Products->executeRequest(DB::TABLE_GOODS__ID, $id, DB::TABLE_GOODS___ORDER, DB::ASC);
        }
        return $Products->extractDataFromResponse($Products->getResponse(), [
            DB::TABLE_GOODS__ID => DB::TABLE_GOODS__ID,
            DB::TABLE_GOODS__KEY_ITEM => DB::TABLE_GOODS__KEY_ITEM,
            DB::TABLE_GOODS__NAME => DB::TABLE_GOODS__NAME,
            DB::TABLE_GOODS__DESCRIPTION => function ($descriptionString) {
                $json = json_decode($descriptionString);
                return is_null($json) ? [] : $json;
            },
            DB::TABLE_GOODS__IMAGE_PATH => DB::TABLE_GOODS__IMAGE_PATH,
            DB::TABLE_GOODS__CATEGORY => DB::TABLE_GOODS__CATEGORY,
            DB::TABLE_GOODS__IMAGES => function ($imagesString) {
                $json = json_decode($imagesString);
                return is_null($json) ? [] : $json;
            }
        ]);
    }

    public static function validate_updateGood()
    {
        $id = Utils::getFromPOST('id');
        $data = Utils::getFromPOST('data');
        $isIdExists = array_key_exists('id', $data);
        $isKeyItemExists = array_key_exists('key_item', $data);
        if ($isIdExists && !ctype_digit($id)
            || !array_key_exists('category', $data)
            || $isIdExists && !$isKeyItemExists
            || $isKeyItemExists && !$isIdExists
        ) {
            throw new BadRequestError('Invalid parameters.');
        }
    }

    /**
     * @api
     * @param $id
     * @param $values
     * @return []
     */
    public static function updateGood($id, $values)
    {
        self::clearCache();
        unset($values[DB::TABLE_GOODS__IMAGES]);

        $goodsType = new DBGoodsType();
        if (array_key_exists(DB::TABLE_GOODS__ID, $values)) {
            unset($values[DB::TABLE_GOODS__ID]);
        }
        if (!array_key_exists(DB::TABLE_GOODS__ID, $values)
            && !array_key_exists(DB::TABLE_GOODS__KEY_ITEM, $values)
        ) {
            $values[DB::TABLE_GOODS__KEY_ITEM] =
                ProductsService::getNextGoodCode($values[DB::TABLE_GOODS__CATEGORY]);
        }
        $values[DB::TABLE_GOODS__DESCRIPTION] = json_encode(
            isset($values[DB::TABLE_GOODS__DESCRIPTION]) ? $values[DB::TABLE_GOODS__DESCRIPTION] : [],
            JSON_UNESCAPED_UNICODE
        );

        $newId = $goodsType->update($id, $values);
        if (!is_null($id)) {
            $goodsType = new DBGoodsType();
            $goodsType->incrementVersion($id);
        }
        return ProductsService::getProduct($newId);
    }

    public static function validate_updateImages()
    {
        $id = Utils::getFromPOST('id');
        $data = Utils::getFromPOST('data', false);
        if (is_null($id)) {
            throw new BadRequestError('"id" field required.');
        } else if (!is_integer($id)) {
            throw new BadRequestError('"id" should be numeric.');
        } else if (!is_array($data)) {
            throw new BadRequestError('"data" should be array.');
        }
        foreach ($data as $image) {
            if (!array_key_exists('isNew', $image)) {
                throw new BadRequestError('"isNew" is required.');
            } else if (!array_key_exists('data', $image)) {
                throw new BadRequestError('"data" is required.');
            }
        }
    }

    /**@typedef {{
     *  new: string/boolean,
     *  image: string,
     *  index: string/int
     * }}
     * @param $id
     * @param $imagesFromFront
     */
    public static function updateImages($id, $imagesFromFront)
    {
        self::clearCache();
        $Products = new DBGoodsType();
        $product = $Products->get($id);
        $imagesOrder = [];
        $productCode = $product[DB::TABLE_GOODS__KEY_ITEM];

        $Preferences = new DBPreferencesType();
        $catalogPath = $Preferences->getPreference(SettingsNames::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
        FileUtils::createDir(FileUtils::buildPath($catalogPath, $productCode));

        for ($imgIndex = 0; $imgIndex < count($imagesFromFront); $imgIndex++) {
            $image = $imagesFromFront[$imgIndex];
            $isNew = $image['isNew'];
            $origin = $image['origin'];
            if ($isNew) {
                if ($origin === 'cloud') {
                    $filePath = $image['data'];
                    $fileContent = DropboxService::downloadFile($filePath);
                    if ($fileContent == false) {
                        continue;
                    }
                    $imageName = self::saveNewImageFromBinary($fileContent, $productCode);
                    array_push($imagesOrder, $imageName);
                } else {
                    $imageName = self::saveNewImage($image['data'], $productCode);
                    array_push($imagesOrder, $imageName);
                }
            } else {
                array_push($imagesOrder, $image['data']);
            }
        }
        $product = [];
        $product[DB::TABLE_GOODS__IMAGES] = json_encode($imagesOrder);
        $Products->update($id, $product);
        self::deleteImagesAllExcept($imagesOrder, $productCode);
    }

    private static function deleteImagesAllExcept($imagesCodes, $productCode)
    {
        $Preferences = new DBPreferencesType();
        $catalogPath = $Preferences->getPreference(SettingsNames::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
        $productDir = FileUtils::buildPath($catalogPath, $productCode);
        $except = [];
        foreach ($imagesCodes as $imageCode) {
            array_push(
                $except,
                Constants::SMALL_IMAGE . $imageCode . '.jpg',
                Constants::MEDIUM_IMAGE . $imageCode . '.jpg',
                Constants::LARGE_IMAGE . $imageCode . '.jpg'
            );
        }
        FileUtils::removeAllExcept($except, $productDir);
    }

    public static function saveNewImageFromBinary($binaryImage, $productCode)
    {
        $Preferences = new DBPreferencesType();
        $imageName = Utils::getRandomString(20);
        $catalogPath = $Preferences->getPreference(SettingsNames::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];

        $smallImageName = FileUtils::buildPath($catalogPath, $productCode, Constants::SMALL_IMAGE . $imageName . '.jpg');
        self::saveImageFromBinary($smallImageName, $binaryImage,
            ['width' => Constants::SMALL_IMAGE_WIDTH, 'height' => Constants::SMALL_IMAGE_HEIGHT]
        );
        $mediumImageName = FileUtils::buildPath($catalogPath, $productCode, Constants::MEDIUM_IMAGE . $imageName . '.jpg');
        self::saveImageFromBinary($mediumImageName, $binaryImage,
            ['width' => Constants::MEDIUM_IMAGE_WIDTH, 'height' => Constants::MEDIUM_IMAGE_HEIGHT]
        );
        $largeImageName = FileUtils::buildPath($catalogPath, $productCode, Constants::LARGE_IMAGE . $imageName . '.jpg');
        self::saveImageFromBinary($largeImageName, $binaryImage,
            ['width' => Constants::LARGE_IMAGE_WIDTH, 'height' => Constants::LARGE_IMAGE_HEIGHT]
        );
        return $imageName;
    }

    public static function saveNewImage($base64Image, $productCode)
    {
        $imageStart = substr($base64Image, 0, 15);
        if ($imageStart !== 'data:image/jpeg') {
            throw new ImageShouldBeJpegError(c);
        }
        $base64 = str_replace(
            'data:image/jpeg;base64',
            '',
            $base64Image
        );
        $base64 = str_replace(' ', '+', $base64);
        $Preferences = new DBPreferencesType();
        $imageName = Utils::getRandomString(20);
        $catalogPath = $Preferences->getPreference(SettingsNames::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];

        $smallImageName = FileUtils::buildPath($catalogPath, $productCode, Constants::SMALL_IMAGE . $imageName . '.jpg');
        self::saveImageFromBase64($smallImageName, $base64,
            ['width' => Constants::SMALL_IMAGE_WIDTH, 'height' => Constants::SMALL_IMAGE_HEIGHT]
        );
        $mediumImageName = FileUtils::buildPath($catalogPath, $productCode, Constants::MEDIUM_IMAGE . $imageName . '.jpg');
        self::saveImageFromBase64($mediumImageName, $base64,
            ['width' => Constants::MEDIUM_IMAGE_WIDTH, 'height' => Constants::MEDIUM_IMAGE_HEIGHT]
        );
        $largeImageName = FileUtils::buildPath($catalogPath, $productCode, Constants::LARGE_IMAGE . $imageName . '.jpg');
        self::saveImageFromBase64($largeImageName, $base64,
            ['width' => Constants::LARGE_IMAGE_WIDTH, 'height' => Constants::LARGE_IMAGE_HEIGHT]
        );
        return $imageName;
    }

    private static function saveImageFromBase64($imagePath, $data, $imageSize)
    {
        $decoded = base64_decode($data);
        if ($decoded === false) {
            throw new Exception("base64_decode failed for $imagePath");
        }
        $isSaved = file_put_contents($imagePath, $decoded);
        if ($isSaved === false) {
            throw new Exception("$imagePath not saved");
        }
        $imageEditorS = new ImageEditor($imagePath);
        $imageEditorS->resizeImage($imageSize['width'], $imageSize['height']);
        $imageEditorS->saveImage($imagePath, 100);
    }

    private static function saveImageFromBinary($imagePath, $binaryData, $imageSize)
    {
        $isSaved = file_put_contents($imagePath, $binaryData);
        if ($isSaved === false) {
            throw new Exception("$imagePath not saved");
        }
        $imageEditorS = new ImageEditor($imagePath);
        $imageEditorS->resizeImage($imageSize['width'], $imageSize['height']);
        $imageEditorS->saveImage($imagePath, 100);
    }

    public static function getProduct($id)
    {
        $goodsType = new DBGoodsType();
        $row = $goodsType->get($id);
        return [
            DB::TABLE_GOODS__ID => $row[DB::TABLE_GOODS__ID],
            DB::TABLE_GOODS__KEY_ITEM => $row[DB::TABLE_GOODS__KEY_ITEM],
            DB::TABLE_GOODS__NAME => $row[DB::TABLE_GOODS__NAME],
            DB::TABLE_GOODS__DESCRIPTION => json_decode($row[DB::TABLE_GOODS__DESCRIPTION], true, 512, JSON_UNESCAPED_UNICODE),
            DB::TABLE_GOODS__IMAGE_PATH => $row[DB::TABLE_GOODS__IMAGE_PATH],
            DB::TABLE_GOODS__CATEGORY => $row[DB::TABLE_GOODS__CATEGORY],
            DB::TABLE_GOODS__IMAGES => json_decode($row[DB::TABLE_GOODS__IMAGES])
        ];
    }

    public static function getNextGoodCode($code)
    {
        $Products = new DBGoodsType();
        $products = $Products->extractDataFromResponse($Products->executeRequestRegExpWithLimit(DB::TABLE_GOODS__KEY_ITEM, '^' . $code, DB::TABLE_GOODS__KEY_ITEM, DB::DESC, 0, 1));
        $nextCode = null;
        if (count($products) > 0) {
            foreach ($products as $product) {
                $key_item = $product[DB::TABLE_GOODS__KEY_ITEM];
                preg_match('/(\d+)$/', $key_item, $matches);
                $numericPartOfCode = intval($matches[1]);
                ++$numericPartOfCode;
                $nextCode = $code . sprintf("%03d", $numericPartOfCode);
                break;
            }
        } else {
            $nextCode = $code . '001';
        }
        return $nextCode;
    }

    public static function deleteGood($id)
    {
        self::clearCache();

        $dbPref = new DBPreferencesType();
        $catalogPath = $dbPref->getPreference(SettingsNames::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
        $goodsType = new DBGoodsType();
        $code = $goodsType->getCode($id);
        FileUtils::removeDirRec($catalogPath . $code);
        $infoRemove = $goodsType->delete($id);
        return $infoRemove;
    }

    public static function saveGoodsOrder($order)
    {
        self::clearCache();

        $dbUserOrder = new DBUserOrderType();
        return $dbUserOrder->saveOrder($order);
    }

    public static function getGoodsOrder()
    {
        $dbGoods = new DBGoodsType();
        $data = $dbGoods->getAdminSortedForCommon(0, PHP_INT_MAX);
        $dbPref = new DBPreferencesType();
        $catalogPath = $dbPref->getPreference(SettingsNames::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
        $mappings = [
            DB::TABLE_GOODS__ID => DB::TABLE_GOODS__ID,
            DB::TABLE_GOODS__KEY_ITEM => DB::TABLE_GOODS__KEY_ITEM,
            DB::TABLE_GOODS__NAME => DB::TABLE_GOODS__NAME,
            DB::TABLE_USER_ORDER__GOOD_ID => DB::TABLE_USER_ORDER__GOOD_ID,
            DB::TABLE_USER_ORDER__GOOD_INDEX => DB::TABLE_USER_ORDER__GOOD_INDEX,
            DB::TABLE_GOODS__IMAGE_PATH => function ($imagePath, $product) use ($catalogPath) {
                $images = json_decode($product[DB::TABLE_GOODS__IMAGES]);
                if (count($images) > 0) {
                    return FileUtils::buildPath(DIRECTORY_SEPARATOR, $catalogPath, $product[DB::TABLE_GOODS__KEY_ITEM], Constants::SMALL_IMAGE . $images[0] . ".jpg");
                }
                return "";
            }
        ];
        return $dbGoods->extractDataFromResponse($data, $mappings);
    }

    private static function clearCache()
    {
        $cacheModel = new DBPagesCacheType();
        $cacheModel->clear();
    }

    public static function readImagesFromCatalogToDb()
    {
        $mapping = [
            DB::TABLE_GOODS__ID => DB::TABLE_GOODS__ID,
            DB::TABLE_GOODS__KEY_ITEM => DB::TABLE_GOODS__KEY_ITEM
        ];
        $Products = new DBGoodsType();
        $products = $Products->extractDataFromResponse($Products->getList(), $mapping);

        $imagesTotalCount = 0;
        $productsTotalCount = count($products);
        if (count($products) > 0) {
            $dbPref = new DBPreferencesType();
            $catalogPath = $dbPref->getPreference(SettingsNames::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
            while (count($products) > 0) {
                $product = array_pop($products);
                $code = $product[DB::TABLE_GOODS__KEY_ITEM];
                $rootDir = FileUtils::buildPath($catalogPath, $code);
                $imagesFromFileSystem = FileUtils::getFilesNamesByPrefixByDescription($rootDir, Constants::SMALL_IMAGE, 'jpg');
                $images = count($imagesFromFileSystem) > 0 ? self::extractImagesCodes($imagesFromFileSystem) : [];
                $product[DB::TABLE_GOODS__IMAGES] = json_encode($images);
                $imagesTotalCount += count($images);
                $id = $product[DB::TABLE_GOODS__ID];
                unset($product[DB::TABLE_GOODS__ID]);
                $Products->update($id, $product);
            }
        }
        return [
            'products' => $productsTotalCount,
            'images' => $imagesTotalCount
        ];
    }

    private static function extractImagesCodes($smallImagesFilesNames)
    {
        $index = 0;
        $result = [];
        asort($smallImagesFilesNames);
        while ($index < count($smallImagesFilesNames)) {
            $image = $smallImagesFilesNames[$index];
            $code = self::extractImageCode($image);
            if (!is_null($code)) {
                array_push($result, $code);
            }
            $index++;
        }
        return $result;
    }

    private static function extractImageCode($smallImageFileName)
    {
        $parts = explode('.', $smallImageFileName);
        if (count($parts) == 2) {
            $nameParts = explode('_', $parts[0]);
            if (count($nameParts) == 2) {
                return $nameParts[1];
            }
        }
        return null;
    }

    private static function getDropboxClient()
    {
        $Preferences = new DBPreferencesType();
        $dropboxAccessToken = $Preferences->getPreference(SettingsNames::DROPBOX_ACCESS_TOKEN)[DB::TABLE_PREFERENCES__VALUE];
        list($appInfo, $clientIdentifier, $userLocale) = getAppConfig();
        $accessToken = $_SESSION['access-token'];
        return new dbx\Client($accessToken, $clientIdentifier, $userLocale, $appInfo->getHost());
    }
}