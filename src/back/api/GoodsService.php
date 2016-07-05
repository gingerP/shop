<?php
include_once('db');
include_once('import');

class GoodsService
{

    public static function getGoods($id)
    {
        $ret = [];
        $goodsType = new DBGoodsType();
        if ($id == -1) {
            $goodsType->executeRequest('', '', DB::TABLE_GOODS___ORDER, DB::ASC);
        } else {
            $goodsType->executeRequest(DB::TABLE_GOODS__ID, $id, DB::TABLE_GOODS___ORDER, DB::ASC);
        }
        $response = $goodsType->getResponse();
        $resKeys = [
            DB::TABLE_GOODS__ID,
            DB::TABLE_GOODS__KEY_ITEM,
            DB::TABLE_GOODS__NAME,
            DB::TABLE_GOODS__PERSON,
            DB::TABLE_GOODS__INDIVIDUAL,
            DB::TABLE_GOODS__DESCRIPTION,
            DB::TABLE_GOODS__IMAGE_PATH,
            DB::TABLE_GOODS__GOD_TYPE
        ];
        $treeUtils = new TreeUtils();
        $tree = $treeUtils->buildTreeByLeafs();
        while ($row = mysql_fetch_array($response)) {
            $item = [];
            foreach ($resKeys as $key) {
                $item[$key] = $row[$key];
            }
            $keyItem = $item[DB::TABLE_GOODS__KEY_ITEM];
            $item['_tree'] = self::getTreePath($treeUtils, $tree, $keyItem);
            array_push($ret, $item);
        }
        return $ret;
    }

    private static function getTreePath($treeUtils, $tree, $keyItem)
    {
        preg_match('/(\w{2})\d{3}/', $keyItem, $matches);
        $key = $matches[1];
        return self::clearTreePath($treeUtils->getTreePath($tree, $key));
    }

    public static function updateGood($id, $values)
    {
        if (is_array($values)) {
            $goodsType = new DBGoodsType();
            if (array_key_exists(DB::TABLE_GOODS__ID, $values)) {
                unset($values[DB::TABLE_GOODS__ID]);
            }
            $pref = new DBPreferencesType();
            $imagePath = $pref->getPreference(Constants::CATALOG_PATH);
            $imagePath = $imagePath[DB::TABLE_PREFERENCES__VALUE];
            $imagesCatalog = $values[DB::TABLE_GOODS__KEY_ITEM];
            $isDirCreated = FileUtils::createDir($imagePath . $imagesCatalog);
            return $goodsType->update($id, $values);
        }
        return -1;
    }

    public static function updateImages($id, $oldCode, $imagesFromFront)
    {
        $imagesFromFront = $imagesFromFront == null ? [] : $imagesFromFront;
        if (is_array($imagesFromFront)) {
            $dbPref = new DBPreferencesType();
            $catalogPath = $dbPref->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
            $mediumImageWatermarkName = $dbPref->getPreference(Constants::WATERMARK_MEDIUM_PATH)[DB::TABLE_PREFERENCES__VALUE];
            $largeImageWatermarkName = $dbPref->getPreference(Constants::WATERMARK_LARGE_PATH)[DB::TABLE_PREFERENCES__VALUE];
            $goodsType = new DBGoodsType();
            $code = $goodsType->getCode($id);

            $newCode = null;
            if (!is_null($oldCode) && $code != $oldCode) {
                $newCode = $code;
                $code = $oldCode;
            }

            $imagesFromFileSystem = FileUtils::getFilesByPrefixByDescription(FileUtils::buildPath($catalogPath, $code), Constants::SMALL_IMAGE, "jpg");
            $imagesToDelete = array_merge($imagesFromFileSystem);
            uasort($imagesFromFront, function ($o1, $o2) {
                return ($o1['index'] < $o2['index']) ? -1 : 1;
            });
            $imagesToProcessing = self::prepareImagesToProcessing($imagesFromFront, $imagesFromFileSystem, $imagesToDelete);
            self::removeImagesFilesBySamples(FileUtils::buildPath($catalogPath, $code), $imagesToDelete);
            //two steps of recreating files
            //step 1: rename old files to temp names (only files witch should be renamed)
            for ($imageIndex = 0; $imageIndex < count($imagesToProcessing); $imageIndex++) {
                $imageData = $imagesToProcessing[$imageIndex];
                if ($imageData['new'] == 'false') {
                    $imageNumber = FileUtils::getCatalogImageNumber($imageData['oldImage']);
                    if ($imageNumber != null) {
                        $smallImagePath = FileUtils::buildPath($catalogPath, $code, Constants::SMALL_IMAGE . $imageNumber . '.jpg');
                        $smallImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::SMALL_IMAGE . $imageData['tempName']);
                        rename($smallImagePath, $smallImageTmpPath);

                        $mediumImagePath = FileUtils::buildPath($catalogPath, $code, Constants::MEDIUM_IMAGE . $imageNumber . '.jpg');
                        $mediumImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::MEDIUM_IMAGE . $imageData['tempName']);
                        rename($mediumImagePath, $mediumImageTmpPath);

                        $largeImagePath = FileUtils::buildPath($catalogPath, $code, Constants::LARGE_IMAGE . $imageNumber . '.jpg');
                        $largeImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::LARGE_IMAGE . $imageData['tempName']);
                        rename($largeImagePath, $largeImageTmpPath);
                    }
                }
            }
            //step 2: rename to real names (old files and new)

            for ($imageIndex = 0; $imageIndex < count($imagesToProcessing); $imageIndex++) {
                $imageData = $imagesToProcessing[$imageIndex];
                if ($imageData['new'] == 'false') {
                    //if image ISN'T NEW

                    $smallImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::SMALL_IMAGE . $imageData['tempName']);
                    $smallImageNewPath = FileUtils::buildPath($catalogPath, $code, Constants::SMALL_IMAGE . $imageData['newName'] . '.jpg');
                    rename($smallImageTmpPath, $smallImageNewPath);

                    $mediumImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::MEDIUM_IMAGE . $imageData['tempName']);
                    $mediumImageNewPath = FileUtils::buildPath($catalogPath, $code, Constants::MEDIUM_IMAGE . $imageData['newName'] . '.jpg');
                    rename($mediumImageTmpPath, $mediumImageNewPath);

                    $largeImageTmpPath = FileUtils::buildPath($catalogPath, $code, Constants::LARGE_IMAGE . $imageData['tempName']);
                    $largeImageNewPath = FileUtils::buildPath($catalogPath, $code, Constants::LARGE_IMAGE . $imageData['newName'] . '.jpg');
                    rename($largeImageTmpPath, $largeImageNewPath);
                } else {
                    //if image IS NEW
                    $data = str_replace('data:image/jpeg;base64,', '', $imageData['oldImage']);
                    $data = str_replace(' ', '+', $data);

                    $smallImageName = FileUtils::buildPath($catalogPath, $code, Constants::SMALL_IMAGE . $imageData['newName'] . '.jpg');
                    if (file_put_contents($smallImageName, base64_decode($data)) == true) {
                        $imageEditorS = new ImageEditor($smallImageName);
                        $imageEditorS->resizeImage(Constants::SMALL_IMAGE_WIDTH, Constants::SMALL_IMAGE_HEIGHT);
                        $imageEditorS->saveImage($smallImageName, 100);
                    };

                    $mediumImageName = FileUtils::buildPath($catalogPath, $code, Constants::MEDIUM_IMAGE . $imageData['newName'] . '.jpg');
                    if (file_put_contents($mediumImageName, base64_decode($data)) == true) {
                        $imageEditorS = new ImageEditor($mediumImageName);
                        $imageEditorS->resizeImage(Constants::MEDIUM_IMAGE_WIDTH, Constants::MEDIUM_IMAGE_HEIGHT);
                        $imageEditorS->applyWatermark($mediumImageWatermarkName);
                        $imageEditorS->saveImage($mediumImageName, 100);
                    };

                    $largeImageName = FileUtils::buildPath($catalogPath, $code, Constants::LARGE_IMAGE . $imageData['newName'] . '.jpg');
                    if (file_put_contents($largeImageName, base64_decode($data)) == true) {
                        $imageEditorS = new ImageEditor($largeImageName);
                        $imageEditorS->resizeImage(Constants::LARGE_IMAGE_WIDTH, Constants::LARGE_IMAGE_HEIGHT);
                        $imageEditorS->applyWatermark($largeImageWatermarkName);
                        $imageEditorS->saveImage($largeImageName, 100);
                    };
                }
            }

            //if good KEY_ITEM was CHANGED
            if (!is_null($newCode)) {
                rename(FileUtils::buildPath($catalogPath, $code), FileUtils::buildPath($catalogPath, $newCode));
            }
        }
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

            if ($imageData['new'] == 'true'
                || $imageNumber != ($imgIndex + 1 + "")
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
                $res = [];
                array_push($res, unlink(FileUtils::buildPath($path, Constants::SMALL_IMAGE . $imageNumber . '.jpg')));
                array_push($res, unlink(FileUtils::buildPath($path, Constants::MEDIUM_IMAGE . $imageNumber . '.jpg')));
                array_push($res, unlink(FileUtils::buildPath($path, Constants::LARGE_IMAGE . $imageNumber . '.jpg')));
            }
        }
    }

    public static function getGood($id)
    {
        $goodsType = new DBGoodsType();
        $row = $goodsType->get($id);
        $ret = null;
        $ret[DB::TABLE_GOODS__ID] = $row[DB::TABLE_GOODS__ID];
        $ret[DB::TABLE_GOODS__KEY_ITEM] = $row[DB::TABLE_GOODS__KEY_ITEM];
        $ret[DB::TABLE_GOODS__NAME] = $row[DB::TABLE_GOODS__NAME];
        $ret[DB::TABLE_GOODS__PERSON] = $row[DB::TABLE_GOODS__PERSON];
        $ret[DB::TABLE_GOODS__INDIVIDUAL] = $row[DB::TABLE_GOODS__INDIVIDUAL];
        $ret[DB::TABLE_GOODS__DESCRIPTION] = $row[DB::TABLE_GOODS__DESCRIPTION];
        $ret[DB::TABLE_GOODS__IMAGE_PATH] = $row[DB::TABLE_GOODS__IMAGE_PATH];
        $ret[DB::TABLE_GOODS__GOD_TYPE] = $row[DB::TABLE_GOODS__GOD_TYPE];
        $treeUtils = new TreeUtils();
        $tree = $treeUtils->buildTreeByLeafs();
        $keyItem = $ret[DB::TABLE_GOODS__KEY_ITEM];
        $ret['_tree'] = self::getTreePath($treeUtils, $tree, $keyItem);
        return $ret;
    }

    public static function getNextGoodCode($code)
    {
        $goodsType = new DBGoodsType();
        $goodsType->executeRequestRegExpWithLimit(DB::TABLE_GOODS__KEY_ITEM, '^' . $code, DB::TABLE_GOODS__KEY_ITEM, DB::DESC, 0, 1);
        $nextCode = null;
        while ($row = mysql_fetch_array($goodsType->getResponse())) {
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
        $dbPref = new DBPreferencesType();
        $catalogPath = $dbPref->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
        $goodsType = new DBGoodsType();
        $code = $goodsType->getCode($id);
        //remove directory with images
        FileUtils::removeDir($catalogPath . $code);
        $infoRemove = $goodsType->delete($id);
        return $infoRemove;
    }

    public static function getImages($id)
    {
        $pref = new DBPreferencesType();
        $catalogDir = $pref->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
        $goodsType = new DBGoodsType();
        $good = $goodsType->get($id);
        $goodCode = $good[DB::TABLE_GOODS__KEY_ITEM];
        $images = [];
        if (!is_null($goodCode)) {
            $images = FileUtils::getFilesByPrefixByDescription(FileUtils::buildPath($catalogDir, $goodCode), Constants::SMALL_IMAGE, "jpg");
            //$filesMedium = FileUtils::getFilesByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH.DIRECTORY_SEPARATOR.$goodCode.DIRECTORY_SEPARATOR, Constants::MEDIUM_IMAGE, "jpg");
        }
        return $images;
    }

    public static function saveGoodsOrder($order)
    {
        $dbUserOrder = new DBUserOrderType();
        Log::db("saveGoodsOrder " . count($order));
        return $dbUserOrder->saveOrder($order);
    }

    public static function getGoodsOrder()
    {
        $dbGoods = new DBGoodsType();
        $data = $dbGoods->getAdminSortedForCommon(0, PHP_INT_MAX);
        Log::db("getGoodsOrder ");
        $mappings = [
            DB::TABLE_GOODS__ID => DB::TABLE_GOODS__ID,
            DB::TABLE_GOODS__KEY_ITEM => DB::TABLE_GOODS__KEY_ITEM,
            DB::TABLE_GOODS__NAME => DB::TABLE_GOODS__NAME,
            DB::TABLE_USER_ORDER__GOOD_ID => DB::TABLE_USER_ORDER__GOOD_ID,
            DB::TABLE_USER_ORDER__GOOD_INDEX => DB::TABLE_USER_ORDER__GOOD_INDEX
        ];
        $goods = $dbGoods->extractDataFromResponse($data, $mappings);
        return self::mergeImagesToGoods($goods);
    }

    private static function mergeImagesToGoods($goods) {
        $goodIndex = 0;
        while($goodIndex < count($goods)) {
            $goods[$goodIndex][DB::TABLE_GOODS__IMAGE_PATH] = '/'.Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $goods[$goodIndex][DB::TABLE_GOODS__KEY_ITEM] . DIRECTORY_SEPARATOR. Constants::SMALL_IMAGE.'001.jpg';
            $goodIndex++;
        }
        return $goods;
    }

    private function clearTreePath($path)
    {
        $ret = [];
        for ($pathItemIndex = 0; $pathItemIndex < count($path); $pathItemIndex++) {
            //$parentKey, $key, $value, $show, $homeViewMode
            array_push($ret, new Tree($path[$pathItemIndex]->parentKey, $path[$pathItemIndex]->key, $path[$pathItemIndex]->value, null, null));
        }
        return $ret;
    }

}