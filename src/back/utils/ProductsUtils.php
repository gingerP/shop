<?php

include_once("src/back/import/import");

class ProductsUtils
{
    public static function normalizeImagesFromCodes($images, $productCode, $prefix, $catalogRoot)
    {
        $result = [];
        if (count($images) > 0) {
            foreach ($images as $imageCode) {
                $imagePath = FileUtils::buildPath(
                    $catalogRoot,
                    $productCode,
                    $prefix . $imageCode . '.jpg');
                array_push($result, $imagePath);
            }
        }
        return $result;
    }

}
