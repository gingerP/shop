<?php
include_once AuWebRoot . '/src/back/import/import.php';

class Item
{

    public static function getMetroItemView($product, $images, $version, $trimName)
    {
        $previewsColsNum = 2;
        $previewsNum = 0;
        $blackOut = new Div();
        $blackOut->addAttributes([
            'itemscope' => '',
            'itemtype' => 'http://data-vocabulary.org/Product'
        ]);
        $mainDiv = new Div();
        $blackOut->addChild($mainDiv);
        TagUtils::createShadow($mainDiv);
        $mainDiv->addStyleClasses(['simple_item_metro', 'ciic']);
        $imagesCount = min(count($images), SettingsNames::MAX_IMAGE_COUNT_METRO_VIEW);

        $row0 = new Div();
        $row0->addStyleClass('images_row');
        $row1 = new Div();
        $row1->addStyleClasses(['images_row_last']);
        $totalCount = count($images);
        if ($totalCount > 3) {
            array_splice($images, 3);
        } else if ($totalCount == 2) {
            $images = [$images[0]];
        }
        $totalCount = count($images);
        for ($imgIndex = 0; $imgIndex < $totalCount; $imgIndex++) {
            if ($imgIndex == $imagesCount) {
                break;
            }
            $productImage = (new Img())
                ->addAttributes(
                    [
                        'rel' => 'preload',
                        'itemprop' => 'image',
                        TagLabels::ON_CLICK => 'openSimpleImg(arguments[0])',
                        'data-src' => '/' . addslashes($images[$imgIndex]),
                        'width' => '',
                        'height' => '',
                        'alt' => $product['name']
                    ])
                ->addStyleClass($imgIndex > 0 ? 'simple_item_image_half' : 'simple_item_image');
            $placeholderImage = (new Img())
                ->addAttributes(
                    [
                        'rel' => 'preload',
                        'src' => '/images/placeholder.png'
                    ]
                )
                ->addStyleClass('simple_item_image catalog-product-placeholder');
            if ($imgIndex == 0) {
                $mainDiv->addChildren($placeholderImage, $productImage);
                break;
            } else {
                $row0->addChild($placeholderImage, $productImage);
                $previewsNum++;
            }
        }

        $blackoutContainer = new Div();
        $blackoutContainer->addStyleClass('blackout_container');
        $blackOut->addChild($blackoutContainer);

        $mainDiv->addChild($row0);
        $mainDiv->addStyleClass('cursor_pointer');
        $link = TagUtils::createNote($trimName, '');
        $link->addAttribute('itemprop', 'name');
        $blackOut->addChild($link);
        $blackOut->addChild(self::getItemButton());

        if ($previewsNum == 0) {
            $previewsColsNum = 0;
        } else if ($previewsNum == 1 || $previewsNum == 2) {
            $previewsColsNum = 1;
        }

        return [
            $blackOut,
            [
                'previews_cols_num' => $previewsColsNum,
                'previews_num' => $previewsNum
            ]
        ];
    }

    public static function getItemButton()
    {
        $button = new Div();
        $button->addStyleClasses(['catalog_item_button', 'f-17', 'input_hover']);
        $button->addChild('подробнее');
        return $button;
    }

    private static function normalizeImagePath($imagePath)
    {
        if (strpos($imagePath, AU_ROOT) == 0) {
            return substr($imagePath, strlen(AU_ROOT));
        }
        return $imagePath;
    }
}
