<?php

class LocalizationHelpers
{
    public static function mergeHelpers(&$localization)
    {
        $localization['get'] = function ($path) use (&$localization) {
            $pathArray = explode('.', $path);
            $value = &$localization;
            foreach ($pathArray as $key) {
                if (is_array($value)) {
                    if (array_key_exists($key, $value)) {
                        $temp = &$temp[$key];
                    } else {
                        return '';
                    }

                } else if (is_string($value) || is_numeric($value)) {
                    return $value;
                }
            }
            $temp = $value;
            unset($temp);
        };
    }

    public static function parseAssocArrayDeeply(&$array, $delimiter = '.') {
        $keys = array_keys($array);
        foreach ($keys as $key) {
            $keyAsArray = explode($delimiter, $key);
            $value = $array[$key];
            if (count($keyAsArray) > 0) {
                $innerArray = &$array;
                for ($index = 0; $index < count($keyAsArray); $index++) {
                    $parsedKey = $keyAsArray[$index];
                    if ($index == count($keyAsArray) - 1) {
                        $innerArray[$parsedKey] = $value;
                    } else if (array_key_exists($parsedKey, $innerArray)) {
                        if (!is_array($innerArray[$parsedKey])) {
                            $innerArray[$parsedKey] = [];
                        }
                        $innerArray = &$innerArray[$parsedKey];
                    } else {
                        $innerArray[$parsedKey] = [];
                        $innerArray = &$innerArray[$parsedKey];
                    }
                }
            }
        }
        return $array;
    }
}