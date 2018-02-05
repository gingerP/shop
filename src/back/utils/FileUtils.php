<?php
include_once("src/back/import/import");
include_once("src/back/import/errors");

class FileUtils
{
    private static $tmpDir = '';
    private static $tmpDirAge = 0;
    private static $prefTimeout = 10;

    private static $bookletImageDir = '';
    private static $bookletImageDirAge = 0;

    public static function getFilesByDescription($rootPath, $description)
    {
        return self::getFilesByPrefixByDescription($rootPath, '', $description);
    }

    public static function getFirstFileInDirectoryByDescription($rootPath, $description)
    {
        $files = self::getFilesByDescription($rootPath, $description);
        if (count($files) > 0) {
            return $files[0];
        }
        return '';
    }

    public static function getFirstFileInDirectoryByPrefixByDescription($rootPath, $prefix, $description)
    {
        $files = self::getFilesByPrefixByDescription($rootPath, $prefix, $description);
        if (count($files) > 0) {
            foreach ($files as $file) {
                $fileNameBegin = strrpos($file, DIRECTORY_SEPARATOR);
                $fileName = substr($file, $fileNameBegin + 1);
                if (preg_match('/^' . $prefix . Constants::DEFAULT_FIRST_IMAGE_PREFIX . '.*$/', $fileName)) {
                    return $file;
                }
            }
            return $files[0];
        }
        return '';
    }

    public static function getFilesByPrefixByDescription($rootPath, $prefix, $description)
    {
        if (strlen($prefix) == 0) {
            $prefix = '.*';
        }
        $files = array();
        if (file_exists($rootPath) && $dh = opendir($rootPath)) {
            while (($file = readdir($dh)) !== false) {

                if (filetype($rootPath . DIRECTORY_SEPARATOR . $file) == "file") {
                    if (preg_match('/^' . $prefix . '.*(\.' . $description . ')$/', $file)) {
                        array_push($files, $rootPath . DIRECTORY_SEPARATOR . $file);
                    }
                }
            }
            //sort($files);
            closedir($dh);
        }
        sort($files);
        return $files;
    }

    public static function getFilesNamesByPrefixByDescription($rootPath, $prefix, $description)
    {
        if (strlen($prefix) == 0) {
            $prefix = '.*';
        }
        $files = array();
        if (file_exists($rootPath) && $dh = opendir($rootPath)) {
            while (($file = readdir($dh)) !== false) {

                if (filetype($rootPath . DIRECTORY_SEPARATOR . $file) == "file") {
                    if (preg_match('/^' . $prefix . '.*(\.' . $description . ')$/', $file)) {
                        array_push($files, $file);
                    }
                }
            }
            //sort($files);
            closedir($dh);
        }
        sort($files);
        return $files;
    }

    public static function buildPath()
    {
        $args = func_num_args();
        $path = "";
        for ($argIndex = 0; $argIndex < $args; $argIndex++) {
            $path .= func_get_arg($argIndex) . DIRECTORY_SEPARATOR;
        }
        $path = preg_replace("/(\\" . DIRECTORY_SEPARATOR . ")+/", DIRECTORY_SEPARATOR, $path);
        $path = preg_replace("/(\\" . DIRECTORY_SEPARATOR . "){1}$/", "", $path);
        return $path;
    }

    public static function getCapImage($imageName)
    {
        return Labels::CAP_IMAGES_ROOT . $imageName;
    }

    public static function isFileExist($relativeFilePath)
    {
        return file_exists($relativeFilePath) && filetype($relativeFilePath);
    }

    public static function getFileContent()
    {

    }

    public static function createDir($relativeFilePath)
    {
        /*$root = $_SERVER["DOCUMENT_ROOT"];*/
        if (!self::isFileExist($relativeFilePath)) {
            return mkdir($relativeFilePath, 0777, true);
        }
        return true;
    }

    public static function removeDirRec($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        self::removeDir($dir . "/" . $object);
                    } else {
                        self::unlinkPath($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            self::removeDir($dir);
        }
    }

    public static function getCatalogImageNumber($imageName)
    {
        $result = null;
        preg_match('/^.*(' . Constants::SMALL_IMAGE . '|' . Constants::MEDIUM_IMAGE . '|' . Constants::LARGE_IMAGE . '){1}(\d{3})\.{1}\w+$/', $imageName, $matches);
        if (count($matches) > 2) {
            $result = $matches[2];
        }
        return $result;
    }

    public static function getCatalogImageName($imageName)
    {
        $result = null;
        preg_match('/^.*((' . Constants::SMALL_IMAGE . '|' . Constants::MEDIUM_IMAGE . '|' . Constants::LARGE_IMAGE . '){1}\d{3}\.(jpg|JPG|jpeg|JPEG){1})$/', $imageName, $matches);
        if (count($matches) > 2) {
            $result = $matches[1];
        }
        return $result;
    }

    public static function getCatalogImageSizeCode($imageName)
    {
        $result = null;
        preg_match('/^.*(' . Constants::SMALL_IMAGE . '|' . Constants::MEDIUM_IMAGE . '|' . Constants::LARGE_IMAGE . '){1}(\d{3})\.{1}\w+$/', $imageName, $matches);
        if (count($matches) > 2) {
            $result = $matches[2];
        }
        return $result;
    }

    public static function createFileBase64($base64Data, $fileName)
    {
        $newFile = fopen($fileName, "wb");
        $data = explode(',', $base64Data);
        $res = fwrite($newFile, base64_decode($data[1]));
        fclose($newFile);
        return !!$res;
    }

    public static function getTmpDir()
    {
        return DBPreferencesType::getPreferenceValue(Constants::TEMP_DIRECTORY);
    }

    public static function getBookletImageDir()
    {
        if (self::$bookletImageDir == null || self::$bookletImageDir == '' || self::$bookletImageDirAge + self::$prefTimeout <= time()) {
            $pref = new DBPreferencesType();
            self::$bookletImageDir = $pref->getPreference(Constants::TEMP_DIRECTORY)[DB::TABLE_PREFERENCES__VALUE];
            self::$bookletImageDirAge = time();
        }
        return self::$bookletImageDir;
    }

    public static function rename($oldPath, $newPath)
    {
        if (!rename($oldPath, $newPath)) {
            $error = error_get_last();
            throw new InternalError($GLOBALS['AU_MESSAGES']['rename_failed_error'] . " " . $error['message']);
        }
    }

    public static function unlinkPath($path)
    {
        $res = unlink($path);
        if (!$res) {
            $error = error_get_last();
            throw new InternalError("Deleting '$path' failed: " . $error['message']);
        }
    }

    public static function removeDir($path) {
        $res = rmdir($path);
        if (!$res) {
            $error = error_get_last();
            throw new InternalError("Removing dir '$path' failed: " . $error['message']);
        }
    }

}