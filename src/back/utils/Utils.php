<?php
include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/db.php';

class Utils
{

    const NAME_LENGTH = 60;

    public static function isIE()
    {
        return preg_match('/(?i)msie [1-8]/', $_SERVER['HTTP_USER_AGENT']);
    }

    public static function getCurrentURL()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public static function isEven($number)
    {
        return ceil(fmod($number, 2)) == 0;
    }

    public static function isSquareViewMode()
    {
        return array_key_exists('mode_view', $_GET) && self::getFromGET('mode_view') == 'square';
    }

    public static function getWindowOnclick($str)
    {
        return " onclick=\"window.location='" . $str . "'\"";

    }

    public static function getWindowOnclickValue($str)
    {
        return "window.location='" . $str . "'";

    }

    public static function trimStr($string, $length)
    {
        if (strlen($string) > $length) return substr($string, 0, $length) . "...";
        return $string;
    }

    public static function trimStrDef($string)
    {
        return self::trimStr($string, self::NAME_LENGTH);
    }

    public static function removeParameterFromURL($parameter, $url)
    {
        if (strlen(trim($url)) == 0) {
            $url = $_SERVER['REQUEST_URI'];
        }
        $pattern1 = '/[\?&]{1}' . $parameter . '=[\w%]*/';
        $pattern2 = '/(\/\&){1}/';
        $url = preg_replace($pattern1, "", $url);
        $url = preg_replace($pattern2, '/?', $url);
        return $url;
    }

    public static function replaceParameterValueInURL($parameter, $newValue, $URL)
    {
        if (strlen(trim($URL)) == 0) {
            $URL = $_SERVER['REQUEST_URI'];
        }
        $pattern = '/' . $parameter . '=[\w%]*/';
        $replacement = $parameter . "=" . $newValue;
        return preg_replace($pattern, $replacement, $URL);
    }

    public static function replaceOrAddParameterValueInURL($parameter, $newValue, $url)
    {
        if (stripos($url, $parameter . "=")) {
            $url = self::replaceParameterValueInURL($parameter, $newValue, $url);
        } else {
            $delim = "&";
            if (strlen($url) == 1) {
                $delim = "?";
            }
            $url = $url . $delim . $parameter . "=" . $newValue;
        }
        return $url;
    }

    public static function replaceOrAddParametersValuesInURL($paramValues, $url)
    {
        foreach ($paramValues as $key => $value) {
            $url = self::replaceOrAddParameterValueInURL($key, $value, $url);
        }
        return $url;
    }

    public static function getStoreModeForUrl()
    {
        if (!array_key_exists("check_fiz", $_GET) && !array_key_exists("check_ur", $_GET)) {
            return "&check_fiz=&check_ur=";
        } else {
            return (array_key_exists("check_fiz", $_GET) ? "&check_fiz=" : "") . (array_key_exists("check_ur", $_GET) ? "&check_ur=" : "");
        }
    }

    public static function getUrlWithStoreMode($url)
    {
        return $url;/*Utils::replaceOrAddParametersValuesInURL(array(Labels::CHECK_FIZ => "", Labels::CHECK_UR => ""), $url);*/
    }

    public static function getHiddenInputByGETParams($params)
    {
        $inputs = "";
        for ($index = 0; $index < count($params); $index++) {
            if (array_key_exists($params[$index], $_GET)) {
                $inputs = $inputs . "<input type='hidden' name='" . $params[$index] . "' value='" . Utils::getFromGET($params[$index]) . "'/>";
            }
        }
        return $inputs;
    }

    public static function getDescriptionArray($description)
    {
        $descArray = explode('|', $description);
        $resultArray = array();
        if (count($descArray) != 0) {
            for ($index = 0; $index < count($descArray); $index++) {
                $keyValue = explode('=', $descArray[$index]);
                $key = trim(preg_replace('/\s\s+/', ' ', $keyValue[0]));
                $key = DescriptionKeys::$keys[$key];
                $value = strlen($keyValue[1]) == 0 ? '' : $keyValue[1];
                $resultArray[$key] = $value;
            }
            return $resultArray;
        }
        return array(DescriptionKeys::$keys[Constants::DEFAULT_ITEM_DESCRIPTION_KEY] => $description);
    }

    public static function getFirstImageByKeyItem($keyItem)
    {


    }

    public static function outArray($array)
    {
        return implode(',', $array);
    }

    public static function buildUrl($array)
    {
        $url = '';
        foreach ($array as $key => $value) {
            $url .= $key . "=" . urlencode($value) . "&";
        }
        return preg_replace('/&{1}$/', '', $url);
    }

    public static function createUrlArrayFromCurrentUrl($urlRule)
    {
        $urlArray = array();
        if (array_key_exists(Labels::MAIN_PARAMS, $urlRule)) {
            $mainParams = $urlRule[Labels::MAIN_PARAMS];
            for ($paramIndex = 0; $paramIndex < count($mainParams); $paramIndex++) {
                if (array_key_exists($mainParams[$paramIndex], $_GET)) {
                    $urlArray[$mainParams[$paramIndex]] = Utils::getFromGET($mainParams[$paramIndex]);
                }
            }
        }
        if (array_key_exists(Labels::ADDITIONAL_PARAMS, $urlRule)) {
            $additionalParams = $urlRule[Labels::ADDITIONAL_PARAMS];
            for ($additionalParamArrayIndex = 0; $additionalParamArrayIndex < count($additionalParams); $additionalParamArrayIndex++) {
                $additionalParamRule = $additionalParams[$additionalParamArrayIndex];
                $correctRule = false;
                for ($additionalParamRuleIndex = 0; $additionalParamRuleIndex < count($additionalParamRule); $additionalParamRuleIndex++) {
                    $correctRule = array_key_exists($additionalParamRule[$additionalParamRuleIndex], $_GET);
                    if (!$correctRule) {
                        break;
                    }
                }
                if ($correctRule) {
                    for ($additionalParamRuleIndex = 0; $additionalParamRuleIndex < count($additionalParamRule); $additionalParamRuleIndex++) {
                        $urlArray[$additionalParamRule[$additionalParamRuleIndex]] = Utils::getFromGET($additionalParamRule[$additionalParamRuleIndex]);
                    }
                }
            }
        }
        return $urlArray;
    }

    public static function isNullOrEmptyString($question)
    {
        return (!isset($question) || trim($question) === '');
    }

    public static function cleanWithSpecialChars($string)
    {
        return preg_replace('/[^\;\:\-\=\|\,\.\_\@\%\-A-Za-zА-Яа-я0-9 ]/u', '', addslashes($string)); // Removes special chars.
    }

    public static function isHomeNaked($url)
    {
        $urlPostfix = parse_url($url, PHP_URL_PATH);
        $urlQuery = parse_url($url, PHP_URL_QUERY);
        return ($urlPostfix == '/' || $urlPostfix == '') && $urlQuery == '';
    }

    public static function getFromGET($key)
    {
        if (array_key_exists($key, $_GET)) {
            if (is_array($_GET[$key])) {
                return self::cleanArrayWithSpecialChars($_GET[$key]);
            } else if (is_string($_GET[$key])) {
                return self::cleanWithSpecialChars(urldecode($_GET[$key]));
            }
        }
        return null;
    }

    public static function getFromGETWithDefault($key, $default)
    {
        if (array_key_exists($key, $_GET)) {
            if (is_array($_GET[$key])) {
                return self::cleanArrayWithSpecialChars($_GET[$key]);
            } else if (is_string($_GET[$key])) {
                return self::cleanWithSpecialChars(urldecode($_GET[$key]));
            }
        }
        return $default;
    }

    public static function getPostSource()
    {
        if (array_key_exists('CONTENT_TYPE', $_SERVER) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') === 0) {
            if (!array_key_exists('POST_JSON', $GLOBALS)) {
                $GLOBALS['POST_JSON'] = json_decode(file_get_contents('php://input'), true);
            }
            return $GLOBALS['POST_JSON'];
        }
        return $_POST;
    }

    public static function getFromPOST($key, $shouldEscape = true)
    {
        $data = self::getPostSource();
        if (array_key_exists($key, $data)) {
            if (!$shouldEscape) {
                return $data[$key];
            }
            if (is_array($data[$key])) {
                return self::cleanArrayWithSpecialChars($data[$key]);
            } else if (is_string($data[$key])) {
                return self::cleanWithSpecialChars(urldecode($data[$key]));
            } else {
                return $data[$key];
            }
        }
        return null;
    }

    public static function getFromPOSTWithDefault($key, $default, $shouldEscape = true)
    {
        $data = $data = self::getPostSource();
        if (array_key_exists($key, $data)) {
            if (!$shouldEscape) {
                return $data[$key];
            }
            if (is_array($data[$key])) {
                return self::cleanArrayWithSpecialChars($data[$key]);
            } else if (is_string($data[$key])) {
                return self::cleanWithSpecialChars(urldecode($data[$key]));
            } else {
                return $data[$key];
            }
        }
        return $default;
    }

    public static function arrayPrependToItem($array, $prependValue)
    {
        for ($index = 0; $index < count($array); $index++) {
            $array[$index] = $prependValue . $array[$index];
        }
        return $array;
    }

    public static function arrayAppendToItem($array, $appendValue)
    {
        for ($index = 0; $index < count($array); $index++) {
            $array[$index] .= $appendValue;
        }
        return $array;
    }

    public static function cleanArrayWithSpecialChars(&$array)
    {
        foreach ($array as $key => $value) {
            if (is_string($array[$key])) {
                $array[$key] = self::cleanWithSpecialChars($array[$key]);
            } else if (is_array($array[$key])) {
                self::cleanArrayWithSpecialChars($array[$key]);
            }
        }
        return $array;
    }

    public static function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function formatClotheTitle($title)
    {
        $replacement = 'Модель';
        $replacement = 'Модель';
        $res_ = strripos($title, $replacement);
        if ($res_ !== false && $res_ > 0) {
            $value = substr($title, 0, $res_ - 1);
            return $value . '<br>' . substr($title, $res_, strlen($title) - 1);
        }
        return $title;
    }

    public static function trimFormatClotheTitle($title)
    {
        $replacement = 'Модель';
        $res_ = strripos($title, $replacement);
        if ($res_ !== false && $res_ > 0) {
            $value = substr($title, 0, $res_ - 1);
            $value = self::trimStrDef($value);
            return $value . '<br>' . substr($title, $res_, strlen($title) - 1);
        }
        return $title;
    }

    public static function cleanExplode($delimiter, $string)
    {
        $result = explode($delimiter, $string);
        $ret = [];
        for ($ind = 0; $ind < count($result); $ind++) {
            if (strlen(trim($result[$ind])) != 0) {
                array_push($ret, trim($result[$ind]));
            }
        }
        return $ret;
    }

    public static function getRandomString($length = 20)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    public static function isBase64($base64String)
    {
        $base64 = self::extractBase64($base64String);
        return $base64 != null;
    }


    public static function extractBase64($base64String)
    {
        //TODO next regexp is NOT so VALID but is FAST!
        preg_match("/^data:[a-z]{1,50}\\/[a-z]{1,50};base64,(.*)$/", $base64String, $res);
        //TODO next regexp is ABSOLUTELY VALID, but is so SLOW!
        //preg_match("/^data:[a-z]{1,50}\\/[a-z]{1,50};base64,((?:[A-Za-z0-9+\\/]{4})*(?:[A-Za-z0-9+\\/]{2}==|[A-Za-z0-9+\\/]{3}=)?)$/", $base64String, $res);
        if (count($res) > 1) {
            return $res[1];
        }
        return null;
    }

    public static function getImageExtensionFromBase64($base64String)
    {
        preg_match("/^data:image\\/([\\w\\d_]{1,30}){1};.*$/", $base64String, $result);
        if (count($result) > 0) {
            return $result[1];
        }
        return null;
    }

    public static function extractObject($source, $mapping)
    {
        $result = [];
        foreach ($mapping as $key => $value) {
            if (is_callable($value)) {
                $result[$key] = $value($source[$key], $source);
            } else if (array_key_exists($value, $source)) {
                $result[$key] = $source[$value];
            }
        }
        return $result;
    }

    public static function normalizeAbsoluteImagePath($imagePath, $query = [])
    {
        if (count($query)) {
            $imagePath .= '?';
            foreach ($query as $key => $value) {
                $imagePath .= "$key=$value";
            }
        }
        return $imagePath;
    }
}