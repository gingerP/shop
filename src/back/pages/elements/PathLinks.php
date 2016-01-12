<?php
include_once("import");
include_once("tag");
include_once("page");

class PathLinks {

    private static $CLASS = "CLASS";
    private $numModeNum = array('6', '12', '24', '48', '96');

    public function PathLinks() {}

    public function render() {
        $borderStyle = 'border-bottom-left-none';
        $mainPathDiv = new Div();
        $mainPathDiv->addStyleClass("main-path");
        if (array_key_exists(UrlParameters::PAGE_NAME, $_GET)) {
            $pageName = Utils::getFromGET(UrlParameters::PAGE_NAME);
            /*log::temp('path-link render 1');*/
            if (array_key_exists(UrlParameters::KEY, $_GET)) {
                /*log::temp('path-link render 2');*/
                $navKeys = new DBNavKeyType();
                if ($pageName == UrlParameters::PAGE__SINGLE_ITEM) {
                    /*log::temp('path-link render 3');*/
                    $div = new Div();
                    $div->addStyleClasses(array($borderStyle
                    , "font_arial"
                    , "float_left"
                    , "path_link_item"
                    , "text_non_select"
                    , "cursor_pointer"));
                    $div->updateId("path_link");
                    $div->addAttribute(TagLabels::ON_CLICK, Utils::getWindowOnclickValue(URLBuilder::getPathLinkSingleItem()));
                    $div->addChild($navKeys->getNameByKey(Utils::getFromGET( UrlParameters::KEY)) . " стр. " . Utils::getFromGET( UrlParameters::PAGE));
                    $mainPathDiv->addChild($div);

                    $divContainer = new Div();
                    $divContainer->updateId('store_mode_container');
                    $div1 = new Div();
                    $div1->updateId('path_');
                    $divContainer->addChild($div1);

                    $div2 = new Div();
                    $div2->updateId('path__');
                    $divContainer->addChild($div2);
                    $mainPathDiv->addChild($divContainer);
                }
                echo $mainPathDiv->getHtml();
            }
        }
    }

    public static function getDOMForTree() {
        $mainTag = new Div();
        $mainTag->addStyleClass("path_link_text");
        $mainTag->addChild("Каталог");
        return $mainTag;
    }

    public static function getDOMForContacts() {
        $mainTag = new Div();
        $mainTag->addStyleClass("path_link_text");
        $mainTag->addChild("Контакты");
        return $mainTag;
    }

    public static function getDOMForTreeCatalog($treeKey, $pageIstance) {
        $mainTag = new Div();
        $mainTag->addStyleClasses(["catalog_tree"]);
        $treeUtils = new TreeUtils();
        $mainTree = $treeUtils->buildTreeByLeafs();
        $path = $treeUtils->getTreePath($mainTree, $treeKey);
        $data = [self::getArrayItemForDefaultLink()];
        $keys = [];
        for ($pathIndex = 1; $pathIndex < count($path); $pathIndex++) {
            array_push($keys, $path[$pathIndex]->value.' - ');
            array_push($data,
                [
                    Utils::trimStr($path[$pathIndex]->value, Constants::DEFAULT_TEXT_LENGTH_FOR_CATALOG_PATH_LINK),
                    URLBuilder::getCatalogLinkForTree($path[$pathIndex]->key)
                ]
            );
        }
        $keys = array_reverse($keys);
        $pageIstance->updateTitleTagChildren($keys);
        return $mainTag->addChildList(self::buildPathLink($data));
    }

    public static function getDOMForCatalog() {
        $mainTag = new Div();
        $mainTag->addStyleClasses(["catalog_tree"]);
        $mainTag->addChildList(self::buildPathLink([[Utils::trimStr("Каталог", Constants::DEFAULT_TEXT_LENGTH_FOR_CATALOG_PATH_LINK)]]));
        return $mainTag;
    }


    public static function getDOMForSearchPage() {
        return "";
    }

    public static function getDOMForDownloadPage() {
        return "";
    }

    public static function getDOMForSingleItemPage() {
        if (array_key_exists(UrlParameters::KEY, $_GET)) {
            return self::getDOMForSingleItemPageFromTree(Utils::getFromGET(UrlParameters::KEY));
        } elseif (array_key_exists(UrlParameters::SEARCH_VALUE, $_GET)) {
            return self::getDOMForSingleItemPageFromSearch(Utils::getFromGET(UrlParameters::SEARCH_VALUE));
        } else {
            return self::getDOMForSingleItemPageFromCatalog();
        }
    }

    public static function getDOMForSingleItemPageFromCatalog() {
        $mainTag = new Div();
        $mainTag->addStyleClasses(["catalog_tree"]);
        $mainTag->addChildList(self::buildPathLink([self::getArrayItemForDefaultLink()]));
        return $mainTag;
    }

    public static function getArrayItemForDefault() {
        return [Utils::trimStr("Каталог", Constants::DEFAULT_TEXT_LENGTH_FOR_CATALOG_PATH_LINK)];
    }

    public static function getArrayItemForDefaultLink() {
        return [Utils::trimStr("Каталог", Constants::DEFAULT_TEXT_LENGTH_FOR_CATALOG_PATH_LINK), Labels::$TOP_NAVIGATION_LINKS[catalog]];
    }

    public static function getDOMForSingleItemPageFromTree($key) {
        $mainTag = new Div();
        $mainTag->addStyleClasses(["catalog_tree"]);
        $treeUtils = new TreeUtils();
        $mainTree = $treeUtils->buildTreeByLeafs();
        $path = $treeUtils->getTreePath($mainTree, $key);
        $data = [self::getArrayItemForDefaultLink()];
        for ($pathIndex = 1; $pathIndex < count($path); $pathIndex++) {
            $item = [];
            $trimText = Utils::trimStr($path[$pathIndex]->value, Constants::DEFAULT_TEXT_LENGTH_FOR_CATALOG_PATH_LINK);
            if ($pathIndex == count($path) - 1) {
                $pageNumber = Utils::getFromGET(UrlParameters::PAGE_NUM);
                $itemsCount = Utils::getFromGET(UrlParameters::ITEMS_COUNT);
                $item[0] = $trimText;
                $item[1] = URLBuilder::getSingleItemLinkBack($pageNumber, $itemsCount);
            } else {
                $item[0] = $trimText;
                $item[1] = URLBuilder::getCatalogLinkForTree($path[$pathIndex]->key);
            }
            array_push($data, $item);
        }
        $mainTag->addChildList(self::buildPathLink($data));
        return $mainTag;
    }

    public static function getDOMForSingleItemPageFromSearch() {
        return "";
    }

    public static function getDOMForViewModeSelector() {
        $rt_numeric_view_mode = Labels::VIEW_MODE_NUMERIC_DEF;
        $rt_compact_view_mode = Labels::VIEW_MODE_COMPACT_DEF;
        if (array_key_exists(UrlParameters::ITEMS_COUNT, $_GET) && in_array(Utils::getFromGET(UrlParameters::ITEMS_COUNT), Labels::$VIEW_MODE_NUMERIC)) {
            $rt_numeric_view_mode = Utils::getFromGET(UrlParameters::ITEMS_COUNT);
        }
        if (array_key_exists(UrlParameters::VIEW_MODE, $_GET) && array_key_exists(Utils::getFromGET(UrlParameters::VIEW_MODE), Labels::$VIEW_MODE_COMPACT)) {
            $rt_compact_view_mode = Utils::getFromGET(UrlParameters::VIEW_MODE);
        }
        $mainTag = new Div();
        $mainTag->addStyleClass("view_mode");

        $numeric = new Div();
        $numeric->addStyleClass("numeric");
        $selectNumeric = new Ul();
        for ($numIndex = 0; $numIndex < count(Labels::$VIEW_MODE_NUMERIC); $numIndex++) {
            $value = Labels::$VIEW_MODE_NUMERIC[$numIndex];
            $option = new Li();
            $item = new Div();
            $item->addChild($value);
            $item->addStyleClass("numeric_item");
            $option->addChild($item);
            if ($value == $rt_numeric_view_mode) {
                $option->addStyleClass("selected");
            }
            $selectNumeric->addChild($option);
        }

        $line = new Div();
        $line->addStyleClass("view");
        $selectCompact = new Ul();
        foreach (Labels::$VIEW_MODE_COMPACT as $key => $value) {
            $option = new Li();
            /*$option->addChild($value);*/
            //$option->addAttribute("value", $key);
            $option->addChild(self::getDOMForViewMode($key));
            if ($key == $rt_compact_view_mode) {
                $option->addStyleClass("selected");
            }
            $selectCompact->addChild($option);
        }
        return $mainTag->addChildList([$line->addChild($selectCompact), $numeric->addChild($selectNumeric)]);
    }


    private function buildPathLink($data) {
        $links = [];
        $zIndex = 10;
        $pathIndex = 0;
        $arrow = new Div();
        $arrow->addStyleClass("path_link_item_arrow_right");
        for($index = 0; $index < count($data); $index++) {
            $node = new Div();
            $node->addAttributes(["xmlns:v" => "http://rdf.data-vocabulary.org/#", "typeof" => "v:Breadcrumb"]);
            $node->addAttribute("style", "z-index: ".$zIndex--);
            $node->addStyleClass("level".($index + 1));
            if (count($data[$index]) > 1 && strlen(trim($data[$index][1])) > 0) {
                $pathNode = new A();
                $pathNode->addAttributes(["rel" => "v:url", "property" => "v:title"]);
                $pathNode->addAttribute("href", $data[$index][1]);
            } else {
                $pathNode = new Div();
            }
            $trimText = Utils::trimStr($data[$index][0], Constants::DEFAULT_TEXT_LENGTH_FOR_CATALOG_PATH_LINK);
            $pathNode->addChild($trimText);
            $pathNode->addStyleClasses(["path_link_item_text", $index > 0? "not_level_first_level": ""]);
            array_push($links, $node->addChildList([$pathNode/*, $arrow*/]));
        }
        return $links;
    }

    private function getModeNumLinks() {
        $toReturn = array(5);
        $url = $_SERVER[ 'REQUEST_URI' ];
        if (!array_key_exists('page', $_GET) && !array_key_exists('num', $_GET)) {
            for ($index = 0; $index < 5; $index++) {
                $toReturn[ $index ] = $url . '&page=1&num=' . $this->numModeNum[ $index ];
            }
        } else {
            for ($index = 0; $index < 5; $index++) {
                $tempURL = Utils::replaceParameterValueInURL('page', '1', $url);
                $toReturn[ $index ] = Utils::replaceParameterValueInURL('num', $this->numModeNum[ $index ], $tempURL);
            }
        }
        return $toReturn;
    }

    private static function getDOMForViewMode($type) {
        $mainDom = new Div();
        $mainDom->addStyleClass($type);
        $mainDom->addAttribute("view_type", $type);
        switch($type) {
            case "compact":
                $mainDom->addChildList([new Div(), new Div(), new Div(), new Div(), new Div(), new Div()]);
                break;
            case "extend":
                $mainDom->addChildList([new Div(), new Div(), new Div()]);
                break;
            case "metro":
                $mainDom->addChildList([new Div(), new Div()]);
                break;
            default:
                $mainDom->addChildList([new Div(), new Div(), new Div(), new Div(), new Div(), new Div()]);
        }
        return $mainDom;
    }
}
