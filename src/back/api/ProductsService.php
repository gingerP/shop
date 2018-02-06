<?php
include_once('src/back/import/db');
include_once('src/back/import/import');
include_once('src/back/import/errors');

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
            DB::TABLE_GOODS__DESCRIPTION => DB::TABLE_GOODS__DESCRIPTION,
            DB::TABLE_GOODS__IMAGE_PATH => DB::TABLE_GOODS__IMAGE_PATH,
            DB::TABLE_GOODS__CATEGORY => DB::TABLE_GOODS__CATEGORY,
            DB::TABLE_GOODS__IMAGES => function ($imagesString) {
                $json = json_decode($imagesString);
                return is_null($json) ? [] : $json;
            }
        ]);
    }

    private static function getTreePath($treeUtils, $tree, $keyItem)
    {
        preg_match('/(\w{2})\d{3}/', $keyItem, $matches);
        $key = $matches[1];
        return self::clearTreePath($treeUtils->getTreePath($tree, $key));
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
        $newId = $goodsType->update($id, $values);
        if (!is_null($id)) {
            $goodsType = new DBGoodsType();
            $goodsType->incrementVersion($id);
        }
        return ProductsService::getGood($newId);
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
        for ($imgIndex = 0; $imgIndex < count($imagesFromFront); $imgIndex++) {
            $image = $imagesFromFront[$imgIndex];
            $isNew = $image['isNew'];
            if ($isNew) {
                $imageName = self::saveNewImage($image['data'], $productCode);
                array_push($imagesOrder, $imageName);
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
        $catalogPath = $Preferences->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
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
        $catalogPath = $Preferences->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];

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

    /*
        public static function updateImages($id, $imagesFromFront)
        {
            self::clearCache();

            $imagesFromFront = $imagesFromFront == null ? [] : $imagesFromFront;
            if (is_array($imagesFromFront)) {
                $dbPref = new DBPreferencesType();
                $catalogPath = $dbPref->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
                $goodsType = new DBGoodsType();
                $code = $goodsType->getCode($id);

                $imagesFromFileSystem = FileUtils::getFilesByPrefixByDescription(FileUtils::buildPath($catalogPath, $code), Constants::SMALL_IMAGE, "jpg");
                $imagesToDelete = array_merge($imagesFromFileSystem);
                uasort($imagesFromFront, function ($o1, $o2) {
                    return ($o1['index'] < $o2['index']) ? -1 : 1;
                });
                $imagesToProcessing = ProductsService::prepareImagesToProcessing($imagesFromFront, $imagesFromFileSystem, $imagesToDelete);
                ProductsService::removeImagesFilesBySamples(FileUtils::buildPath($catalogPath, $code), $imagesToDelete);
                //two steps of recreating files
                //step 1: rename old files to temp names (only files witch should be renamed)
                for ($imageIndex = 0; $imageIndex < count($imagesToProcessing); $imageIndex++) {
                    $imageData = $imagesToProcessing[$imageIndex];
                    if ($imageData['new'] == false) {
                        $imageNumber = FileUtils::getCatalogImageNumber($imageData['oldImage']);
                        if ($imageNumber != null) {
                            $smallImagePath = FileUtils::buildPath($catalogPath, $code, Constants::SMALL_IMAGE . $imageNumber . '.jpg');
                            $smallImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::SMALL_IMAGE . $imageData['tempName']);
                            FileUtils::rename($smallImagePath, $smallImageTmpPath);

                            $mediumImagePath = FileUtils::buildPath($catalogPath, $code, Constants::MEDIUM_IMAGE . $imageNumber . '.jpg');
                            $mediumImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::MEDIUM_IMAGE . $imageData['tempName']);
                            FileUtils::rename($mediumImagePath, $mediumImageTmpPath);

                            $largeImagePath = FileUtils::buildPath($catalogPath, $code, Constants::LARGE_IMAGE . $imageNumber . '.jpg');
                            $largeImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::LARGE_IMAGE . $imageData['tempName']);
                            FileUtils::rename($largeImagePath, $largeImageTmpPath);
                        }
                    }
                }
                //step 2: rename to real names (old files and new)
                for ($imageIndex = 0; $imageIndex < count($imagesToProcessing); $imageIndex++) {
                    $imageData = $imagesToProcessing[$imageIndex];
                    if ($imageData['new'] == false) {
                        //if image ISN'T NEW

                        $smallImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::SMALL_IMAGE . $imageData['tempName']);
                        $smallImageNewPath = FileUtils::buildPath($catalogPath, $code, Constants::SMALL_IMAGE . $imageData['newName'] . '.jpg');
                        FileUtils::rename($smallImageTmpPath, $smallImageNewPath);

                        $mediumImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::MEDIUM_IMAGE . $imageData['tempName']);
                        $mediumImageNewPath = FileUtils::buildPath($catalogPath, $code, Constants::MEDIUM_IMAGE . $imageData['newName'] . '.jpg');
                        FileUtils::rename($mediumImageTmpPath, $mediumImageNewPath);

                        $largeImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::LARGE_IMAGE . $imageData['tempName']);
                        $largeImageNewPath = FileUtils::buildPath($catalogPath, $code, Constants::LARGE_IMAGE . $imageData['newName'] . '.jpg');
                        FileUtils::rename($largeImageTmpPath, $largeImageNewPath);
                    } else {
                        //if image IS NEW
                        $imageStart = substr($imageData['oldImage'], 0, 15);
                        if ($imageStart !== 'data:image/jpeg') {
                            throw new ImageShouldBeJpegError();
                        }
                        $data = str_replace(
                            'data:image/jpeg;base64',
                            '', $imageData['oldImage']
                        );
                        $data = str_replace(' ', '+', $data);

                        $smallImageName = FileUtils::buildPath($catalogPath, $code, Constants::SMALL_IMAGE . $imageData['newName'] . '.jpg');
                        self::saveImageFromBase64($smallImageName, $data,
                            ['width' => Constants::SMALL_IMAGE_WIDTH, 'height' => Constants::SMALL_IMAGE_HEIGHT]
                        );
                        $mediumImageName = FileUtils::buildPath($catalogPath, $code, Constants::MEDIUM_IMAGE . $imageData['newName'] . '.jpg');
                        self::saveImageFromBase64($mediumImageName, $data,
                            ['width' => Constants::MEDIUM_IMAGE_WIDTH, 'height' => Constants::MEDIUM_IMAGE_HEIGHT]
                        );
                        $largeImageName = FileUtils::buildPath($catalogPath, $code, Constants::LARGE_IMAGE . $imageData['newName'] . '.jpg');
                        self::saveImageFromBase64($largeImageName, $data,
                            ['width' => Constants::LARGE_IMAGE_WIDTH, 'height' => Constants::LARGE_IMAGE_HEIGHT]
                        );
                    }
                }
                if (!is_null($id)) {
                    $goodsType = new DBGoodsType();
                    $goodsType->incrementVersion($id);
                }
            }
            return true;
        }
    */
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

    private static function prepareImagesToProcessing(&$imagesFromWeb, &$imagesFromFileSystem, &$imagesToDelete)
    {
        $result = [];
        for ($imgIndex = 0; $imgIndex < count($imagesFromWeb); $imgIndex++) {
            $imageData = $imagesFromWeb[$imgIndex];
            $imageNumber = "";
            if ($imageData['new'] != 'true') {
                $imageNumber = FileUtils::getCatalogImageNumber($imageData['image']);
            }
            $isNew = $imageData['new'] == 'true';

            if ($isNew
                || $imageNumber != strval($imgIndex + 1)
                || $imgIndex >= count($imagesFromFileSystem)
                || $imagesFromFileSystem[$imgIndex] != $imageData['image']
            ) {
                $newImageData = [
                    'new' => $imageData['new'],
                    'newName' => sprintf('%03d', $imgIndex + 1),
                    'tempName' => Utils::getRandomString(),
                    'oldImage' => $imageData['image']
                ];
                array_push($result, $newImageData);
            }
            foreach ($imagesToDelete as $key => $value) {
                $imageFromFileSystem = FileUtils::getCatalogImageName($value);
                $imageFromWeb = FileUtils::getCatalogImageName($imageData['image']);
                if ($imageFromFileSystem == $imageFromWeb) {
                    unset($imagesToDelete[$key]);
                }
            }
        }
        return $result;
    }

    private static function removeImagesFilesBySamples($path, $images)
    {
        foreach ($images as $key => $value) {
            preg_match('/^.*(s_|l_|m_){1}(\d{3})\.{1}\w+$/', $value, $matches);
            if (count($matches) > 2) {
                $imageNumber = $matches[2];
                ProductsService::tryToDeleteFile(
                    FileUtils::buildPath($path, Constants::SMALL_IMAGE . $imageNumber . '.jpg'));
                ProductsService::tryToDeleteFile(
                    FileUtils::buildPath($path, Constants::MEDIUM_IMAGE . $imageNumber . '.jpg'));
                ProductsService::tryToDeleteFile(
                    FileUtils::buildPath($path, Constants::LARGE_IMAGE . $imageNumber . '.jpg'));
            }
        }
    }

    private static function tryToDeleteFile($fileName)
    {
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    public static function getGood($id)
    {
        $goodsType = new DBGoodsType();
        $row = $goodsType->get($id);
        return [
            DB::TABLE_GOODS__ID => $row[DB::TABLE_GOODS__ID],
            DB::TABLE_GOODS__KEY_ITEM => $row[DB::TABLE_GOODS__KEY_ITEM],
            DB::TABLE_GOODS__NAME => $row[DB::TABLE_GOODS__NAME],
            DB::TABLE_GOODS__DESCRIPTION => $row[DB::TABLE_GOODS__DESCRIPTION],
            DB::TABLE_GOODS__IMAGE_PATH => $row[DB::TABLE_GOODS__IMAGE_PATH],
            DB::TABLE_GOODS__CATEGORY => $row[DB::TABLE_GOODS__CATEGORY],
            DB::TABLE_GOODS__IMAGES => json_decode($row[DB::TABLE_GOODS__IMAGES])
        ];
    }

    public static function getNextGoodCode($code)
    {
        $goodsType = new DBGoodsType();
        $goodsType->executeRequestRegExpWithLimit(DB::TABLE_GOODS__KEY_ITEM, '^' . $code, DB::TABLE_GOODS__KEY_ITEM, DB::DESC, 0, 1);
        $nextCode = null;
        while ($row = mysqli_fetch_array($goodsType->getResponse())) {
            $key_item = $row[DB::TABLE_GOODS__KEY_ITEM];
            preg_match('/(\d+)$/', $key_item, $matches);
            $numericPartOfCode = intval($matches[1]);
            ++$numericPartOfCode;
            $nextCode = $code . sprintf("%03d", $numericPartOfCode);
            break;
        }
        return $nextCode;
    }

    public static function deleteGood($id)
    {
        self::clearCache();

        $dbPref = new DBPreferencesType();
        $catalogPath = $dbPref->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
        $goodsType = new DBGoodsType();
        $code = $goodsType->getCode($id);
        FileUtils::removeDirRec($catalogPath . $code);
        $infoRemove = $goodsType->delete($id);
        return $infoRemove;
    }

    public static function getImages($id)
    {
        $pref = new DBPreferencesType();
        $catalogDir = $pref->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
        $goodsType = new DBGoodsType();
        $good = $goodsType->get($id);
        $result = [];
        if ($good != null) {
            $goodCode = $good[DB::TABLE_GOODS__KEY_ITEM];
            if (!is_null($goodCode)) {
                $version = $good[DB::TABLE_GOODS__VERSION];
                $imagesPaths = FileUtils::getFilesByPrefixByDescription(FileUtils::buildPath($catalogDir, $goodCode), Constants::SMALL_IMAGE, "jpg");
                foreach ($imagesPaths as $imagePath) {
                    array_push($result, Utils::normalizeAbsoluteImagePath($imagePath));
                }
            }
        }
        return $result;
    }

    public static function saveGoodsOrder($order)
    {
        self::clearCache();

        $dbUserOrder = new DBUserOrderType();
        Log::db("saveGoodsOrder " . count($order));
        return $dbUserOrder->saveOrder($order);
    }

    public static function getGoodsOrder()
    {
        $dbGoods = new DBGoodsType();
        $data = $dbGoods->getAdminSortedForCommon(0, PHP_INT_MAX);
        $dbPref = new DBPreferencesType();
        $catalogPath = $dbPref->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
        Log::db("getGoodsOrder ");
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

    private static function mergeImagesToGoods($goods)
    {
        $goodIndex = 0;
        while ($goodIndex < count($goods)) {
            $goods[$goodIndex][DB::TABLE_GOODS__IMAGE_PATH] = '/' . Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $goods[$goodIndex][DB::TABLE_GOODS__KEY_ITEM] . DIRECTORY_SEPARATOR . Constants::SMALL_IMAGE . '001.jpg';
            $goodIndex++;
        }
        return $goods;
    }

    private static function clearTreePath($path)
    {
        $ret = [];
        for ($pathItemIndex = 0; $pathItemIndex < count($path); $pathItemIndex++) {
            //$parentKey, $key, $value, $show, $homeViewMode
            array_push($ret, new Tree($path[$pathItemIndex]->parentKey, $path[$pathItemIndex]->key, $path[$pathItemIndex]->value, null, null));
        }
        return $ret;
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
            $catalogPath = $dbPref->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
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
}